<?php

namespace HiEvents\Http\Actions\Organizers\Stats;

use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Http\Actions\BaseAction;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;

class GetOrganizerBenchmarkAction extends BaseAction
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function __invoke(int $organizerId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $completedStatus = OrderStatus::COMPLETED->name;

        $results = $this->db->select(<<<SQL
            WITH organizer_metrics AS (
                SELECT
                    COUNT(DISTINCT e.id) AS event_count,
                    COALESCE(AVG(event_stats.tickets_sold), 0) AS avg_tickets_per_event,
                    COALESCE(AVG(event_stats.gross_revenue), 0) AS avg_revenue_per_event,
                    COALESCE(AVG(event_stats.refund_rate), 0) AS avg_refund_rate,
                    COALESCE(AVG(event_stats.check_in_rate), 0) AS avg_check_in_rate
                FROM events e
                LEFT JOIN (
                    SELECT
                        o.event_id,
                        COUNT(DISTINCT oi.id) AS tickets_sold,
                        SUM(o.total_gross) AS gross_revenue,
                        CASE
                            WHEN SUM(o.total_gross) = 0 THEN 0
                            ELSE (SUM(o.total_refunded) / SUM(o.total_gross)) * 100
                        END AS refund_rate,
                        0 AS check_in_rate
                    FROM orders o
                    JOIN order_items oi ON oi.order_id = o.id
                    WHERE o.status = '$completedStatus' AND o.deleted_at IS NULL
                    GROUP BY o.event_id
                ) event_stats ON e.id = event_stats.event_id
                WHERE e.organizer_id = :organizer_id AND e.deleted_at IS NULL
            ),
            platform_metrics AS (
                SELECT
                    COALESCE(AVG(event_stats.tickets_sold), 0) AS platform_avg_tickets,
                    COALESCE(AVG(event_stats.gross_revenue), 0) AS platform_avg_revenue,
                    COALESCE(AVG(event_stats.refund_rate), 0) AS platform_avg_refund_rate
                FROM events e
                LEFT JOIN (
                    SELECT
                        o.event_id,
                        COUNT(DISTINCT oi.id) AS tickets_sold,
                        SUM(o.total_gross) AS gross_revenue,
                        CASE
                            WHEN SUM(o.total_gross) = 0 THEN 0
                            ELSE (SUM(o.total_refunded) / SUM(o.total_gross)) * 100
                        END AS refund_rate
                    FROM orders o
                    JOIN order_items oi ON oi.order_id = o.id
                    WHERE o.status = '$completedStatus' AND o.deleted_at IS NULL
                    GROUP BY o.event_id
                ) event_stats ON e.id = event_stats.event_id
                WHERE e.deleted_at IS NULL
            )
            SELECT
                om.event_count,
                ROUND(om.avg_tickets_per_event::numeric, 1) AS avg_tickets_per_event,
                ROUND(om.avg_revenue_per_event::numeric, 2) AS avg_revenue_per_event,
                ROUND(om.avg_refund_rate::numeric, 1) AS avg_refund_rate,
                ROUND(pm.platform_avg_tickets::numeric, 1) AS platform_avg_tickets,
                ROUND(pm.platform_avg_revenue::numeric, 2) AS platform_avg_revenue,
                ROUND(pm.platform_avg_refund_rate::numeric, 1) AS platform_avg_refund_rate
            FROM organizer_metrics om, platform_metrics pm
SQL
        , ['organizer_id' => $organizerId]);

        return $this->jsonResponse($results[0] ?? (object)[]);
    }
}
