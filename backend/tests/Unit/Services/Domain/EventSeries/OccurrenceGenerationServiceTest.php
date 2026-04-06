<?php

namespace Tests\Unit\Services\Domain\EventSeries;

use HiEvents\DomainObjects\EventOccurrenceDomainObject;
use HiEvents\DomainObjects\EventSeriesDomainObject;
use HiEvents\Repository\Interfaces\EventOccurrenceRepositoryInterface;
use HiEvents\Repository\Interfaces\EventSeriesRepositoryInterface;
use HiEvents\Services\Domain\EventSeries\OccurrenceGenerationService;
use Illuminate\Database\DatabaseManager;
use Mockery;
use Tests\TestCase;

class OccurrenceGenerationServiceTest extends TestCase
{
    private OccurrenceGenerationService $service;
    private EventSeriesRepositoryInterface $seriesRepo;
    private EventOccurrenceRepositoryInterface $occurrenceRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seriesRepo = Mockery::mock(EventSeriesRepositoryInterface::class);
        $this->occurrenceRepo = Mockery::mock(EventOccurrenceRepositoryInterface::class);

        $dbManager = Mockery::mock(DatabaseManager::class);
        $dbManager->shouldReceive('transaction')->andReturnUsing(fn($callback) => $callback());

        $this->service = new OccurrenceGenerationService(
            $this->seriesRepo,
            $this->occurrenceRepo,
            $dbManager,
        );
    }

    public function test_expand_daily_dates(): void
    {
        $dates = $this->service->expandDates(
            recurrenceType: 'daily',
            startsAt: '2026-07-01',
            endsAt: '2026-07-05',
            rrule: null,
            customDates: null,
        );

        $this->assertCount(5, $dates);
        $this->assertEquals('2026-07-01', $dates[0]);
        $this->assertEquals('2026-07-05', $dates[4]);
    }

    public function test_expand_weekly_with_byday(): void
    {
        $dates = $this->service->expandDates(
            recurrenceType: 'weekly',
            startsAt: '2026-07-06', // Monday
            endsAt: '2026-07-12',   // Sunday
            rrule: 'RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR',
            customDates: null,
        );

        $this->assertCount(3, $dates);
        $this->assertEquals('2026-07-06', $dates[0]); // Monday
        $this->assertEquals('2026-07-08', $dates[1]); // Wednesday
        $this->assertEquals('2026-07-10', $dates[2]); // Friday
    }

    public function test_expand_custom_dates(): void
    {
        $dates = $this->service->expandDates(
            recurrenceType: 'custom',
            startsAt: '2026-07-01',
            endsAt: null,
            rrule: null,
            customDates: ['2026-08-15', '2026-07-10', '2026-09-20'],
        );

        $this->assertCount(3, $dates);
        // Should be sorted
        $this->assertEquals('2026-07-10', $dates[0]);
        $this->assertEquals('2026-08-15', $dates[1]);
        $this->assertEquals('2026-09-20', $dates[2]);
    }

    public function test_create_series_with_daily_occurrences(): void
    {
        $mockSeries = (new EventSeriesDomainObject())
            ->setId(1)
            ->setEventId(10)
            ->setRecurrenceType('daily')
            ->setSeriesStartsAt('2026-07-01')
            ->setSeriesEndsAt('2026-07-03');

        $this->seriesRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockSeries);

        // 3 days × 1 slot = 3 occurrences
        $this->occurrenceRepo->shouldReceive('create')
            ->times(3)
            ->andReturn(new EventOccurrenceDomainObject());

        $result = $this->service->createSeriesWithOccurrences(
            eventId: 10,
            recurrenceType: 'daily',
            seriesStartsAt: '2026-07-01',
            seriesEndsAt: '2026-07-03',
        );

        $this->assertEquals(1, $result->getId());
    }

    public function test_create_series_with_multiple_slots_per_day(): void
    {
        $mockSeries = (new EventSeriesDomainObject())
            ->setId(2)
            ->setEventId(10)
            ->setRecurrenceType('daily')
            ->setSlotsPerDay(3)
            ->setSeriesStartsAt('2026-07-01')
            ->setSeriesEndsAt('2026-07-02');

        $this->seriesRepo->shouldReceive('create')
            ->once()
            ->andReturn($mockSeries);

        // 2 days × 3 slots = 6 occurrences
        $this->occurrenceRepo->shouldReceive('create')
            ->times(6)
            ->andReturn(new EventOccurrenceDomainObject());

        $result = $this->service->createSeriesWithOccurrences(
            eventId: 10,
            recurrenceType: 'daily',
            seriesStartsAt: '2026-07-01',
            seriesEndsAt: '2026-07-02',
            slotsPerDay: 3,
        );

        $this->assertEquals(2, $result->getId());
    }

    public function test_invalid_recurrence_type_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createSeriesWithOccurrences(
            eventId: 10,
            recurrenceType: 'biweekly',
            seriesStartsAt: '2026-07-01',
        );
    }

    public function test_custom_without_dates_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->service->createSeriesWithOccurrences(
            eventId: 10,
            recurrenceType: 'custom',
            seriesStartsAt: '2026-07-01',
            customDates: [],
        );
    }

    public function test_cancel_occurrence(): void
    {
        $cancelled = (new EventOccurrenceDomainObject())
            ->setId(5)
            ->setStatus('cancelled');

        $this->occurrenceRepo->shouldReceive('updateWhere')
            ->once()
            ->with(
                Mockery::on(fn($attrs) => $attrs['status'] === 'cancelled'),
                Mockery::any(),
            )
            ->andReturn(1);

        $this->occurrenceRepo->shouldReceive('findById')
            ->once()
            ->with(5)
            ->andReturn($cancelled);

        $result = $this->service->cancelOccurrence(5);
        $this->assertEquals('cancelled', $result->getStatus());
    }

    public function test_update_occurrence_overrides(): void
    {
        $updated = (new EventOccurrenceDomainObject())
            ->setId(5)
            ->setCapacityOverride(100)
            ->setPriceOverride(25.00);

        $this->occurrenceRepo->shouldReceive('updateWhere')
            ->once()
            ->andReturn(1);

        $this->occurrenceRepo->shouldReceive('findById')
            ->once()
            ->with(5)
            ->andReturn($updated);

        $result = $this->service->updateOccurrenceOverrides(
            occurrenceId: 5,
            capacityOverride: 100,
            priceOverride: 25.00,
        );

        $this->assertEquals(100, $result->getCapacityOverride());
        $this->assertEquals(25.00, $result->getPriceOverride());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
