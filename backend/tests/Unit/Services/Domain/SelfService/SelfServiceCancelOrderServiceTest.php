<?php

namespace Tests\Unit\Services\Domain\SelfService;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\OrderDomainObject;
use HiEvents\DomainObjects\Status\OrderRefundStatus;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Exceptions\ResourceConflictException;
use HiEvents\Services\Application\Handlers\Order\CancelOrderHandler;
use HiEvents\Services\Application\Handlers\Order\DTO\CancelOrderDTO;
use HiEvents\Services\Domain\SelfService\OrderAuditLogService;
use HiEvents\Services\Domain\SelfService\SelfServiceCancelOrderService;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SelfServiceCancelOrderServiceTest extends TestCase
{
    private SelfServiceCancelOrderService $service;
    private MockInterface|CancelOrderHandler $cancelOrderHandler;
    private MockInterface|OrderAuditLogService $orderAuditLogService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cancelOrderHandler = Mockery::mock(CancelOrderHandler::class);
        $this->orderAuditLogService = Mockery::mock(OrderAuditLogService::class);

        $this->service = new SelfServiceCancelOrderService(
            $this->cancelOrderHandler,
            $this->orderAuditLogService,
        );
    }

    public function testCancelsFutureRefundableOrderAndLogsAuditEntry(): void
    {
        $event = Mockery::mock(EventDomainObject::class);
        $event->shouldReceive('getId')->andReturn(789);
        $event->shouldReceive('isEventInFuture')->andReturnTrue();

        $order = Mockery::mock(OrderDomainObject::class);
        $order->shouldReceive('isOrderCancelled')->andReturnFalse();
        $order->shouldReceive('getStatus')->andReturn(OrderStatus::COMPLETED->name);
        $order->shouldReceive('isRefundable')->andReturnTrue();
        $order->shouldReceive('getRefundStatus')->andReturn(null);
        $order->shouldReceive('getTotalRefunded')->andReturn(0.0);
        $order->shouldReceive('getId')->andReturn(123);

        $updatedOrder = Mockery::mock(OrderDomainObject::class);
        $updatedOrder->shouldReceive('getStatus')->andReturn(OrderStatus::CANCELLED->name);
        $updatedOrder->shouldReceive('getRefundStatus')->andReturn(OrderRefundStatus::REFUNDED->name);
        $updatedOrder->shouldReceive('getTotalRefunded')->andReturn(99.99);

        $this->cancelOrderHandler
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function (CancelOrderDTO $dto) {
                return $dto->eventId === 789
                    && $dto->orderId === 123
                    && $dto->refund === true;
            })
            ->andReturn($updatedOrder);

        $this->orderAuditLogService
            ->shouldReceive('logOrderSelfCancelled')
            ->once()
            ->withArgs(function ($loggedOrder, $oldValues, $newValues, $ipAddress, $userAgent) use ($updatedOrder) {
                return $loggedOrder === $updatedOrder
                    && $oldValues['status'] === OrderStatus::COMPLETED->name
                    && $oldValues['refund_status'] === null
                    && $oldValues['total_refunded'] === 0.0
                    && $newValues['status'] === OrderStatus::CANCELLED->name
                    && $newValues['refund_status'] === OrderRefundStatus::REFUNDED->name
                    && $newValues['total_refunded'] === 99.99
                    && $ipAddress === '192.168.1.1'
                    && $userAgent === 'Mozilla/5.0';
            });

        $result = $this->service->cancelOrder(
            order: $order,
            event: $event,
            ipAddress: '192.168.1.1',
            userAgent: 'Mozilla/5.0',
        );

        $this->assertTrue($result->success);
        $this->assertTrue($result->refunded);
    }

    public function testCancelsFutureNonRefundableOrderWithoutRefund(): void
    {
        $event = Mockery::mock(EventDomainObject::class);
        $event->shouldReceive('getId')->andReturn(321);
        $event->shouldReceive('isEventInFuture')->andReturnTrue();

        $order = Mockery::mock(OrderDomainObject::class);
        $order->shouldReceive('isOrderCancelled')->andReturnFalse();
        $order->shouldReceive('getStatus')->andReturn(OrderStatus::AWAITING_OFFLINE_PAYMENT->name);
        $order->shouldReceive('isRefundable')->andReturnFalse();
        $order->shouldReceive('getRefundStatus')->andReturn(null);
        $order->shouldReceive('getTotalRefunded')->andReturn(0.0);
        $order->shouldReceive('getId')->andReturn(555);

        $updatedOrder = Mockery::mock(OrderDomainObject::class);
        $updatedOrder->shouldReceive('getStatus')->andReturn(OrderStatus::CANCELLED->name);
        $updatedOrder->shouldReceive('getRefundStatus')->andReturn(null);
        $updatedOrder->shouldReceive('getTotalRefunded')->andReturn(0.0);

        $this->cancelOrderHandler
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function (CancelOrderDTO $dto) {
                return $dto->eventId === 321
                    && $dto->orderId === 555
                    && $dto->refund === false;
            })
            ->andReturn($updatedOrder);

        $this->orderAuditLogService
            ->shouldReceive('logOrderSelfCancelled')
            ->once();

        $result = $this->service->cancelOrder(
            order: $order,
            event: $event,
            ipAddress: '192.168.1.1',
            userAgent: null,
        );

        $this->assertTrue($result->success);
        $this->assertFalse($result->refunded);
    }

    public function testRejectsSelfServiceCancellationForPastEvents(): void
    {
        $event = Mockery::mock(EventDomainObject::class);
        $event->shouldReceive('isEventInFuture')->andReturnFalse();

        $order = Mockery::mock(OrderDomainObject::class);
        $order->shouldReceive('isOrderCancelled')->andReturnFalse();

        $this->cancelOrderHandler->shouldReceive('handle')->never();
        $this->orderAuditLogService->shouldReceive('logOrderSelfCancelled')->never();

        $this->expectException(ResourceConflictException::class);
        $this->expectExceptionMessage('Self-service cancellation is only available before the event starts');

        $this->service->cancelOrder(
            order: $order,
            event: $event,
            ipAddress: '192.168.1.1',
            userAgent: null,
        );
    }

    public function testRejectsUnsupportedOrderStatuses(): void
    {
        $event = Mockery::mock(EventDomainObject::class);
        $event->shouldReceive('isEventInFuture')->andReturnTrue();

        $order = Mockery::mock(OrderDomainObject::class);
        $order->shouldReceive('isOrderCancelled')->andReturnFalse();
        $order->shouldReceive('getStatus')->andReturn(OrderStatus::RESERVED->name);

        $this->cancelOrderHandler->shouldReceive('handle')->never();
        $this->orderAuditLogService->shouldReceive('logOrderSelfCancelled')->never();

        $this->expectException(ResourceConflictException::class);
        $this->expectExceptionMessage('This order cannot be cancelled through self-service');

        $this->service->cancelOrder(
            order: $order,
            event: $event,
            ipAddress: '192.168.1.1',
            userAgent: null,
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
