<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class RevenueByDiscountReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');
        $completedStatus = OrderStatus::COMPLETED->name;

        return <<<SQL
        SELECT
            CASE
                WHEN o.promo_code_id IS NOT NULL THEN 'Promo Code'
                WHEN o.affiliate_id IS NOT NULL THEN 'Affiliate'
                WHEN EXISTS (
                    SELECT 1 FROM order_items oi2
                    WHERE oi2.order_id = o.id AND oi2.price_before_discount != oi2.price
                ) THEN 'Tiered/Discounted'
                WHEN o.is_free_order = true THEN 'Free'
                ELSE 'Full Price'
            END AS discount_type,
            COUNT(DISTINCT o.id) AS order_count,
            SUM(o.total_gross) AS gross_revenue,
            SUM(o.total_before_additions) AS net_before_additions,
            SUM(o.total_refunded) AS total_refunded,
            SUM(o.total_tax) AS total_tax,
            SUM(o.total_fee) AS total_fee,
            o.currency
        FROM orders o
        WHERE o.event_id = :event_id
            AND o.status = '$completedStatus'
            AND o.deleted_at IS NULL
            AND o.created_at BETWEEN '$startDateString' AND '$endDateString'
        GROUP BY discount_type, o.currency
        ORDER BY gross_revenue DESC NULLS LAST
SQL;
    }
}
