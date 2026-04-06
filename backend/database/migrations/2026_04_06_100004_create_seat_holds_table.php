<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seat_holds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seat_id')->index();
            $table->unsignedBigInteger('seating_chart_id')->index();
            $table->unsignedBigInteger('event_id')->index();
            $table->string('session_token', 64);
            $table->string('held_by_ip', 45)->nullable();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->unique('seat_id');
            $table->foreign('seat_id')->references('id')->on('seats')->cascadeOnDelete();
            $table->foreign('seating_chart_id')->references('id')->on('seating_charts')->cascadeOnDelete();
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seat_holds');
    }
};
