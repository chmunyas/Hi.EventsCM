<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class ProductCategoryPerformanceReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');
        $completedStatus = OrderStatus::COMPLETED->name;

        return <<<SQL
        SELECT
            COALESCE(pc.title, 'Uncategorized') AS category_name,
            COUNT(DISTINCT p.id) AS product_count,
            COALESCE(SUM(oi.quantity), 0) AS total_sold,
            COALESCE(SUM(oi.total_gross), 0) AS gross_revenue,
            COALESCE(SUM(oi.total_tax), 0) AS total_tax,
            COALESCE(SUM(oi.total_service_fee), 0) AS total_fees,
            CASE
                WHEN SUM(oi.quantity) > 0 THEN ROUND(SUM(oi.total_gross) / SUM(oi.quantity), 2)
                ELSE 0
            END AS avg_price,
            o.currency
        FROM products p
        LEFT JOIN product_categories pc ON p.product_category_id = pc.id
        LEFT JOIN order_items oi ON oi.product_id = p.id
        LEFT JOIN orders o ON oi.order_id = o.id
            AND o.status = '$completedStatus'
            AND o.deleted_at IS NULL
            AND o.created_at BETWEEN '$startDateString' AND '$endDateString'
        WHERE p.event_id = :event_id AND p.deleted_at IS NULL
        GROUP BY COALESCE(pc.title, 'Uncategorized'), o.currency
        ORDER BY gross_revenue DESC NULLS LAST
SQL;
    }
}
