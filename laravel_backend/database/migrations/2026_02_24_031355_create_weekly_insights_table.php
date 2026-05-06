<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->date('since_date');
            $table->date('until_date');
            
            // Gunakan JSONB untuk PostgreSQL agar bisa dikueri ke dalam JSON-nya jika perlu
            $table->jsonb('metrics'); 
            
            $table->timestamps();

            $table->unique(['account_id', 'since_date', 'until_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_insights');
    }
};
