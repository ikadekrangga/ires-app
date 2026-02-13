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
        Schema::create(table:'accounts', callback: function(Blueprint $table) :void {
            $table->id();
            $table->string("account_name");
            $table-> string('fb_pageId');
            $table-> string("ig_pageId")->nullable();
            $table -> text('access_token')-> nullable();
            $table -> timestamp('token_expires_at') -> nullable();
            $table -> timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
