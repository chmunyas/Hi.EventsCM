<?php

namespace HiEvents\Http\Actions\Attendees;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Http\Actions\BaseAction;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;

class GetAttendeeJourneyAction extends BaseAction
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function __invoke(int $eventId, int $attendeeId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $timeline = $this->db->select(<<<SQL
            SELECT * FROM (
                -- Order placed
                SELECT
                    'order_placed' AS event_type,
                    o.created_at AS occurred_at,
                    CONCAT('Order #', o.short_id, ' placed - ', o.currency, ' ', o.total_gross) AS description
                FROM attendees a
                JOIN orders o ON a.order_id = o.id
                WHERE a.id = :attendee_id AND a.event_id = :event_id AND a.deleted_at IS NULL

                UNION ALL

                -- Check-ins
                SELECT
                    'check_in' AS event_type,
                    aci.created_at AS occurred_at,
                    CONCAT('Checked in via list: ', COALESCE(cil.name, 'Unknown')) AS description
                FROM attendee_check_ins aci
                JOIN attendees a ON aci.attendee_id = a.id
                LEFT JOIN check_in_lists cil ON aci.check_in_list_id = cil.id
                WHERE a.id = :attendee_id AND a.event_id = :event_id AND aci.deleted_at IS NULL

                UNION ALL

                -- Question answers
                SELECT
                    'question_answered' AS event_type,
                    qa.created_at AS occurred_at,
                    CONCAT('Answered: ', q.title) AS description
                FROM question_answers qa
                JOIN questions q ON qa.question_id = q.id
                JOIN attendees a ON qa.attendee_id = a.id
                WHERE a.id = :attendee_id AND a.event_id = :event_id
                    AND qa.deleted_at IS NULL
                    AND qa.answer IS NOT NULL AND qa.answer != '' AND qa.answer != '[]'

                UNION ALL

                -- Refunds on the order
                SELECT
                    'refund' AS event_type,
                    orf.created_at AS occurred_at,
                    CONCAT('Refund of ', orf.amount, ' processed') AS description
                FROM order_refunds orf
                JOIN orders o ON orf.order_id = o.id
                JOIN attendees a ON a.order_id = o.id
                WHERE a.id = :attendee_id AND a.event_id = :event_id
            ) timeline
            ORDER BY occurred_at ASC
SQL
        , [
            'attendee_id' => $attendeeId,
            'event_id' => $eventId,
        ]);

        return $this->jsonResponse($timeline);
    }
}
