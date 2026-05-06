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
        // Migration ini sudah tidak diperlukan karena access_token sudah
        // dipindah ke tabel account_credentials pada arsitektur baru.
    } 

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
