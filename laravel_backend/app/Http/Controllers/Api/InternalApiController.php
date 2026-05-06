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
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TokenService;

class InternalApiController extends Controller{
    public function getPendingJob()
{
    try {
        DB::beginTransaction();

        $job = DB::table('scraping_jobs')
            ->whereIn('status', ['pending', 'failed'])
            ->where('next_retry_at', '<=', now())
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
        Log::error('getPendingJob error', ['error' => $e->getMessage()]);

        return response()->json([
            'error' => 'Internal server error'
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

        $newAttempt = $job->attempt_count + 1;

        $status = $newAttempt >= $job->max_attempts
            ? 'permanent_fail' : 'failed';

        $job->update([
            'status' => $status,
            'error_reason' => $request->error,
            'attempt_count' => $newAttempt,
            'next_retry_at' => $status === 'failed' ? now()->addMinutes(10 * $newAttempt) : $job->next_retry_at,
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
        
        $account = Account::with('activeCredential')->findOrFail($id);

        if (!$account->activeCredential) {
            return response()->json(['error' => 'No active token found'], 404);
        }

        return response()->json([
            'id' => $account->id,
            'instagram_business_id' => $account->ig_account_id,
            'access_token' => $account->activeCredential->access_token,
            'expires_at' => $account->activeCredential->expires_at,
        ]);
    }

    public function jobStats(){
        return response()->json([
            'pending' => ScrapingJob::where('status', 'pending')->count(),
            'processing' => ScrapingJob::where('status', 'processing')->count(),
            'success' => ScrapingJob::where('status', 'success')->count(),
            'failed' => ScrapingJob::where('status', 'failed')->count(),
            'permanent_fail' => ScrapingJob::where('status', 'permanent_fail')->count()
        ]);
    }

    public function health(){
        $stuckJobs = ScrapingJob::where('status', 'processing')
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

        // 🔥 Memasukkan semua metric langsung ke jsonb
        $payload = [
            'account_id'     => $accountId,
            'since_date'     => $since,
            'until_date'     => $until,
            'metrics'        => json_encode([
                'like'                  => $data['like'] ?? 0,
                'views'                 => $data['views'] ?? 0,
                'follows_and_unfollows' => $data['follows_and_unfollows'] ?? 0,
                'reach'                 => $data['reach'] ?? 0,
            ]),
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
        Log::error('storeInsight error', ['error' => $e->getMessage()]);
        return response()->json([
            'error' => 'Failed to store insight'
        ], 500);
    }
    }  



    public function refreshToken($id, TokenService $tokenService)
{
    $account = Account::findOrFail($id);
    $activeCredential = $account->activeCredential;

    if (!$activeCredential) {
        return response()->json(['error' => 'No active token found'], 404);
    }

    $success = $tokenService->refreshToken($activeCredential);

    if ($success) {
        return response()->json(['status' => 'success']);
    }

    return response()->json(['error' => 'Failed to refresh token'], 500);
}


public function updateAccountStatus(Request $request, $id, TokenService $tokenService)
{
    $request->validate(['status' => 'required|in:need_reauth,active,disconnected']);
    $account = Account::findOrFail($id);

    if ($request->status === 'need_reauth') {
        $tokenService->markAccountAsNeedsReauth($account);
    } else {
        $account->update(['status' => $request->status]);
    }

    return response()->json(['message' => 'Status updated']);
}

} 