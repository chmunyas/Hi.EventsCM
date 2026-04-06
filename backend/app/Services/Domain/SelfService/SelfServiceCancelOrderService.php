<?php

namespace HiEvents\Services\Domain\SelfService;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Exceptions\ResourceConflictException;
use HiEvents\Services\Application\Handlers\Order\CancelOrderHandler;
use HiEvents\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use HiEvents\Services\Domain\SelfService\DTO\CancelOrderResultDTO;
use Throwable;

class SelfServiceCancelOrderService
{
    public function __construct(
        private readonly CancelOrderHandler $cancelOrderHandler,
        private readonly OrderAuditLogService $orderAuditLogService,
    ) {
    }

    /**
     * @throws ResourceConflictException
     * @throws Throwable
     */
    public function cancelOrder(
        OrderDomainObject $order,
        EventDomainObject $event,
        string $ipAddress,
        ?string $userAgent
    ): CancelOrderResultDTO {
        if ($order->isOrderCancelled()) {
            throw new ResourceConflictException(__('Order already cancelled'));
        }

        if (!$event->isEventInFuture()) {
            throw new ResourceConflictException(__('Self-service cancellation is only available before the event starts'));
        }

        if (!in_array($order->getStatus(), [
            OrderStatus::COMPLETED->name,
            OrderStatus::AWAITING_OFFLINE_PAYMENT->name,
            OrderStatus::AWAITING_APPROVAL->name,
        ], true)) {
            throw new ResourceConflictException(__('This order cannot be cancelled through self-service'));
        }

        $shouldRefund = $order->isRefundable();

        $oldValues = [
            'status' => $order->getStatus(),
            'refund_status' => $order->getRefundStatus(),
            'total_refunded' => $order->getTotalRefunded(),
        ];

        $updatedOrder = $this->cancelOrderHandler->handle(new CancelOrderDTO(
            eventId: $event->getId(),
            orderId: $order->getId(),
            refund: $shouldRefund,
        ));

        $newValues = [
            'status' => $updatedOrder->getStatus(),
            'refund_status' => $updatedOrder->getRefundStatus(),
            'total_refunded' => $updatedOrder->getTotalRefunded(),
        ];

        $this->orderAuditLogService->logOrderSelfCancelled(
            order: $updatedOrder,
            oldValues: $oldValues,
            newValues: $newValues,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        return new CancelOrderResultDTO(
            success: true,
            refunded: $shouldRefund,
        );
    }
}
