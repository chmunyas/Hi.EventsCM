<?php

namespace Tests\Unit\Services\Domain\Tracking;

use HiEvents\Services\Domain\Tracking\TrackingIntegrationService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TrackingIntegrationServiceTest extends TestCase
{
    private TrackingIntegrationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TrackingIntegrationService();
    }

    public function test_fires_ga4_event_when_enabled(): void
    {
        Http::fake(['google-analytics.com/*' => Http::response('', 204)]);

        $integrations = [
            'ga4' => [
                'measurement_id' => 'G-TEST123',
                'api_secret' => 'test_secret',
                'enabled' => true,
            ],
        ];

        $eventData = [
            'order_id' => 'ORD-001',
            'total' => 99.99,
            'currency' => 'USD',
            'items' => [
                ['product_id' => 1, 'product_name' => 'VIP Ticket', 'quantity' => 2, 'price' => 49.99],
            ],
        ];

        $this->service->firePurchaseEvent($integrations, $eventData);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'google-analytics.com/mp/collect')
                && str_contains($request->url(), 'measurement_id=G-TEST123');
        });
    }

    public function test_fires_tiktok_event_when_enabled(): void
    {
        Http::fake(['business-api.tiktok.com/*' => Http::response(['code' => 0], 200)]);

        $integrations = [
            'tiktok' => [
                'pixel_id' => 'TIKTOK_PX_001',
                'access_token' => 'tiktok_test_token',
                'enabled' => true,
            ],
        ];

        $eventData = [
            'order_id' => 'ORD-002',
            'total' => 50.00,
            'currency' => 'GBP',
            'items' => [
                ['product_id' => 2, 'product_name' => 'GA Ticket', 'quantity' => 1, 'price' => 50.00],
            ],
        ];

        $this->service->firePurchaseEvent($integrations, $eventData);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'business-api.tiktok.com')
                && $request->hasHeader('Access-Token', 'tiktok_test_token');
        });
    }

    public function test_skips_disabled_providers(): void
    {
        Http::fake();

        $integrations = [
            'ga4' => [
                'measurement_id' => 'G-TEST123',
                'api_secret' => 'test_secret',
                'enabled' => false,
            ],
            'tiktok' => [
                'pixel_id' => 'PX_001',
                'access_token' => 'tok',
                'enabled' => false,
            ],
        ];

        $this->service->firePurchaseEvent($integrations, ['order_id' => '1', 'total' => 10]);

        Http::assertNothingSent();
    }

    public function test_skips_missing_credentials(): void
    {
        Http::fake();

        // ga4 enabled but missing api_secret
        $integrations = [
            'ga4' => [
                'measurement_id' => 'G-TEST123',
                'enabled' => true,
            ],
        ];

        $this->service->firePurchaseEvent($integrations, ['order_id' => '1', 'total' => 10]);

        Http::assertNothingSent();
    }

    public function test_handles_empty_integrations(): void
    {
        Http::fake();

        $this->service->firePurchaseEvent([], ['order_id' => '1']);

        Http::assertNothingSent();
    }
}
