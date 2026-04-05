<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class RefundAnalyticsReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');

        return <<<SQL
        WITH refund_data AS (
            SELECT
                orf.id,
                orf.order_id,
                orf.amount,
                orf.created_at,
                o.total_gross AS order_total,
                o.currency
            FROM order_refunds orf
            JOIN orders o ON orf.order_id = o.id
            WHERE o.event_id = :event_id
                AND o.deleted_at IS NULL
                AND orf.created_at BETWEEN '$startDateString' AND '$endDateString'
        )
        SELECT
            rd.created_at::date AS refund_date,
            COUNT(*) AS refund_count,
            SUM(rd.amount) AS total_refunded,
            SUM(CASE WHEN rd.amount >= rd.order_total THEN 1 ELSE 0 END) AS full_refund_count,
            SUM(CASE WHEN rd.amount < rd.order_total THEN 1 ELSE 0 END) AS partial_refund_count,
            AVG(rd.amount) AS avg_refund_amount,
            rd.currency
        FROM refund_data rd
        GROUP BY rd.created_at::date, rd.currency
        ORDER BY refund_date DESC
SQL;
    }
}
