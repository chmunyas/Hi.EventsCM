<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\DomainObjects\Status\AttendeeStatus;
use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class CheckInByProductReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $activeStatus = AttendeeStatus::ACTIVE->name;

        return <<<SQL
        SELECT
            p.id AS product_id,
            p.title AS product_title,
            COUNT(a.id) AS total_attendees,
            COUNT(DISTINCT aci.attendee_id) AS checked_in_count,
            COUNT(a.id) - COUNT(DISTINCT aci.attendee_id) AS not_checked_in_count,
            CASE
                WHEN COUNT(a.id) = 0 THEN 0
                ELSE ROUND((COUNT(DISTINCT aci.attendee_id)::numeric / COUNT(a.id)) * 100, 1)
            END AS check_in_rate,
            MIN(aci.created_at) AS first_check_in,
            MAX(aci.created_at) AS last_check_in
        FROM products p
        LEFT JOIN attendees a ON a.product_id = p.id
            AND a.event_id = :event_id
            AND a.status = '$activeStatus'
            AND a.deleted_at IS NULL
        LEFT JOIN attendee_check_ins aci ON aci.attendee_id = a.id
            AND aci.deleted_at IS NULL
        WHERE p.event_id = :event_id AND p.deleted_at IS NULL
        GROUP BY p.id, p.title
        ORDER BY check_in_rate DESC NULLS LAST
SQL;
    }
}
