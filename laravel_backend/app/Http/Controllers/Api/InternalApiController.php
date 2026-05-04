<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\WeeklyInsight;
use App\Models\ScrapingJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Throwable;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\StoreInsightRequest;
use GrahamCampbell\ResultType\Success;

class InternalApiController extends Controller{
    public function getPendingJob()
{
    try {
        DB::beginTransaction();

        $job = DB::table('scraping_jobs')
            ->where('status', 'pending')
            ->lockForUpdate()
            ->first();

        if ($job) {
            DB::table('scraping_jobs')
                ->where('id', $job->id)
                ->update([
                    'status' => 'processing',
                    'started_at' => now(),
                    'updated_at' => now()
                ]);
        }

        DB::commit();

        return response()->json($job);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function markSuccess($id){
        $job = ScrapingJob::findOrFail($id);

        $job->update([
            'status' => 'success',
            'finished_at' => now()
        ]);

        return response()->json(['ok' => true]);
    }

    public function markFailed(Request $request, $id){
        $job = ScrapingJob::findOrFail($id);

        $newAttempt = $job->attempt_count+1;

        $status = $newAttempt >=3
            ? 'failed_non_retryable': 'failed_retryable';

        $job->update([
            'status' => $status,
            'last_error' => $request->error,
            'finished_at' => now(),
            'attempt_count' => $job->attempt_count + 1
        ]);

        return response()->json(['ok'=>true]);
    }

    public function recoverStuck(){
        ScrapingJob::where('status', 'processing')
        ->where('started_at', '<', now()->subMinutes(30))
        ->where('attempt_count', '<', 3)
        ->update([
            'status' => 'pending',
            'attempt_count' => DB::raw('attempt_count + 1')
        ]);

        return response()->json(['ok' => true]);
    }

    public function getAccount($id){
        
            $account = Account::findOrFail($id);

            return response()->json([
                'id' =>$account->id,
                'instagram_business_id' => $account->instagram_business_id,
                "access_token" => $account->access_token,
                'expires_at' => $account->expires_at,
            ]);
    }

    public function jobStats(){
        return response()->json([
            'pending' => ScrapingJob::where('status', ScrapingJob::STATUS_PENDING) -> count(),
            'processing' => ScrapingJob::where('status', ScrapingJob::STATUS_PROCESSING)->count(),
            'success' => ScrapingJob::where('status', ScrapingJob::STATUS_SUCCESS)-> count(),
            'failed_retryable' => ScrapingJob::where('status', ScrapingJob::STATUS_FAILED_RETRYABLE)-> count(),
            'failed_non_retryable' => ScrapingJob::where('status', ScrapingJob::STATUS_FAILED_NON_RETRYABLE)->count()
        ]);
    }

    public function health(){
        $stuckJobs = ScrapingJob::where('status', ScrapingJob::STATUS_PROCESSING)
        ->where('started_at', '<', now()->subMinutes(30))
        ->count();

        return response()->json([
            'status' => $stuckJobs > 0 ? 'unhealthy' : 'healthy',
            'stuck_jobs' => $stuckJobs
        ]);
    }

    public function storeInsight(Request $request){

        try {
        $accountId = $request->input('account_id');
        $data = $request->input('data', []);
        $since = $request->input('since');
        $until = $request->input('until');

        // 🔒 Validasi basic
        if (!$accountId || !$since || !$until) {
            return response()->json([
                'error' => 'Missing required fields'
            ], 400);
        }

        // 🔥 Mapping metric ke kolom DB
        $payload = [
            'account_id'     => $accountId,
            'since_date'     => $since,
            'until_date'     => $until,
            'like'           => $data['like'] ?? 0,
            'views'          => $data['views'] ?? 0,
            'follows_and_unfollows' => $data['follows_and_unfollows'] ?? 0,
            'reach'          => $data['reach'] ?? 0,
        ];

        // 🔥 UPSERT (hindari duplicate since-until)
        DB::table('weekly_insights')->updateOrInsert(
            [
                'account_id' => $accountId,
                'since_date' => $since,
                'until_date' => $until,
            ],
            $payload
        );

        return response()->json([
            'status' => 'success',
            'data' => $payload
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
    }  



    public function refreshToken($id)
{
    try {
        $account = Account::findOrFail($id);

        if (!$account->access_token) {
            throw new Exception("Access token kosong di DB");
        }

        $response = Http::get('https://graph.facebook.com/v24.0/oauth/access_token', [
            'grant_type' => 'fb_exchange_token',
            'client_id' => config('services.meta.client_id'),
            'client_secret' => config('services.meta.client_secret'),
            'fb_exchange_token' => $account->access_token,
        ]);

        // 🔥 LOG RAW RESPONSE
        Log::info("META TOKEN RESPONSE", [
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        if (!$response->ok()) {
            throw new Exception("Meta API failed: " . $response->body());
        }

        $data = $response->json();

        // 🔥 VALIDASI RESPONSE
        if (!isset($data['access_token'])) {
            throw new Exception("Meta response invalid: " . json_encode($data));
        }

        $account->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in'] ?? 0)
        ]);

        return response()->json([
            'status' => 'success'
        ]);

    } catch (\Throwable $e) {

        Log::error("REFRESH TOKEN ERROR", [
            'account_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
} 