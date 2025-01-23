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
        Schema::table('users', function (Blueprint $table) {
            // stripe account id
            $table->string('stripe_account_id')->nullable()->after('role');
            $table->string('stripe_boarding_completed')->nullable()->after('stripe_account_id');
            $table->string('google_id')->nullable()->after('stripe_account_id');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
