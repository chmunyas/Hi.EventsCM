<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\Pos;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Domain\Pos\PosSessionReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetPosSessionSummaryAction extends BaseAction
{
    public function __construct(
        private readonly PosSessionReconciliationService $reconciliationService,
    ) {
    }

    public function __invoke(int $eventId, int $sessionId, Request $request): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $summary = $this->reconciliationService->generateSummary($sessionId);

        return $this->jsonResponse($summary);
    }
}
