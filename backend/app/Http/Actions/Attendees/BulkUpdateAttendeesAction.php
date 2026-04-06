<?php

namespace HiEvents\Http\Actions\Attendees;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\Status\AttendeeStatus;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BulkUpdateAttendeesAction extends BaseAction
{
    public function __construct(
        private readonly AttendeeRepositoryInterface $attendeeRepository,
    ) {
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $validated = $request->validate([
            'attendee_ids' => 'required|array|min:1|max:500',
            'attendee_ids.*' => 'required|integer',
            'action' => 'required|string|in:check_in,cancel',
        ]);

        $attendeeIds = $validated['attendee_ids'];
        $action = $validated['action'];
        $updated = 0;

        foreach ($attendeeIds as $attendeeId) {
            $attendee = $this->attendeeRepository->findFirstWhere([
                'id' => $attendeeId,
                'event_id' => $eventId,
            ]);

            if (!$attendee) {
                continue;
            }

            if ($action === 'cancel') {
                $this->attendeeRepository->updateWhere(
                    ['id' => $attendeeId],
                    ['status' => AttendeeStatus::CANCELLED->name]
                );
                $updated++;
            } elseif ($action === 'check_in') {
                $this->attendeeRepository->updateWhere(
                    ['id' => $attendeeId],
                    ['checked_in_at' => now()]
                );
                $updated++;
            }
        }

        return $this->jsonResponse([
            'updated' => $updated,
            'total' => count($attendeeIds),
        ]);
    }
}
