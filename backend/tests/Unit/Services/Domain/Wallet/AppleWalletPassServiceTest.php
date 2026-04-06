<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Domain\Wallet;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Services\Domain\Wallet\AppleWalletPassService;
use Tests\TestCase;

class AppleWalletPassServiceTest extends TestCase
{
    private AppleWalletPassService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AppleWalletPassService();
    }

    private function makeAttendee(array $overrides = []): AttendeeDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'short_id' => 'ATT-001',
            'public_id' => 'pub-uuid-001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'event_id' => 10,
            'status' => 'ACTIVE',
        ], $overrides);

        return AttendeeDomainObject::hydrateFromArray($data);
    }

    private function makeEvent(array $overrides = []): EventDomainObject
    {
        $data = array_merge([
            'id' => 10,
            'title' => 'Summer Music Festival',
            'start_date' => '2026-07-15T18:00:00Z',
            'end_date' => '2026-07-15T23:00:00Z',
        ], $overrides);

        return EventDomainObject::hydrateFromArray($data);
    }

    private function makeSettings(array $overrides = []): EventSettingDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'event_id' => 10,
            'support_email' => 'support@example.com',
            'location_details' => [
                'venue_name' => 'Central Park',
                'latitude' => 40.7829,
                'longitude' => -73.9654,
            ],
        ], $overrides);

        return EventSettingDomainObject::hydrateFromArray($data);
    }

    private function makeOrganizer(array $overrides = []): OrganizerDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'name' => 'EventCo Productions',
        ], $overrides);

        return OrganizerDomainObject::hydrateFromArray($data);
    }

    public function test_generates_apple_pass_with_correct_structure(): void
    {
        $attendee = $this->makeAttendee();
        $event = $this->makeEvent();
        $settings = $this->makeSettings();
        $organizer = $this->makeOrganizer();

        $result = $this->service->generatePass($attendee, $event, $settings, $organizer);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('filename', $result);
        $this->assertArrayHasKey('mime', $result);

        $passData = json_decode($result['content'], true);

        $this->assertEquals(1, $passData['formatVersion']);
        $this->assertEquals('pub-uuid-001', $passData['serialNumber']);
        $this->assertEquals('EventCo Productions', $passData['organizationName']);
        $this->assertStringContainsString('Summer Music Festival', $passData['description']);
    }

    public function test_includes_barcode_with_qr_format(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $passData = json_decode($result['content'], true);

        $this->assertEquals('pub-uuid-001', $passData['barcode']['message']);
        $this->assertEquals('PKBarcodeFormatQR', $passData['barcode']['format']);
        $this->assertCount(1, $passData['barcodes']);
    }

    public function test_includes_event_date_and_attendee_fields(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $passData = json_decode($result['content'], true);
        $eventTicket = $passData['eventTicket'];

        // Header has date
        $this->assertEquals('2026-07-15T18:00:00Z', $eventTicket['headerFields'][0]['value']);

        // Primary has event name
        $this->assertEquals('Summer Music Festival', $eventTicket['primaryFields'][0]['value']);

        // Secondary has attendee name
        $this->assertEquals('Jane Doe', $eventTicket['secondaryFields'][0]['value']);
    }

    public function test_includes_location_when_available(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $passData = json_decode($result['content'], true);

        $this->assertArrayHasKey('locations', $passData);
        $this->assertEqualsWithDelta(40.7829, $passData['locations'][0]['latitude'], 0.001);
        $this->assertEqualsWithDelta(-73.9654, $passData['locations'][0]['longitude'], 0.001);
    }

    public function test_filename_contains_short_id(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $this->assertStringContainsString('ATT-001', $result['filename']);
    }
}
