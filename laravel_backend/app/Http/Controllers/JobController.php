<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScrapingJob;

class JobController extends Controller
{
    /**
     * CREATE JOB
     */
    public function createJob(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'since' => 'required|date',
            'until' => 'required|date|after_or_equal:since'
        ]);

        // 🔥 prevent duplicate job (pending / processing)
        $existing = ScrapingJob::where('account_id', $validated['account_id'])
            ->where('since_date', $validated['since'])
            ->where('until_date', $validated['until'])
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        if ($existing) {
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Job already exists',
                'data' => $existing
            ], 409);
        }

        $job = ScrapingJob::create([
            'account_id' => $validated['account_id'],
            'status' => 'pending',
            'attempt_count' => 0,
            'since_date' => $validated['since'],
            'until_date' => $validated['until'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $job
        ]);
    }

    /**
     * LIST JOBS
     */
    public function listJobs(Request $request)
    {
        $query = ScrapingJob::query();

        if ($request->account_id) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $jobs = $query
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $jobs
        ]);
    }

    /**
     * JOB DETAIL
     */
    public function show($id)
    {
        $job = ScrapingJob::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $job
        ]);
    }

    /**
     * JOB STATS (optional tapi berguna)
     */
    public function stats()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'pending' => ScrapingJob::where('status', 'pending')->count(),
                'processing' => ScrapingJob::where('status', 'processing')->count(),
                'success' => ScrapingJob::where('status', 'success')->count(),
                'failed' => ScrapingJob::where('status', 'failed')->count(),
                'permanent_fail' => ScrapingJob::where('status', 'permanent_fail')->count(),
            ]
        ]);
    }
}