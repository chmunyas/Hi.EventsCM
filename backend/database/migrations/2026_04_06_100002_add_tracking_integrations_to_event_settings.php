<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('event_settings', function (Blueprint $table) {
            $table->jsonb('tracking_integrations')->nullable()->after('show_share_buttons');
            // Structure:
            // {
            //   "ga4": { "measurement_id": "G-XXXXX", "api_secret": "...", "enabled": true },
            //   "tiktok": { "pixel_id": "...", "access_token": "...", "enabled": true },
            //   "mailchimp": { "api_key": "...", "list_id": "...", "enabled": true },
            //   "hubspot": { "api_key": "...", "enabled": true }
            // }
        });
    }

    public function down(): void
    {
        Schema::table('event_settings', function (Blueprint $table) {
            $table->dropColumn('tracking_integrations');
        });
    }
};
