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
        Schema::create(table:'insights', callback : function(Blueprint $table) :void {
            $table -> id();
            $table-> foreignId("account_id")->constrained('accounts') -> onDelete('cascade');
            $table -> integer('reach')->default(0);
            $table -> integer('impressions') -> default(0);
            $table -> integer('profile_views') -> default(0);
            $table -> date('report_date');
            $table -> timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insights');
    }
};
