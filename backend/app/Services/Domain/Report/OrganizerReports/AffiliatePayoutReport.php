<?php

namespace HiEvents\Services\Domain\Report\OrganizerReports;

use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Services\Domain\Report\AbstractOrganizerReportService;
use Illuminate\Support\Carbon;

class AffiliatePayoutReport extends AbstractOrganizerReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate, ?string $currency = null): string
    {
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();
        $completedStatus = OrderStatus::COMPLETED->name;
        $currencyFilter = $this->buildCurrencyFilter('o.currency', $currency);

        return <<<SQL
        SELECT
            aff.id AS affiliate_id,
            aff.name AS affiliate_name,
            aff.code AS affiliate_code,
            aff.commission_rate,
            e.title AS event_title,
            e.id AS event_id,
            COUNT(DISTINCT o.id) AS order_count,
            SUM(o.total_gross) AS total_sales,
            SUM(o.total_gross * aff.commission_rate / 100) AS commission_earned,
            o.currency
        FROM affiliates aff
        JOIN events e ON aff.event_id = e.id
        LEFT JOIN orders o ON o.affiliate_id = aff.id
            AND o.status = '$completedStatus'
            AND o.deleted_at IS NULL
            AND o.created_at::date BETWEEN '$startDateStr' AND '$endDateStr'
        WHERE e.organizer_id = :organizer_id
            AND e.deleted_at IS NULL
            AND aff.deleted_at IS NULL
            $currencyFilter
        GROUP BY aff.id, aff.name, aff.code, aff.commission_rate, e.title, e.id, o.currency
        ORDER BY commission_earned DESC NULLS LAST
SQL;
    }
}
