<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsightController extends Controller
{
    /**
     * DASHBOARD AGGREGATE
     */
    public function dashboard(Request $request)
    {
        $query = DB::table('weekly_insights');

        // filter account (single / multi)
        if ($request->account_id) {
            $ids = array_filter(explode(',', $request->account_id), 'is_numeric');
            $query->whereIn('account_id', $ids);
        }

        // filter tanggal
        if ($request->since && $request->until) {
            $query->whereBetween('since_date', [
                $request->since,
                $request->until
            ]);
        }

        // 🔥 aggregate utama (PostgreSQL JSONB mapping)
        $data = $query
            ->selectRaw("
                SUM((metrics->>'reach')::numeric) as reach,
                SUM((metrics->>'views')::numeric) as views,
                SUM((metrics->>'likes')::numeric) as likes,
                SUM((metrics->>'comments')::numeric) as comments,
                SUM((metrics->>'follows_and_unfollows')::numeric) as follows_and_unfollows
            ")
            ->first();

        // 🔥 breakdown per account (penting untuk UI)
        $breakdown = DB::table('weekly_insights')
            ->selectRaw("
                account_id, 
                SUM((metrics->>'reach')::numeric) as reach, 
                SUM((metrics->>'views')::numeric) as views,
                SUM((metrics->>'likes')::numeric) as likes,
                SUM((metrics->>'comments')::numeric) as comments,
                SUM((metrics->>'follows_and_unfollows')::numeric) as follows_and_unfollows
            ")
            ->groupBy('account_id')
            ->get();

        // 🔥 trend per tanggal (untuk Line Chart)
        $trendQuery = DB::table('weekly_insights')
            ->selectRaw("
                since_date as date,
                SUM((metrics->>'reach')::numeric) as reach,
                SUM((metrics->>'views')::numeric) as views,
                SUM((metrics->>'likes')::numeric) as likes,
                SUM((metrics->>'comments')::numeric) as comments,
                SUM((metrics->>'follows_and_unfollows')::numeric) as follows_and_unfollows
            ");

        if ($request->account_id) {
            $ids = array_filter(explode(',', $request->account_id), 'is_numeric');
            $trendQuery->whereIn('account_id', $ids);
        }
        if ($request->since && $request->until) {
            $trendQuery->whereBetween('since_date', [$request->since, $request->until]);
        }

        $trend = $trendQuery->groupBy('since_date')->orderBy('since_date')->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'breakdown' => $breakdown,
            'trend' => $trend
        ]);
    }

    /**
     * MANUAL INPUT INSIGHT
     */
    public function storeManual(Request $request)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'since' => 'required|date',
            'until' => 'required|date|after_or_equal:since',
            'reach' => 'nullable|integer',
            'views' => 'nullable|integer',
            'likes' => 'nullable|integer',
            'comments' => 'nullable|integer',
            'follows_and_unfollows' => 'nullable|integer',
        ]);

        $metrics = [
            'reach' => $validated['reach'] ?? 0,
            'views' => $validated['views'] ?? 0,
            'likes' => $validated['likes'] ?? 0,
            'comments' => $validated['comments'] ?? 0,
            'follows_and_unfollows' => $validated['follows_and_unfollows'] ?? 0,
            'source' => 'manual'
        ];

        $payload = [
            'account_id' => $validated['account_id'],
            'since_date' => $validated['since'],
            'until_date' => $validated['until'],
            'metrics' => json_encode($metrics)
        ];

        DB::table('weekly_insights')->updateOrInsert(
            [
                'account_id' => $validated['account_id'],
                'since_date' => $validated['since'],
                'until_date' => $validated['until'],
            ],
            $payload
        );

        return response()->json([
            'status' => 'success',
            'data' => $payload
        ]);
    }
}