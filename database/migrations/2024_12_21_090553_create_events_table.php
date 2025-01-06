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
        Schema::create('events', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('cover')->nullable();
            $table->string('title');
            $table->integer('category_id');
            $table->integer('user_id');
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->text('description')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly'])->default('once');
            $table->integer('frequency_count')->nullable();
            $table->integer('frequency_end_after')->nullable();
            $table->date('frequency_end_date')->nullable();
            $table->string('location_type')->default('physical');
            $table->string('location_address')->nullable();
            $table->string('amount')->nullable();
            $table->text('offerings')->nullable();
            $table->boolean('has_guests')->default(false);
            $table->json('guest_list')->nullable();
            $table->json('guest_options')->nullable();
            $table->text('note_for_guests')->nullable();


            // view counts
            $table->integer('view_count')->default(0);
            $table->integer('total_bookings')->default(0);



            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
