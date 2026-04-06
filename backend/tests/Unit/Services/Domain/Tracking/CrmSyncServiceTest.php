<?php

namespace Tests\Unit\Services\Domain\Tracking;

use HiEvents\Services\Domain\Tracking\CrmSyncService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CrmSyncServiceTest extends TestCase
{
    private CrmSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CrmSyncService();
    }

    public function test_syncs_to_mailchimp_when_enabled(): void
    {
        Http::fake(['*.api.mailchimp.com/*' => Http::response(['id' => 'abc'], 200)]);

        $integrations = [
            'mailchimp' => [
                'api_key' => 'testkey123-us21',
                'list_id' => 'list_abc',
                'enabled' => true,
            ],
        ];

        $contactData = [
            'email' => 'buyer@example.com',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'event_name' => 'Summer Festival',
        ];

        $this->service->syncPurchase($integrations, $contactData);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'us21.api.mailchimp.com/3.0/lists/list_abc/members/');
        });
    }

    public function test_syncs_to_hubspot_when_enabled(): void
    {
        Http::fake(['api.hubapi.com/*' => Http::response(['id' => '123'], 200)]);

        $integrations = [
            'hubspot' => [
                'api_key' => 'hs_test_token',
                'enabled' => true,
            ],
        ];

        $contactData = [
            'email' => 'buyer@example.com',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'event_name' => 'Tech Conference',
        ];

        $this->service->syncPurchase($integrations, $contactData);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.hubapi.com/crm/v3/objects/contacts')
                && $request->hasHeader('Authorization', 'Bearer hs_test_token');
        });
    }

    public function test_skips_disabled_crm_providers(): void
    {
        Http::fake();

        $integrations = [
            'mailchimp' => ['api_key' => 'key-us21', 'list_id' => 'l', 'enabled' => false],
            'hubspot' => ['api_key' => 'key', 'enabled' => false],
        ];

        $this->service->syncPurchase($integrations, ['email' => 'test@test.com']);

        Http::assertNothingSent();
    }

    public function test_skips_when_email_missing(): void
    {
        Http::fake();

        $integrations = [
            'mailchimp' => ['api_key' => 'key-us21', 'list_id' => 'l', 'enabled' => true],
        ];

        $this->service->syncPurchase($integrations, ['first_name' => 'No Email']);

        Http::assertNothingSent();
    }

    public function test_handles_empty_integrations(): void
    {
        Http::fake();

        $this->service->syncPurchase([], ['email' => 'test@test.com']);

        Http::assertNothingSent();
    }
}
