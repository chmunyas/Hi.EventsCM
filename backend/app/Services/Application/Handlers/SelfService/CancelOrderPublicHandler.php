<?php

namespace HiEvents\Services\Application\Handlers\SelfService;

use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Services\Application\Handlers\SelfService\DTO\CancelOrderPublicDTO;
use HiEvents\Services\Domain\SelfService\DTO\CancelOrderResultDTO;
use HiEvents\Services\Domain\SelfService\SelfServiceCancelOrderService;

class CancelOrderPublicHandler
{
    use SelfServiceValidationTrait;

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly EventRepositoryInterface $eventRepository,
        private readonly SelfServiceCancelOrderService $selfServiceCancelOrderService,
    ) {
    }

    public function handle(CancelOrderPublicDTO $dto): CancelOrderResultDTO
    {
        $event = $this->loadAndValidateEvent($dto->eventId);
        $order = $this->loadAndValidateOrder($dto->orderShortId, $dto->eventId);

        return $this->selfServiceCancelOrderService->cancelOrder(
            order: $order,
            event: $event,
            ipAddress: $dto->ipAddress,
            userAgent: $dto->userAgent,
        );
    }
}
