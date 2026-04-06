<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class AttendeeGeographicReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');
        $completedStatus = OrderStatus::COMPLETED->name;

        return <<<SQL
        SELECT
            COALESCE(NULLIF(TRIM(oba.country), ''), 'Unknown') AS country,
            COALESCE(NULLIF(TRIM(oba.city), ''), 'Unknown') AS city,
            COALESCE(NULLIF(TRIM(oba.state_or_region), ''), 'Unknown') AS state_region,
            COUNT(DISTINCT o.id) AS order_count,
            COUNT(DISTINCT a.id) AS attendee_count,
            SUM(o.total_gross) AS total_revenue,
            o.currency
        FROM orders o
        JOIN order_billing_addresses oba ON oba.order_id = o.id
        LEFT JOIN attendees a ON a.order_id = o.id AND a.deleted_at IS NULL
        WHERE o.event_id = :event_id
            AND o.status = '$completedStatus'
            AND o.deleted_at IS NULL
            AND o.created_at BETWEEN '$startDateString' AND '$endDateString'
        GROUP BY country, city, state_region, o.currency
        ORDER BY attendee_count DESC NULLS LAST
SQL;
    }
}
