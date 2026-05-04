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
            $ids = explode(',', $request->account_id);
            $query->whereIn('account_id', $ids);
        }

        // filter tanggal
        if ($request->since && $request->until) {
            $query->whereBetween('since_date', [
                $request->since,
                $request->until
            ]);
        }

        // 🔥 aggregate utama
        $data = $query
            ->selectRaw('
                SUM(reach) as reach,
                SUM(views) as views,
                SUM(likes) as likes,
                SUM(comments) as comments,
                SUM(follows_and_unfollows) as follows_and_unfollows
            ')
            ->first();

        // 🔥 breakdown per account (penting untuk UI)
        $breakdown = DB::table('weekly_insights')
            ->selectRaw('account_id, SUM(reach) as reach, SUM(views) as views')
            ->groupBy('account_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'breakdown' => $breakdown
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

        $payload = [
            'account_id' => $validated['account_id'],
            'since_date' => $validated['since'],
            'until_date' => $validated['until'],
            'reach' => $validated['reach'] ?? 0,
            'views' => $validated['views'] ?? 0,
            'likes' => $validated['likes'] ?? 0,
            'comments' => $validated['comments'] ?? 0,
            'follows_and_unfollows' => $validated['follows_and_unfollows'] ?? 0,
            'source' => 'manual'
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