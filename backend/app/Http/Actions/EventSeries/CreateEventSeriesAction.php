<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\EventSeries;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\ResponseCodes;
use HiEvents\Services\Domain\EventSeries\OccurrenceGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CreateEventSeriesAction extends BaseAction
{
    public function __construct(
        private readonly OccurrenceGenerationService $occurrenceGenerationService,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(Request $request, int $event_id): JsonResponse
    {
        $this->isActionAuthorized($event_id);

        $validated = $request->validate([
            'recurrence_type' => 'required|string|in:daily,weekly,custom',
            'rrule' => 'nullable|string|max:500',
            'custom_dates' => 'nullable|array|min:1',
            'custom_dates.*' => 'date',
            'slots_per_day' => 'integer|min:1|max:24',
            'series_starts_at' => 'required|date',
            'series_ends_at' => 'nullable|date|after:series_starts_at',
            'default_duration_minutes' => 'integer|min:15|max:1440',
            'timezone' => 'nullable|timezone:all',
        ]);

        $series = $this->occurrenceGenerationService->createSeriesWithOccurrences(
            eventId: $event_id,
            recurrenceType: $validated['recurrence_type'],
            seriesStartsAt: $validated['series_starts_at'],
            seriesEndsAt: $validated['series_ends_at'] ?? null,
            rrule: $validated['rrule'] ?? null,
            customDates: $validated['custom_dates'] ?? null,
            slotsPerDay: $validated['slots_per_day'] ?? 1,
            defaultDurationMinutes: $validated['default_duration_minutes'] ?? 60,
            timezone: $validated['timezone'] ?? 'UTC',
        );

        return $this->jsonResponse($series->toArray(), ResponseCodes::HTTP_CREATED);
    }
}
