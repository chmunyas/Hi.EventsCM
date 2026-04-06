<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\EventSeries;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\EventOccurrenceRepositoryInterface;
use HiEvents\Repository\Interfaces\EventSeriesRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEventSeriesAction extends BaseAction
{
    public function __construct(
        private readonly EventSeriesRepositoryInterface     $eventSeriesRepository,
        private readonly EventOccurrenceRepositoryInterface $eventOccurrenceRepository,
    ) {
    }

    public function __invoke(Request $request, int $event_id): JsonResponse
    {
        $this->isActionAuthorized($event_id);

        $series = $this->eventSeriesRepository->findByEventId($event_id);

        if (!$series) {
            return $this->jsonResponse(['series' => null, 'occurrences' => []]);
        }

        $occurrences = $this->eventOccurrenceRepository->findByEventId($event_id);

        return $this->jsonResponse([
            'series' => $series->toArray(),
            'occurrences' => $occurrences->map(fn($o) => $o->toArray())->toArray(),
        ]);
    }
}
