<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scraping_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
            'pending',
            'processing',
            'success',
            'failed_retryable',
            'failed_non_retryable'
            ])->default('pending');
            $table->integer('attempt_count')->default(0)->index();
            $table->date("since_date");
            $table->date('until_date');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text("last_error")->nullable();

            $table->timestamps();
            $table->unique(['account_id', 'since_date', 'until_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scraping_jobs');
    }
};
