<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Domain\Pos;

use HiEvents\Services\Domain\Pos\PosSessionReconciliationService;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PosSessionReconciliationServiceTest extends TestCase
{
    private PosSessionReconciliationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PosSessionReconciliationService();
    }

    public function test_summary_with_mixed_payment_methods(): void
    {
        $transactions = collect([
            (object) ['payment_method' => 'cash', 'amount' => 25.00, 'created_at' => '2026-04-06 10:00:00'],
            (object) ['payment_method' => 'card', 'amount' => 50.00, 'created_at' => '2026-04-06 10:05:00'],
            (object) ['payment_method' => 'card', 'amount' => 30.00, 'created_at' => '2026-04-06 10:10:00'],
            (object) ['payment_method' => 'free', 'amount' => 0.00, 'created_at' => '2026-04-06 10:15:00'],
        ]);

        $summary = $this->service->computeSummary(1, $transactions);

        $this->assertEquals(1, $summary['session_id']);
        $this->assertEquals(4, $summary['total_transactions']);
        $this->assertEquals(105.00, $summary['total_sales']);
        $this->assertEquals(25.00, $summary['breakdown']['cash']);
        $this->assertEquals(80.00, $summary['breakdown']['card']);
        $this->assertEquals(0.00, $summary['breakdown']['free']);
        $this->assertEquals(1, $summary['transaction_count']['cash']);
        $this->assertEquals(2, $summary['transaction_count']['card']);
        $this->assertEquals(1, $summary['transaction_count']['free']);
    }

    public function test_summary_with_no_transactions(): void
    {
        $summary = $this->service->computeSummary(42, collect());

        $this->assertEquals(0, $summary['total_transactions']);
        $this->assertEquals(0.00, $summary['total_sales']);
        $this->assertNull($summary['first_transaction_at']);
        $this->assertNull($summary['last_transaction_at']);
    }

    public function test_summary_cash_only_session(): void
    {
        $transactions = collect([
            (object) ['payment_method' => 'cash', 'amount' => 15.00, 'created_at' => '2026-04-06 09:00:00'],
            (object) ['payment_method' => 'cash', 'amount' => 20.00, 'created_at' => '2026-04-06 09:30:00'],
        ]);

        $summary = $this->service->computeSummary(5, $transactions);

        $this->assertEquals(35.00, $summary['total_sales']);
        $this->assertEquals(35.00, $summary['breakdown']['cash']);
        $this->assertEquals(0.00, $summary['breakdown']['card']);
        $this->assertEquals(2, $summary['transaction_count']['cash']);
        $this->assertEquals(0, $summary['transaction_count']['card']);
    }

    public function test_summary_tracks_first_and_last_transaction_times(): void
    {
        $transactions = collect([
            (object) ['payment_method' => 'card', 'amount' => 10.00, 'created_at' => '2026-04-06 08:00:00'],
            (object) ['payment_method' => 'card', 'amount' => 20.00, 'created_at' => '2026-04-06 12:00:00'],
            (object) ['payment_method' => 'card', 'amount' => 15.00, 'created_at' => '2026-04-06 10:00:00'],
        ]);

        $summary = $this->service->computeSummary(1, $transactions);

        $this->assertEquals('2026-04-06 08:00:00', $summary['first_transaction_at']);
        $this->assertEquals('2026-04-06 12:00:00', $summary['last_transaction_at']);
    }

    public function test_summary_rounds_to_two_decimal_places(): void
    {
        $transactions = collect([
            (object) ['payment_method' => 'card', 'amount' => 33.333, 'created_at' => '2026-04-06 10:00:00'],
            (object) ['payment_method' => 'card', 'amount' => 33.333, 'created_at' => '2026-04-06 10:01:00'],
            (object) ['payment_method' => 'card', 'amount' => 33.334, 'created_at' => '2026-04-06 10:02:00'],
        ]);

        $summary = $this->service->computeSummary(1, $transactions);

        $this->assertEquals(100.00, $summary['total_sales']);
        $this->assertEquals(100.00, $summary['breakdown']['card']);
    }
}
