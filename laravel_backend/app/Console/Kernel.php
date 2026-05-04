<?php

namespace App\Console;

use App\Models\ScrapingJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
{
    $schedule->call(function () {

        $maxRetry = 3;
        $timeoutMinutes = 30;

        $count = ScrapingJob::where('status', ScrapingJob::STATUS_PROCESSING)
            ->where(function ($query) use ($timeoutMinutes) {
                $query->whereNull('started_at')
                      ->orWhere('started_at', '<', now()->subMinutes($timeoutMinutes));
            })
            ->where('attempt_count', '<', $maxRetry)
            ->update([
                'status' => ScrapingJob::STATUS_PENDING,
                'attempt_count' => DB::raw('attempt_count + 1'),
                'finished_at' => null
            ]);

        Log::info('Recovered stuck jobs', [
            'count' => $count
        ]);

    })->everyFiveMinutes();
}

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
    }
}