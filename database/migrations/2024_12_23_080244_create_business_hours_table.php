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
        Schema::create('business_hours', function (Blueprint $table) {

                $table->id();
                $table->foreignId('business_profile_id')->constrained()->onDelete('cascade');
                $table->string('day')->nullable(); // Sunday, Monday, etc.
                $table->string('date')->nullable(); // Sunday, Monday, etc.
                $table->boolean('is_closed')->default(false);

                $table->time('open_time')->nullable(); // Null if closed
                $table->time('close_time')->nullable(); // Null if closed
                $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_hours');
    }
};
