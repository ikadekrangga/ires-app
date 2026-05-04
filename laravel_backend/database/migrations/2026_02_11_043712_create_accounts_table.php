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
            $table->string('facebook_page_id')->nullable()->index();
            $table-> string("instagram_business_id")->nullable() ->unique();
            $table -> text('access_token')-> nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('token_expires_at')->nullable();
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
