<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('event_series', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->string('recurrence_type', 20); // daily, weekly, custom
            $table->text('rrule')->nullable(); // RFC 5545 RRULE string
            $table->json('custom_dates')->nullable(); // for custom: array of date strings
            $table->integer('slots_per_day')->default(1);
            $table->timestamp('series_starts_at');
            $table->timestamp('series_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });

        Schema::create('event_occurrences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('event_series_id')->index();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->string('status', 20)->default('active'); // active, cancelled, sold_out
            $table->integer('capacity_override')->nullable();
            $table->decimal('price_override', 14, 2)->nullable();
            $table->json('metadata')->nullable(); // per-occurrence overrides
            $table->integer('tickets_sold')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->foreign('event_series_id')->references('id')->on('event_series')->cascadeOnDelete();
            $table->index(['event_id', 'start_date']);
            $table->index(['event_id', 'status']);
        });

        // Link orders/attendees to a specific occurrence
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('event_occurrence_id')->nullable()->after('event_id')->index();
            $table->foreign('event_occurrence_id')->references('id')->on('event_occurrences')->nullOnDelete();
        });

        Schema::table('attendees', function (Blueprint $table) {
            $table->unsignedBigInteger('event_occurrence_id')->nullable()->after('event_id')->index();
            $table->foreign('event_occurrence_id')->references('id')->on('event_occurrences')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropForeign(['event_occurrence_id']);
            $table->dropColumn('event_occurrence_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['event_occurrence_id']);
            $table->dropColumn('event_occurrence_id');
        });

        Schema::dropIfExists('event_occurrences');
        Schema::dropIfExists('event_series');
    }
};
