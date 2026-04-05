<?php

namespace HiEvents\Jobs\Event;

use HiEvents\DomainObjects\Status\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckLowCapacityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const THRESHOLDS = [10, 5, 0];

    public function handle(DatabaseManager $db): void
    {
        $completedStatus = OrderStatus::COMPLETED->name;

        $results = $db->select(<<<SQL
            SELECT
                e.id AS event_id,
                e.title AS event_title,
                p.id AS product_id,
                p.title AS product_title,
                ca.capacity AS total_capacity,
                COALESCE(sold.quantity_sold, 0) AS quantity_sold,
                ca.capacity - COALESCE(sold.quantity_sold, 0) AS remaining,
                CASE
                    WHEN ca.capacity = 0 THEN 0
                    ELSE ROUND(((ca.capacity - COALESCE(sold.quantity_sold, 0))::numeric / ca.capacity) * 100, 1)
                END AS remaining_percent,
                es.support_email,
                es.notify_organizer_of_new_orders,
                es.low_capacity_alert_sent_thresholds
            FROM products p
            JOIN events e ON p.event_id = e.id
            JOIN event_settings es ON es.event_id = e.id
            JOIN capacity_assignment_products capi ON capi.product_id = p.id
            JOIN capacity_assignments ca ON capi.capacity_assignment_id = ca.id AND ca.deleted_at IS NULL
            LEFT JOIN (
                SELECT oi.product_id, SUM(oi.quantity) AS quantity_sold
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                WHERE o.status = '$completedStatus' AND o.deleted_at IS NULL
                GROUP BY oi.product_id
            ) sold ON p.id = sold.product_id
            WHERE e.deleted_at IS NULL
                AND p.deleted_at IS NULL
                AND e.status = 'LIVE'
                AND es.low_capacity_alerts_enabled = true
                AND ca.capacity > 0
                AND (ca.capacity - COALESCE(sold.quantity_sold, 0)) <= (ca.capacity * 0.1)
            ORDER BY remaining_percent ASC
SQL
        );

        foreach ($results as $row) {
            $remainingPercent = (float)$row->remaining_percent;
            $sentThresholds = json_decode($row->low_capacity_alert_sent_thresholds ?? '[]', true) ?: [];

            foreach (self::THRESHOLDS as $threshold) {
                if ($remainingPercent <= $threshold && !in_array("{$row->product_id}_{$threshold}", $sentThresholds)) {
                    Log::info("Low capacity alert: Event {$row->event_title}, Product {$row->product_title} at {$remainingPercent}% ({$row->remaining} remaining)");

                    $sentThresholds[] = "{$row->product_id}_{$threshold}";

                    $db->update(
                        'UPDATE event_settings SET low_capacity_alert_sent_thresholds = ? WHERE event_id = ?',
                        [json_encode($sentThresholds), $row->event_id]
                    );

                    break;
                }
            }
        }
    }
}
