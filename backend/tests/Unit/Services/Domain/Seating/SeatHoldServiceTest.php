<?php

namespace Tests\Unit\Services\Domain\Seating;

use HiEvents\Exceptions\SeatNotAvailableException;
use HiEvents\Models\Account;
use HiEvents\Models\User;
use HiEvents\Services\Domain\Seating\SeatHoldService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SeatHoldServiceTest extends TestCase
{
    private SeatHoldService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SeatHoldService();
    }

    public function test_hold_seat_creates_hold_and_changes_status(): void
    {
        // Seed required parent records
        $eventId = $this->seedEvent();
        $chartId = $this->seedChart($eventId);
        $sectionId = $this->seedSection($chartId);
        $seatId = $this->seedSeat($sectionId, $chartId);

        $result = $this->service->holdSeat(
            seatId: $seatId,
            chartId: $chartId,
            eventId: $eventId,
            sessionToken: 'test-token-abc',
        );

        $this->assertArrayHasKey('hold_id', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertEquals($seatId, $result['seat_id']);
        $this->assertEquals('test-token-abc', $result['session_token']);

        // Verify seat status changed
        $seat = DB::table('seats')->where('id', $seatId)->first();
        $this->assertEquals('held', $seat->status);

        // Verify hold record exists
        $hold = DB::table('seat_holds')->where('seat_id', $seatId)->first();
        $this->assertNotNull($hold);
        $this->assertEquals('test-token-abc', $hold->session_token);
    }

    public function test_hold_seat_throws_when_already_held(): void
    {
        $eventId = $this->seedEvent();
        $chartId = $this->seedChart($eventId);
        $sectionId = $this->seedSection($chartId);
        $seatId = $this->seedSeat($sectionId, $chartId, 'held');

        $this->expectException(SeatNotAvailableException::class);
        $this->expectExceptionMessage('not available');

        $this->service->holdSeat(
            seatId: $seatId,
            chartId: $chartId,
            eventId: $eventId,
            sessionToken: 'token-2',
        );
    }

    public function test_release_hold_returns_seat_to_available(): void
    {
        $eventId = $this->seedEvent();
        $chartId = $this->seedChart($eventId);
        $sectionId = $this->seedSection($chartId);
        $seatId = $this->seedSeat($sectionId, $chartId);

        $this->service->holdSeat(
            seatId: $seatId,
            chartId: $chartId,
            eventId: $eventId,
            sessionToken: 'release-token',
        );

        $released = $this->service->releaseHold($seatId, 'release-token');

        $this->assertTrue($released);

        $seat = DB::table('seats')->where('id', $seatId)->first();
        $this->assertEquals('available', $seat->status);

        $hold = DB::table('seat_holds')->where('seat_id', $seatId)->first();
        $this->assertNull($hold);
    }

    public function test_release_hold_returns_false_for_wrong_token(): void
    {
        $eventId = $this->seedEvent();
        $chartId = $this->seedChart($eventId);
        $sectionId = $this->seedSection($chartId);
        $seatId = $this->seedSeat($sectionId, $chartId);

        $this->service->holdSeat(
            seatId: $seatId,
            chartId: $chartId,
            eventId: $eventId,
            sessionToken: 'correct-token',
        );

        $released = $this->service->releaseHold($seatId, 'wrong-token');

        $this->assertFalse($released);
    }

    public function test_confirm_hold_transitions_to_sold(): void
    {
        $eventId = $this->seedEvent();
        $chartId = $this->seedChart($eventId);
        $sectionId = $this->seedSection($chartId);
        $seatId = $this->seedSeat($sectionId, $chartId);
        $attendeeId = $this->seedAttendee($eventId);

        $this->service->holdSeat(
            seatId: $seatId,
            chartId: $chartId,
            eventId: $eventId,
            sessionToken: 'confirm-token',
        );

        $this->service->confirmHold(
            seatId: $seatId,
            sessionToken: 'confirm-token',
            attendeeId: $attendeeId,
        );

        $seat = DB::table('seats')->where('id', $seatId)->first();
        $this->assertEquals('sold', $seat->status);
        $this->assertEquals($attendeeId, $seat->attendee_id);

        $hold = DB::table('seat_holds')->where('seat_id', $seatId)->first();
        $this->assertNull($hold);
    }

    public function test_confirm_expired_hold_throws(): void
    {
        $eventId = $this->seedEvent();
        $chartId = $this->seedChart($eventId);
        $sectionId = $this->seedSection($chartId);
        $seatId = $this->seedSeat($sectionId, $chartId, 'held');

        // Insert an already-expired hold directly
        DB::table('seat_holds')->insert([
            'seat_id' => $seatId,
            'seating_chart_id' => $chartId,
            'event_id' => $eventId,
            'session_token' => 'expired-token',
            'expires_at' => Carbon::now()->subMinutes(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->expectException(SeatNotAvailableException::class);
        $this->expectExceptionMessage('expired');

        $this->service->confirmHold(
            seatId: $seatId,
            sessionToken: 'expired-token',
            attendeeId: 999,
        );
    }

    public function test_release_expired_holds_frees_seats(): void
    {
        $eventId = $this->seedEvent();
        $chartId = $this->seedChart($eventId);
        $sectionId = $this->seedSection($chartId);
        $seatId = $this->seedSeat($sectionId, $chartId, 'held');

        // Insert an expired hold
        DB::table('seat_holds')->insert([
            'seat_id' => $seatId,
            'seating_chart_id' => $chartId,
            'event_id' => $eventId,
            'session_token' => 'expired-sweep',
            'expires_at' => Carbon::now()->subMinutes(1),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $released = $this->service->releaseExpiredHolds();

        $this->assertGreaterThanOrEqual(1, $released);

        $seat = DB::table('seats')->where('id', $seatId)->first();
        $this->assertEquals('available', $seat->status);
    }

    // ---- Seed helpers ----

    private function seedEvent(): int
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();

        $organizerId = DB::table('organizers')->insertGetId([
            'name' => 'Test Org',
            'email' => 'org-' . Str::random(8) . '@example.com',
            'timezone' => 'UTC',
            'account_id' => $account->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('events')->insertGetId([
            'title' => 'Test Event',
            'organizer_id' => $organizerId,
            'account_id' => $account->id,
            'user_id' => $user->id,
            'status' => 'LIVE',
            'short_id' => Str::random(10),
            'currency' => 'USD',
            'category' => 'OTHER',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedAccount(): int
    {
        return Account::factory()->create()->id;
    }

    private function seedChart(int $eventId): int
    {
        return DB::table('seating_charts')->insertGetId([
            'event_id' => $eventId,
            'name' => 'Main Hall',
            'total_seats' => 100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedSection(int $chartId): int
    {
        return DB::table('seating_sections')->insertGetId([
            'seating_chart_id' => $chartId,
            'name' => 'Section A',
            'capacity' => 50,
            'row_count' => 5,
            'seats_per_row' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedSeat(int $sectionId, int $chartId, string $status = 'available'): int
    {
        return DB::table('seats')->insertGetId([
            'seating_section_id' => $sectionId,
            'seating_chart_id' => $chartId,
            'row_label' => 'A',
            'seat_number' => 1,
            'label' => 'A1',
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedAttendee(int $eventId): int
    {
        $productId = DB::table('products')->insertGetId([
            'event_id' => $eventId,
            'title' => 'GA Ticket',
            'order' => 1,
            'type' => 'PAID',
            'product_type' => 'TICKET',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $priceId = DB::table('product_prices')->insertGetId([
            'product_id' => $productId,
            'price' => 0,
            'order' => 1,
            'quantity_sold' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'event_id' => $eventId,
            'short_id' => Str::random(10),
            'currency' => 'USD',
            'status' => 'COMPLETED',
            'public_id' => 'ord-' . Str::random(8),
            'locale' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('attendees')->insertGetId([
            'event_id' => $eventId,
            'order_id' => $orderId,
            'product_id' => $productId,
            'product_price_id' => $priceId,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'short_id' => 'TESTSHORT',
            'public_id' => 'pub-test-123',
            'status' => 'ACTIVE',
            'locale' => 'en',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
