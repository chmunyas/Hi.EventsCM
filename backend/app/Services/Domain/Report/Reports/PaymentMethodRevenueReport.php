<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class PaymentMethodRevenueReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');
        $completedStatus = OrderStatus::COMPLETED->name;

        return <<<SQL
        SELECT
            CASE
                WHEN o.is_manually_created = true THEN 'Manual'
                WHEN o.payment_gateway IS NOT NULL THEN INITCAP(o.payment_gateway)
                WHEN o.is_free_order = true THEN 'Free'
                ELSE 'Offline'
            END AS payment_method,
            COUNT(DISTINCT o.id) AS order_count,
            SUM(o.total_gross) AS gross_revenue,
            SUM(o.total_before_additions) AS net_revenue,
            SUM(o.total_tax) AS total_tax,
            SUM(o.total_fee) AS total_fee,
            SUM(o.total_refunded) AS total_refunded,
            o.currency
        FROM orders o
        WHERE o.event_id = :event_id
            AND o.status = '$completedStatus'
            AND o.deleted_at IS NULL
            AND o.created_at BETWEEN '$startDateString' AND '$endDateString'
        GROUP BY payment_method, o.currency
        ORDER BY gross_revenue DESC NULLS LAST
SQL;
    }
}
