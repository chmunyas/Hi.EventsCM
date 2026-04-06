<?php

namespace HiEvents\Http\Actions\Events\Stats;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Http\Actions\BaseAction;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;

class GetCheckoutFunnelAction extends BaseAction
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $completedStatus = OrderStatus::COMPLETED->name;
        $cancelledStatus = OrderStatus::CANCELLED->name;
        $expiredStatus = OrderStatus::EXPIRED->name;

        $results = $this->db->select(<<<SQL
            SELECT
                COUNT(DISTINCT o.id) AS total_orders_initiated,
                COUNT(DISTINCT CASE WHEN o.status = '$completedStatus' THEN o.id END) AS completed_orders,
                COUNT(DISTINCT CASE WHEN o.status = '$cancelledStatus' THEN o.id END) AS cancelled_orders,
                COUNT(DISTINCT CASE WHEN o.status = '$expiredStatus' THEN o.id END) AS expired_orders,
                CASE
                    WHEN COUNT(DISTINCT o.id) = 0 THEN 0
                    ELSE ROUND((COUNT(DISTINCT CASE WHEN o.status = '$completedStatus' THEN o.id END)::numeric / COUNT(DISTINCT o.id)) * 100, 1)
                END AS completion_rate,
                CASE
                    WHEN COUNT(DISTINCT o.id) = 0 THEN 0
                    ELSE ROUND((COUNT(DISTINCT CASE WHEN o.status = '$cancelledStatus' THEN o.id END)::numeric / COUNT(DISTINCT o.id)) * 100, 1)
                END AS cancellation_rate,
                CASE
                    WHEN COUNT(DISTINCT o.id) = 0 THEN 0
                    ELSE ROUND((COUNT(DISTINCT CASE WHEN o.status = '$expiredStatus' THEN o.id END)::numeric / COUNT(DISTINCT o.id)) * 100, 1)
                END AS expiration_rate,
                COALESCE(SUM(CASE WHEN o.status = '$completedStatus' THEN o.total_gross ELSE 0 END), 0) AS completed_revenue,
                COALESCE(SUM(CASE WHEN o.status != '$completedStatus' THEN o.total_gross ELSE 0 END), 0) AS lost_revenue,
                COALESCE(AVG(CASE WHEN o.status = '$completedStatus'
                    THEN EXTRACT(EPOCH FROM (o.updated_at - o.created_at)) END), 0) AS avg_completion_time_seconds
            FROM orders o
            WHERE o.event_id = :event_id
                AND o.deleted_at IS NULL
SQL
        , ['event_id' => $eventId]);

        return $this->jsonResponse($results[0] ?? (object)[
            'total_orders_initiated' => 0,
            'completed_orders' => 0,
            'cancelled_orders' => 0,
            'expired_orders' => 0,
            'completion_rate' => 0,
            'cancellation_rate' => 0,
            'expiration_rate' => 0,
            'completed_revenue' => 0,
            'lost_revenue' => 0,
            'avg_completion_time_seconds' => 0,
        ]);
    }
}
