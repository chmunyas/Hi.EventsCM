<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class CapacityUtilizationReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $completedStatus = OrderStatus::COMPLETED->name;

        return <<<SQL
        SELECT
            p.id AS product_id,
            p.title AS product_title,
            p.type AS product_type,
            COALESCE(ca.capacity, 0) AS total_capacity,
            COALESCE(sold.quantity_sold, 0) AS quantity_sold,
            CASE
                WHEN COALESCE(ca.capacity, 0) = 0 THEN 0
                ELSE GREATEST(ca.capacity - COALESCE(sold.quantity_sold, 0), 0)
            END AS remaining,
            CASE
                WHEN COALESCE(ca.capacity, 0) = 0 THEN 0
                ELSE ROUND((COALESCE(sold.quantity_sold, 0)::numeric / ca.capacity) * 100, 1)
            END AS utilization_percent
        FROM products p
        LEFT JOIN (
            SELECT
                capi.product_id,
                caa.capacity
            FROM capacity_assignment_products capi
            JOIN capacity_assignments caa ON capi.capacity_assignment_id = caa.id
            WHERE caa.event_id = :event_id AND caa.deleted_at IS NULL
        ) ca ON p.id = ca.product_id
        LEFT JOIN (
            SELECT
                oi.product_id,
                SUM(oi.quantity) AS quantity_sold
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.event_id = :event_id
                AND o.status = '$completedStatus'
                AND o.deleted_at IS NULL
            GROUP BY oi.product_id
        ) sold ON p.id = sold.product_id
        WHERE p.event_id = :event_id AND p.deleted_at IS NULL
        ORDER BY utilization_percent DESC NULLS LAST
SQL;
    }
}
