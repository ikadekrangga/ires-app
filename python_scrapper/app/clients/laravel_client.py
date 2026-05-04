import time
import httpx

from app.core.config import settings
from app.core.logger import log
from app.core.exceptions import RetryableError, PermanentError


class LaravelClient:

    def __init__(self):
        if not settings.LARAVEL_API_URL:
            raise ValueError("LARAVEL_API_URL is not set")

        if not settings.INTERNAL_API_KEY:
            raise ValueError("INTERNAL_API_KEY is not set")

        self.base_url = settings.LARAVEL_API_URL.rstrip("/")
        self.timeout = httpx.Timeout(10.0, connect=5.0)
        self.max_retries = 5

        self.client = httpx.Client(
            base_url=self.base_url,
            headers={
                "X-INTERNAL-KEY": settings.INTERNAL_API_KEY,
                "Content-Type": "application/json"
            },
            timeout=self.timeout
        )

    # -------------------------
    # INTERNAL REQUEST HANDLER
    # -------------------------
    def _request(self, method, endpoint, json=None):
        endpoint = endpoint.lstrip("/")

        for attempt in range(1, self.max_retries + 1):
            try:
                start = time.time()

                full_url = f"{self.base_url}/{endpoint}"
                print("\n=== LARAVEL REQUEST DEBUG ===")
                print("URL:", full_url)
                print("METHOD:", method)
                print("ATTEMPT:", attempt)

                response = self.client.request(
                    method=method,
                    url=endpoint,
                    json=json
                )

                duration = round(time.time() - start, 3)

                print("STATUS:", response.status_code)
                print("BODY:", response.text[:300])
                print("=============================\n")

                log({
                    "type": "laravel_request",
                    "method": method,
                    "endpoint": endpoint,
                    "status": response.status_code,
                    "duration": duration,
                    "attempt": attempt
                })

                # -------------------------
                # SUCCESS
                # -------------------------
                if 200 <= response.status_code < 300:
                    try:
                        return response.json()
                    except Exception:
                        return {"raw": response.text}

                # -------------------------
                # AUTH ERROR
                # -------------------------
                if response.status_code in [401, 403]:
                    raise PermanentError(
                        f"Unauthorized: {response.text}"
                    )

                # -------------------------
                # NOT FOUND
                # -------------------------
                if response.status_code == 404:
                    raise PermanentError(
                        f"Endpoint not found: {endpoint}"
                    )

                # -------------------------
                # VALIDATION ERROR
                # -------------------------
                if response.status_code == 422:
                    raise PermanentError(
                        f"Validation error: {response.text}"
                    )

                # -------------------------
                # SERVER ERROR
                # -------------------------
                if 500 <= response.status_code < 600:
                    raise RetryableError(
                        f"Laravel 500 error: {response.text}"
                    )

                # -------------------------
                # OTHER 4xx
                # -------------------------
                if 400 <= response.status_code < 500:
                    raise RetryableError(
                        f"Laravel 4xx error: {response.status_code} - {response.text}"
                    )

            # -------------------------
            # HTTPX NETWORK ERRORS
            # -------------------------
            except httpx.RequestError as e:
                print("REQUEST ERROR:", str(e))

                log({
                    "status": "laravel_request_error",
                    "endpoint": endpoint,
                    "attempt": attempt,
                    "error": str(e)
                })

                if attempt < self.max_retries:
                    time.sleep(2)
                    continue
                else:
                    raise Exception(
                        f"Laravel API network failure: {str(e)}"
                    )

            # -------------------------
            # RETRYABLE ERROR (API)
            # -------------------------
            except RetryableError as e:
                print("RETRYABLE ERROR:", str(e))

                if attempt < self.max_retries:
                    time.sleep(2)
                    continue
                else:
                    raise

            # -------------------------
            # PERMANENT ERROR
            # -------------------------
            except PermanentError as e:
                print("PERMANENT ERROR:", str(e))
                raise

            # -------------------------
            # UNKNOWN ERROR (CRITICAL)
            # -------------------------
            except Exception as e:
                print("UNKNOWN CLIENT ERROR:", str(e))

                log({
                    "status": "laravel_unknown_error",
                    "endpoint": endpoint,
                    "attempt": attempt,
                    "error": str(e)
                })

                if attempt < self.max_retries:
                    time.sleep(2)
                    continue
                else:
                    raise

        # -------------------------
        # FINAL FAIL
        # -------------------------
        raise Exception(f"Laravel API unreachable after {self.max_retries} attempts")

    # -------------------------
    # PUBLIC METHODS
    # -------------------------

    def recover_stuck_jobs(self):
        return self._request("POST", "/internal/jobs/recover-stuck")

    def get_pending_job(self):
        return self._request("GET", "/internal/jobs/pending")

    def get_account_credentials(self, account_id):
        return self._request("GET", f"/internal/accounts/{account_id}")

    def mark_success(self, job_id):
        return self._request("POST", f"/internal/jobs/{job_id}/success")

    def mark_failed(self, job_id, error_message):
        return self._request(
            "POST",
            f"/internal/jobs/{job_id}/failed",
            json={"error": error_message}
        )

    def save_insights(self, payload):
        return self._request(
            "POST",
            "/internal/insights",
            json=payload
        )

    def refresh_token(self, account_id):
        return self._request(
            "POST",
            f"/internal/accounts/{account_id}/refresh-token"
        )

    def close(self):
        self.client.close()