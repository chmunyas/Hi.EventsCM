<?php

namespace HiEvents\Http\Actions\Events\Stats;

use Carbon\Carbon;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\Status\AttendeeStatus;
use HiEvents\DomainObjects\Status\OrderStatus;
use HiEvents\Http\Actions\BaseAction;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;

class GetAttendeeEngagementAction extends BaseAction
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function __invoke(int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $activeStatus = AttendeeStatus::ACTIVE->name;
        $completedStatus = OrderStatus::COMPLETED->name;

        $results = $this->db->select(<<<SQL
            SELECT
                COUNT(a.id) AS total_attendees,
                COUNT(DISTINCT aci.attendee_id) AS checked_in_count,
                CASE
                    WHEN COUNT(a.id) = 0 THEN 0
                    ELSE ROUND((COUNT(DISTINCT aci.attendee_id)::numeric / COUNT(a.id)) * 100, 1)
                END AS check_in_rate,
                COUNT(DISTINCT CASE WHEN qa.id IS NOT NULL THEN a.id END) AS responded_to_questions,
                CASE
                    WHEN COUNT(a.id) = 0 THEN 0
                    ELSE ROUND((COUNT(DISTINCT CASE WHEN qa.id IS NOT NULL THEN a.id END)::numeric / COUNT(a.id)) * 100, 1)
                END AS question_response_rate,
                COUNT(DISTINCT CASE WHEN a.locale IS NOT NULL THEN a.locale END) AS unique_locales,
                COUNT(DISTINCT CASE WHEN o.promo_code IS NOT NULL THEN a.id END) AS used_promo_code
            FROM attendees a
            JOIN orders o ON a.order_id = o.id
            LEFT JOIN attendee_check_ins aci ON aci.attendee_id = a.id AND aci.deleted_at IS NULL
            LEFT JOIN question_answers qa ON qa.attendee_id = a.id AND qa.deleted_at IS NULL
                AND qa.answer IS NOT NULL AND qa.answer != '' AND qa.answer != '[]'
            WHERE a.event_id = :event_id
                AND a.status = '$activeStatus'
                AND a.deleted_at IS NULL
                AND o.status = '$completedStatus'
                AND o.deleted_at IS NULL
SQL
        , ['event_id' => $eventId]);

        return $this->jsonResponse($results[0] ?? (object)[
            'total_attendees' => 0,
            'checked_in_count' => 0,
            'check_in_rate' => 0,
            'responded_to_questions' => 0,
            'question_response_rate' => 0,
            'unique_locales' => 0,
            'used_promo_code' => 0,
        ]);
    }
}
