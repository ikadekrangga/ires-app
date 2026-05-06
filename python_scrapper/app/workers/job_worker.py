import time
import signal
from app.clients.laravel_client import LaravelClient
from app.services.scrapping_services import ScrappingService
from app.services.retry_services import should_retry, backoff_sleep
from app.core.logger import log
from app.core.config import settings
from app.core.exceptions import (
    RetryableError,
    PermanentError,
    TokenExpiredError
)

running = True


def handle_shutdown(signum, frame):
    global running
    running = False
    log({
        "status": "shutdown_signal_received",
        "signal": signum
    })


def start_worker():
    signal.signal(signal.SIGTERM, handle_shutdown)
    signal.signal(signal.SIGINT, handle_shutdown)

    laravel = LaravelClient()
    scraping = ScrappingService()

    print("=== WORKER STARTED ===")

    while running:

        # -------------------------
        # FETCH JOB
        # -------------------------
        try:
            job = laravel.get_pending_job()
        except Exception as e:
            print("ERROR FETCH JOB:", str(e))
            time.sleep(5)
            continue

        if not job or "id" not in job:
            time.sleep(settings.POLLING_INTERVAL)
            continue

        job_id = job["id"]
        account_id = job["account_id"]
        attempt = job.get("attempt_count", 0)

        print(f"\n=== PROCESSING JOB {job_id} ===")

        start_time = time.time()

        # -------------------------
        # PROCESS JOB
        # -------------------------
        try:
            credentials = laravel.get_account_credentials(account_id)

            if not credentials:
                raise Exception("Account credentials not found")

            result = scraping.process(credentials)

            print("SCRAP RESULT:", result)

            # 🔥 HANDLE EMPTY DATA
            if result.get("note") == "empty_insights":
                print(f"JOB {job_id} → EMPTY INSIGHTS (ALL ZERO)")

            payload = {
                "account_id": account_id,
                "data": result["metrics"],
                "since": result["since"],
                "until": result["until"]
            }

            laravel.save_insights(payload)
            laravel.mark_success(job_id)

            duration = round(time.time() - start_time, 2)

            log({
                "job_id": job_id,
                "account_id": account_id,
                "status": "success",
                "duration_seconds": duration,
                "metrics": result["metrics"]
            })

        # -------------------------
        # TOKEN EXPIRED
        # -------------------------
        except TokenExpiredError as e:
            print("TOKEN EXPIRED → ATTEMPTING REFRESH VIA LARAVEL")

            try:
                laravel.refresh_token(account_id)
                time.sleep(2)
                continue  # 🔥 Jika sukses, retry job langsung (akan diambil di putaran berikutnya)
            except Exception as refresh_error:
                # Jika gagal, Laravel (TokenService) otomatis menggagalkan job ini (permanent_fail) dan men-set akun jadi need_reauth.
                # Worker HANYA perlu log error, JANGAN call mark_failed agar tidak menimpa status Laravel.
                print("REFRESH FAILED → LARAVEL DISABLED THIS ACCOUNT")
                log({
                    "job_id": job_id,
                    "status": "token_refresh_failed_and_disabled",
                    "error": str(refresh_error)
                })

        # -------------------------
        # PERMANENT ERROR
        # -------------------------
        except PermanentError as e:
            laravel.mark_failed(job_id, f"PERMANENT_ERROR: {str(e)}")

            log({
                "job_id": job_id,
                "status": "permanent_error",
                "error": str(e)
            })

        # -------------------------
        # RETRYABLE ERROR (FIXED)
        # -------------------------
        except RetryableError as e:
            log({
                "job_id": job_id,
                "status": "retryable_error",
                "error": str(e),
                "attempt": attempt
            })

            if should_retry(attempt):
                print(f"RETRYING JOB {job_id} (attempt {attempt})")

                backoff_sleep(attempt)
                continue  # 🔥 retry tanpa mark_failed

            else:
                laravel.mark_failed(job_id, f"MAX_RETRY: {str(e)}")

        # -------------------------
        # UNKNOWN ERROR
        # -------------------------
        except Exception as e:
            duration = round(time.time() - start_time, 2)

            print("UNKNOWN ERROR:", str(e))

            laravel.mark_failed(job_id, f"UNKNOWN_ERROR: {str(e)}")

            log({
                "job_id": job_id,
                "status": "unknown_error",
                "error": str(e),
                "duration_seconds": duration
            })