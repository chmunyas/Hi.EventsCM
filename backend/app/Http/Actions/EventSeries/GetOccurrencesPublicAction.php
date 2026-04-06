<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\EventSeries;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\EventOccurrenceRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOccurrencesPublicAction extends BaseAction
{
    public function __construct(
        private readonly EventOccurrenceRepositoryInterface $eventOccurrenceRepository,
    ) {
    }

    public function __invoke(Request $request, int $event_id): JsonResponse
    {
        $occurrences = $this->eventOccurrenceRepository->findUpcomingByEventId(
            eventId: $event_id,
            limit: (int) $request->get('limit', 50),
        );

        return $this->jsonResponse([
            'occurrences' => $occurrences->map(fn($o) => $o->toArray())->toArray(),
        ]);
    }
}
