<?php

declare(strict_types=1);

namespace HiEvents\Http\Actions\Attendees;

use HiEvents\DomainObjects\AttendeeDomainObject;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\EventSettingDomainObject;
use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Eloquent\Value\Relationship;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use HiEvents\Services\Domain\Wallet\AppleWalletPassService;
use HiEvents\Services\Domain\Wallet\GoogleWalletPassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetAttendeeWalletPassPublicAction extends BaseAction
{
    public function __construct(
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly EventRepositoryInterface    $eventRepository,
        private readonly AppleWalletPassService      $appleWalletService,
        private readonly GoogleWalletPassService     $googleWalletService,
    ) {
    }

    public function __invoke(Request $request, int $eventId, string $attendeeShortId): Response|JsonResponse
    {
        $attendee = $this->attendeeRepository->findFirstWhere([
            AttendeeDomainObject::SHORT_ID => $attendeeShortId,
            AttendeeDomainObject::EVENT_ID => $eventId,
        ]);

        if (!$attendee) {
            return $this->notFoundResponse();
        }

        // Don't generate passes for cancelled attendees
        if ($attendee->getStatus() === 'CANCELLED') {
            return $this->jsonResponse(['error' => 'This ticket has been cancelled.'], 410);
        }

        $event = $this->eventRepository
            ->loadRelation(new Relationship(EventSettingDomainObject::class))
            ->loadRelation(new Relationship(OrganizerDomainObject::class))
            ->findById($eventId);

        /** @var EventSettingDomainObject $eventSettings */
        $eventSettings = $event->getEventSettings();
        /** @var OrganizerDomainObject $organizer */
        $organizer = $event->getOrganizer();

        $platform = $request->query('platform', 'apple');

        if ($platform === 'google') {
            $pass = $this->googleWalletService->generatePass(
                $attendee, $event, $eventSettings, $organizer
            );
            return $this->jsonResponse($pass);
        }

        // Default: Apple Wallet
        $pass = $this->appleWalletService->generatePass(
            $attendee, $event, $eventSettings, $organizer
        );

        return response($pass['content'], 200, [
            'Content-Type' => $pass['mime'],
            'Content-Disposition' => sprintf('attachment; filename="%s"', $pass['filename']),
        ]);
    }
}
