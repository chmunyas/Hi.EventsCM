<?php

declare(strict_types=1);

namespace HiEvents\Services\Domain\EventSeries;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use HiEvents\DomainObjects\EventOccurrenceDomainObject;
use HiEvents\DomainObjects\EventSeriesDomainObject;
use HiEvents\Repository\Interfaces\EventOccurrenceRepositoryInterface;
use HiEvents\Repository\Interfaces\EventSeriesRepositoryInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Throwable;

class OccurrenceGenerationService
{
    public function __construct(
        private readonly EventSeriesRepositoryInterface     $eventSeriesRepository,
        private readonly EventOccurrenceRepositoryInterface $eventOccurrenceRepository,
        private readonly DatabaseManager                    $databaseManager,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function createSeriesWithOccurrences(
        int     $eventId,
        string  $recurrenceType,
        string  $seriesStartsAt,
        ?string $seriesEndsAt = null,
        ?string $rrule = null,
        ?array  $customDates = null,
        int     $slotsPerDay = 1,
        ?int    $defaultDurationMinutes = 60,
        ?string $timezone = 'UTC',
    ): EventSeriesDomainObject {
        $this->validateInput($recurrenceType, $rrule, $customDates);

        return $this->databaseManager->transaction(function () use (
            $eventId, $recurrenceType, $seriesStartsAt, $seriesEndsAt,
            $rrule, $customDates, $slotsPerDay, $defaultDurationMinutes, $timezone
        ) {
            $series = $this->eventSeriesRepository->create([
                'event_id' => $eventId,
                'recurrence_type' => $recurrenceType,
                'rrule' => $rrule,
                'custom_dates' => $customDates,
                'slots_per_day' => $slotsPerDay,
                'series_starts_at' => $seriesStartsAt,
                'series_ends_at' => $seriesEndsAt,
                'is_active' => true,
            ]);

            $dates = $this->expandDates($recurrenceType, $seriesStartsAt, $seriesEndsAt, $rrule, $customDates);

            foreach ($dates as $date) {
                for ($slot = 0; $slot < $slotsPerDay; $slot++) {
                    $startDate = Carbon::parse($date, $timezone)->addMinutes($slot * $defaultDurationMinutes);
                    $endDate = (clone $startDate)->addMinutes($defaultDurationMinutes);

                    $this->eventOccurrenceRepository->create([
                        'event_id' => $eventId,
                        'event_series_id' => $series->getId(),
                        'start_date' => $startDate->utc()->toDateTimeString(),
                        'end_date' => $endDate->utc()->toDateTimeString(),
                        'status' => 'active',
                        'tickets_sold' => 0,
                    ]);
                }
            }

            return $series;
        });
    }

    public function cancelOccurrence(int $occurrenceId): EventOccurrenceDomainObject
    {
        $this->eventOccurrenceRepository->updateWhere(
            attributes: ['status' => 'cancelled'],
            where: [['id', '=', $occurrenceId]],
        );

        return $this->eventOccurrenceRepository->findById($occurrenceId);
    }

    public function updateOccurrenceOverrides(
        int    $occurrenceId,
        ?int   $capacityOverride = null,
        ?float $priceOverride = null,
        ?array $metadata = null,
    ): EventOccurrenceDomainObject {
        $updates = array_filter([
            'capacity_override' => $capacityOverride,
            'price_override' => $priceOverride,
            'metadata' => $metadata,
        ], fn($v) => $v !== null);

        $this->eventOccurrenceRepository->updateWhere(
            attributes: $updates,
            where: [['id', '=', $occurrenceId]],
        );

        return $this->eventOccurrenceRepository->findById($occurrenceId);
    }

    /**
     * @return string[] Array of date strings (Y-m-d format)
     */
    public function expandDates(
        string  $recurrenceType,
        string  $startsAt,
        ?string $endsAt,
        ?string $rrule,
        ?array  $customDates,
    ): array {
        return match ($recurrenceType) {
            'daily' => $this->expandDaily($startsAt, $endsAt),
            'weekly' => $this->expandWeekly($startsAt, $endsAt, $rrule),
            'custom' => $this->expandCustom($customDates),
            default => throw new InvalidArgumentException("Unsupported recurrence type: {$recurrenceType}"),
        };
    }

    private function expandDaily(string $startsAt, ?string $endsAt): array
    {
        if (!$endsAt) {
            throw new InvalidArgumentException('Daily recurrence requires an end date');
        }

        $period = CarbonPeriod::create(
            Carbon::parse($startsAt)->startOfDay(),
            '1 day',
            Carbon::parse($endsAt)->startOfDay()
        );

        return collect(iterator_to_array($period))
            ->map(fn(Carbon $date) => $date->format('Y-m-d'))
            ->values()
            ->all();
    }

    private function expandWeekly(string $startsAt, ?string $endsAt, ?string $rrule): array
    {
        if (!$endsAt) {
            throw new InvalidArgumentException('Weekly recurrence requires an end date');
        }

        // Parse BYDAY from RRULE if present (e.g., RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR)
        $daysOfWeek = $this->parseDaysFromRrule($rrule);

        $start = Carbon::parse($startsAt)->startOfDay();
        $end = Carbon::parse($endsAt)->startOfDay();
        $dates = [];

        $current = $start->copy();
        while ($current->lte($end)) {
            if (empty($daysOfWeek) || in_array($current->dayOfWeekIso, $daysOfWeek, true)) {
                $dates[] = $current->format('Y-m-d');
            }
            $current->addDay();
        }

        return $dates;
    }

    private function expandCustom(?array $customDates): array
    {
        if (empty($customDates)) {
            throw new InvalidArgumentException('Custom recurrence requires at least one date');
        }

        return collect($customDates)
            ->map(fn(string $date) => Carbon::parse($date)->format('Y-m-d'))
            ->sort()
            ->values()
            ->all();
    }

    /**
     * Parse BYDAY components from an RRULE string into ISO day-of-week integers.
     * E.g., "RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR" → [1, 3, 5]
     */
    private function parseDaysFromRrule(?string $rrule): array
    {
        if (!$rrule) {
            return [];
        }

        $dayMap = [
            'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4,
            'FR' => 5, 'SA' => 6, 'SU' => 7,
        ];

        if (preg_match('/BYDAY=([A-Z,]+)/i', $rrule, $matches)) {
            $parts = explode(',', $matches[1]);
            return array_values(array_filter(
                array_map(fn(string $d) => $dayMap[strtoupper(trim($d))] ?? null, $parts)
            ));
        }

        return [];
    }

    private function validateInput(string $recurrenceType, ?string $rrule, ?array $customDates): void
    {
        if (!in_array($recurrenceType, ['daily', 'weekly', 'custom'], true)) {
            throw new InvalidArgumentException("Invalid recurrence type: {$recurrenceType}");
        }

        if ($recurrenceType === 'custom' && empty($customDates)) {
            throw new InvalidArgumentException('Custom recurrence requires custom_dates');
        }
    }
}
