<?php

namespace Tests\Unit\Services\Domain\Badge;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\ProductDomainObject;
use HiEvents\Services\Domain\Badge\BadgeRenderService;
use Tests\TestCase;

class BadgeRenderServiceTest extends TestCase
{
    private BadgeRenderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BadgeRenderService();
    }

    public function test_render_badge_substitutes_name_placeholders(): void
    {
        $template = '<div>{{first_name}} {{last_name}}</div>';
        $attendee = $this->makeAttendee('Alice', 'Smith', 'alice@example.com');

        $html = $this->service->renderBadge($template, $attendee);

        $this->assertStringContainsString('Alice', $html);
        $this->assertStringContainsString('Smith', $html);
        $this->assertStringNotContainsString('{{first_name}}', $html);
    }

    public function test_render_badge_substitutes_email_and_short_id(): void
    {
        $template = '<div>{{email}} - {{short_id}}</div>';
        $attendee = $this->makeAttendee('Bob', 'Jones', 'bob@test.com', 'SHORT123');

        $html = $this->service->renderBadge($template, $attendee);

        $this->assertStringContainsString('bob@test.com', $html);
        $this->assertStringContainsString('SHORT123', $html);
    }

    public function test_render_badge_inserts_qr_code_url(): void
    {
        $template = '<img src="{{qr_code}}" />';
        $attendee = $this->makeAttendee('Eve', 'Clark', 'eve@test.com', 'QR_DATA_1');

        $html = $this->service->renderBadge($template, $attendee);

        $this->assertStringContainsString('chart.googleapis.com', $html);
        $this->assertStringContainsString('QR_DATA_1', $html);
    }

    public function test_render_badge_escapes_html_in_values(): void
    {
        $template = '<div>{{first_name}}</div>';
        $attendee = $this->makeAttendee('<script>alert(1)</script>', 'Test', 'test@t.com');

        $html = $this->service->renderBadge($template, $attendee);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_render_badges_wraps_each_with_page_break(): void
    {
        $template = '<span>{{first_name}}</span>';
        $attendees = [
            $this->makeAttendee('One', 'A', 'one@t.com'),
            $this->makeAttendee('Two', 'B', 'two@t.com'),
        ];

        $html = $this->service->renderBadges($template, $attendees);

        $this->assertStringContainsString('page-break-after', $html);
        $this->assertStringContainsString('One', $html);
        $this->assertStringContainsString('Two', $html);
        $this->assertStringContainsString('<!DOCTYPE html>', $html);
    }

    public function test_render_badge_handles_null_product_gracefully(): void
    {
        $template = '<div>{{product_name}}</div>';
        $attendee = $this->makeAttendee('Pat', 'Lee', 'pat@t.com');

        $html = $this->service->renderBadge($template, $attendee);

        $this->assertStringNotContainsString('{{product_name}}', $html);
    }

    public function test_get_available_placeholders_returns_array(): void
    {
        $placeholders = $this->service->getAvailablePlaceholders();

        $this->assertIsArray($placeholders);
        $this->assertContains('{{first_name}}', $placeholders);
        $this->assertContains('{{qr_code}}', $placeholders);
    }

    private function makeAttendee(
        string $firstName,
        string $lastName,
        string $email,
        string $shortId = 'ABC123',
    ): AttendeeDomainObject {
        $attendee = AttendeeDomainObject::hydrateFromArray([
            'id' => 1,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'short_id' => $shortId,
            'public_id' => 'pub-' . $shortId,
            'status' => 'ACTIVE',
            'event_id' => 10,
        ]);

        return $attendee;
    }
}
