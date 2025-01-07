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
            $table->string('type'); //

            $table->integer('user_id');
            $table->string('cover')->nullable(); // profile and event
            $table->string('business_name')->nullable(); //
            $table->integer('category_id');
            $table->integer('sub_category_id')->nullable();
            $table->enum('activity', ['Indoor', 'Outdoor'])->default('Indoor');

            $table->string('location')->nullable();


            $table->string('title')->nullable(); //events

            $table->text('description')->nullable(); // events
            $table->date('date')->nullable(); // events
            $table->time('start_time')->nullable(); //events
            $table->time('end_time')->nullable(); // events
            $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly'])->nullable;// events
            $table->integer('frequency_count')->nullable(); // events
            $table->integer('frequency_end_after')->nullable(); // events
            $table->date('frequency_end_date')->nullable(); // events
            $table->string('location_type')->default('physical'); // events
            $table->string('location_address')->nullable(); // events
            $table->string('amount')->nullable(); // events
            $table->text('offerings')->nullable(); // events
            $table->boolean('has_guests')->default(false); // events
            $table->json('guest_list')->nullable();     // events
            $table->json('guest_options')->nullable(); // events
            $table->text('note_for_guests')->nullable();     // events


            // view counts
            $table->integer('view_count')->default(0);
            $table->integer('total_bookings')->default(0);


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
