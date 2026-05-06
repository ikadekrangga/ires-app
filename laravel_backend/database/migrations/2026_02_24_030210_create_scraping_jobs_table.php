<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraping_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            
            // pending, processing, success, failed, permanent_fail
            $table->string('status', 50)->default('pending')->index(); 
            
            $table->date('since_date');
            $table->date('until_date');
            
            $table->integer('attempt_count')->default(0);
            $table->integer('max_attempts')->default(3);
            
            // Kolom krusial untuk mekanisme backoff / delay retry
            $table->timestamp('next_retry_at')->useCurrent()->index();
            
            $table->text('error_reason')->nullable();
            
            // Kolom tracking eksekusi
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            // Mencegah job yang sama (rentang tanggal sama) bertumpuk di akun yang sama
            $table->unique(['account_id', 'since_date', 'until_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraping_jobs');
    }
};
