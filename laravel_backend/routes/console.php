<?php

use Illuminate\Support\Facades\Schedule;
use App\Models\Account;
use App\Models\ScrapingJob;

Schedule::call(function (){
    $accounts = Account::where('is_active', true)->get();

    foreach($accounts as $account){

        $today = now()->toDateString();
        $since = now()->subDays(7)->toDateString();

        ScrapingJob::firstOrCreate([
            'account_id' => $account->id,
            'since_date' => $since,
            'until_date' => $today
        ]);
    }

})-> weeklyOn(1, '08:00');
