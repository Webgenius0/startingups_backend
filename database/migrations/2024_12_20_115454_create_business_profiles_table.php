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
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();

            $table->integer('user_id');
            $table->string('cover')->nullable();
            $table->string('business_name')->nullable();
            $table->integer('category_id');
            // $table->integer('sub_category_id')->nullable();
            $table->enum('activity', ['Indoor', 'Outdoor'])->default('Indoor');

            $table->string('location')->nullable();

            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();

            $table->integer('view_count')->default(0);
            $table->integer('total_bookings')->default(0);
            $table->string('status')->nullable();


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
