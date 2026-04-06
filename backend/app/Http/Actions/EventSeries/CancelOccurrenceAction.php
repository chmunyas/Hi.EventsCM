<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\EventSeries;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Domain\EventSeries\OccurrenceGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CancelOccurrenceAction extends BaseAction
{
    public function __construct(
        private readonly OccurrenceGenerationService $occurrenceGenerationService,
    ) {
    }

    public function __invoke(Request $request, int $event_id, int $occurrence_id): JsonResponse
    {
        $this->isActionAuthorized($event_id);

        $occurrence = $this->occurrenceGenerationService->cancelOccurrence($occurrence_id);

        return $this->jsonResponse($occurrence->toArray());
    }
}
