<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\SeatingCharts;

use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Domain\Seating\SeatHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReleaseSeatHoldPublicAction extends BaseAction
{
    public function __construct(
        private readonly SeatHoldService $seatHoldService,
    ) {
    }

    public function __invoke(Request $request, int $eventId, int $seatingChartId, int $seatId): JsonResponse
    {
        $validated = $request->validate([
            'session_token' => 'required|string|max:64',
        ]);

        $released = $this->seatHoldService->releaseHold(
            seatId: $seatId,
            sessionToken: $validated['session_token'],
        );

        if (!$released) {
            return $this->errorResponse('No matching hold found', 404);
        }

        return $this->jsonResponse(['message' => 'Seat hold released']);
    }
}
