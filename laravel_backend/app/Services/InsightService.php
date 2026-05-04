<?php

namespace App\Services;
use App\Models\WeeklyInsight;



class InsightService{
    public function store(array $payload){

        $accountId = $payload['account_id'];
        $data = $payload['data'];

        $since = now()->subDays(7)->startOfDay();
        $until = now()->endOfDay();

        WeeklyInsight::updateOrCreate(
            [
                'account_id' => $accountId,
                'since_date' => $since,
                'until_date' => $until
            ],
            [
                'reach' => $data['reach'] ?? 0,
                'profile_views' => $data['profile_views'] ?? 0,
                'likes' => $data['likes'] ?? 0,
                'views' => $data['views'] ?? 0,
                'comments' => $data['comments'] ?? 0,
                'follows_and_unfollows' => $data['follows_and_unfollows'] ?? 0
            ]
        );
    }
}