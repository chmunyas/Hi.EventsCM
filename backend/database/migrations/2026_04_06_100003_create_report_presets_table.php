<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_presets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('event_id')->nullable();
            $table->unsignedBigInteger('created_by_user_id');
            $table->string('name', 255);
            $table->string('report_type', 100);
            $table->jsonb('filters')->nullable();
            $table->jsonb('columns')->nullable();
            $table->string('sort_by', 100)->nullable();
            $table->string('sort_direction', 4)->default('asc');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['account_id', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_presets');
    }
};
