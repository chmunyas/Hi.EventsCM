<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Domain\Wallet;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Services\Domain\Wallet\GoogleWalletPassService;
use Tests\TestCase;

class GoogleWalletPassServiceTest extends TestCase
{
    private GoogleWalletPassService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GoogleWalletPassService();
    }

    private function makeAttendee(array $overrides = []): AttendeeDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'short_id' => 'ATT-001',
            'public_id' => 'pub-uuid-001',
            'first_name' => 'John',
            'last_name' => 'Smith',
            'event_id' => 10,
            'status' => 'ACTIVE',
        ], $overrides);

        return AttendeeDomainObject::hydrateFromArray($data);
    }

    private function makeEvent(array $overrides = []): EventDomainObject
    {
        $data = array_merge([
            'id' => 10,
            'title' => 'Tech Conference 2026',
            'start_date' => '2026-09-20T09:00:00Z',
            'end_date' => '2026-09-20T17:00:00Z',
        ], $overrides);

        return EventDomainObject::hydrateFromArray($data);
    }

    private function makeSettings(array $overrides = []): EventSettingDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'event_id' => 10,
            'location_details' => [
                'venue_name' => 'Convention Center',
                'address_line_1' => '123 Main St',
            ],
        ], $overrides);

        return EventSettingDomainObject::hydrateFromArray($data);
    }

    private function makeOrganizer(array $overrides = []): OrganizerDomainObject
    {
        $data = array_merge([
            'id' => 1,
            'name' => 'TechEvents Inc',
        ], $overrides);

        return OrganizerDomainObject::hydrateFromArray($data);
    }

    public function test_generates_google_pass_with_save_url(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $this->assertArrayHasKey('save_url', $result);
        $this->assertArrayHasKey('jwt', $result);
        $this->assertArrayHasKey('class', $result);
        $this->assertArrayHasKey('object', $result);
        $this->assertStringStartsWith('https://pay.google.com/gp/v/save/', $result['save_url']);
    }

    public function test_class_contains_event_metadata(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $class = $result['class'];

        $this->assertEquals('TechEvents Inc', $class['issuerName']);
        $this->assertEquals('Tech Conference 2026', $class['eventName']['defaultValue']['value']);
        $this->assertEquals('2026-09-20T09:00:00Z', $class['dateTime']['start']);
        $this->assertEquals('2026-09-20T17:00:00Z', $class['dateTime']['end']);
    }

    public function test_object_contains_attendee_data(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $object = $result['object'];

        $this->assertEquals('ACTIVE', $object['state']);
        $this->assertEquals('John Smith', $object['ticketHolderName']);
        $this->assertEquals('ATT-001', $object['ticketNumber']);
        $this->assertEquals('QR_CODE', $object['barcode']['type']);
        $this->assertEquals('pub-uuid-001', $object['barcode']['value']);
    }

    public function test_includes_venue_when_location_available(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $class = $result['class'];

        $this->assertArrayHasKey('venue', $class);
        $this->assertEquals('Convention Center', $class['venue']['name']['defaultValue']['value']);
    }

    public function test_handles_event_without_end_date(): void
    {
        $result = $this->service->generatePass(
            $this->makeAttendee(),
            $this->makeEvent(['end_date' => null]),
            $this->makeSettings(),
            $this->makeOrganizer(),
        );

        $class = $result['class'];

        $this->assertArrayHasKey('dateTime', $class);
        $this->assertEquals('2026-09-20T09:00:00Z', $class['dateTime']['start']);
        $this->assertArrayNotHasKey('end', $class['dateTime']);
    }
}
