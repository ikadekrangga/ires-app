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

Schedule::call(function (){
    $accounts = Account::where('status', 'active')->get(); // Ubah 'is_active' jadi 'status' => 'active'
    foreach($accounts as $account){
        $today = now()->toDateString();
        $since = now()->subDays(7)->toDateString();
        ScrapingJob::firstOrCreate([
            'account_id' => $account->id,
            'since_date' => $since,
            'until_date' => $today
        ]);
    }
})->weeklyOn(1, '08:00');


Schedule::call(function (TokenService $tokenService) {
    Log::info('Checking for expiring Meta tokens...');
    $expiringCredentials = AccountCredential::with('account')
        ->where('is_valid', true)
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now()->addDays(7))
        ->get();
    foreach ($expiringCredentials as $credential) {
        if ($credential->account->status !== 'active') continue;
        $tokenService->refreshToken($credential);
    }
})->dailyAt('02:00')->name('refresh-meta-tokens')->withoutOverlapping();
