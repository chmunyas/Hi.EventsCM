<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\SeatingCharts;

use HiEvents\Exceptions\SeatNotAvailableException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Domain\Seating\SeatHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HoldSeatPublicAction extends BaseAction
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

        try {
            $result = $this->seatHoldService->holdSeat(
                seatId: $seatId,
                chartId: $seatingChartId,
                eventId: $eventId,
                sessionToken: $validated['session_token'],
                heldByIp: $request->ip(),
            );
        } catch (SeatNotAvailableException $e) {
            return $this->errorResponse($e->getMessage(), 422);
        }

        return $this->jsonResponse($result, 201);
    }
}
