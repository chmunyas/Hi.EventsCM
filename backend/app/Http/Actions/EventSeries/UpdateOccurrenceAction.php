<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\EventSeries;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Domain\EventSeries\OccurrenceGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateOccurrenceAction extends BaseAction
{
    public function __construct(
        private readonly OccurrenceGenerationService $occurrenceGenerationService,
    ) {
    }

    public function __invoke(Request $request, int $event_id, int $occurrence_id): JsonResponse
    {
        $this->isActionAuthorized($event_id);

        $validated = $request->validate([
            'capacity_override' => 'nullable|integer|min:0',
            'price_override' => 'nullable|numeric|min:0',
            'metadata' => 'nullable|array',
        ]);

        $occurrence = $this->occurrenceGenerationService->updateOccurrenceOverrides(
            occurrenceId: $occurrence_id,
            capacityOverride: $validated['capacity_override'] ?? null,
            priceOverride: isset($validated['price_override']) ? (float) $validated['price_override'] : null,
            metadata: $validated['metadata'] ?? null,
        );

        return $this->jsonResponse($occurrence->toArray());
    }
}
