<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Feature #15: Low-capacity alerts
        Schema::table('event_settings', function (Blueprint $table) {
            $table->boolean('low_capacity_alerts_enabled')->default(false);
            $table->json('low_capacity_alert_sent_thresholds')->nullable();
        });

        // Feature #17: Invoice line item customization
        Schema::table('event_settings', function (Blueprint $table) {
            $table->boolean('invoice_hide_tax_details')->default(false);
            $table->boolean('invoice_show_fees_separately')->default(false);
            $table->string('invoice_custom_label', 255)->nullable();
            $table->text('invoice_company_info')->nullable();
        });

        // Feature #18: Event tags/categories for discovery
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'tags')) {
                $table->jsonb('tags')->nullable();
            }
        });

        // Feature #16: Waitlist auto-offer config (extend existing settings)
        Schema::table('event_settings', function (Blueprint $table) {
            $table->integer('waitlist_auto_offer_seats')->nullable();
            $table->integer('waitlist_auto_offer_delay_minutes')->nullable()->default(5);
        });
    }

    public function down(): void
    {
        Schema::table('event_settings', function (Blueprint $table) {
            $table->dropColumn([
                'low_capacity_alerts_enabled',
                'low_capacity_alert_sent_thresholds',
                'invoice_hide_tax_details',
                'invoice_show_fees_separately',
                'invoice_custom_label',
                'invoice_company_info',
                'waitlist_auto_offer_seats',
                'waitlist_auto_offer_delay_minutes',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }
};
