<?php

namespace HiEvents\Http\Actions\Attendees;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\AttendeeRepositoryInterface;
use HiEvents\Repository\Interfaces\DocumentTemplateRepositoryInterface;
use HiEvents\Services\Domain\Badge\BadgeRenderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GenerateBadgesAction extends BaseAction
{
    public function __construct(
        private readonly AttendeeRepositoryInterface $attendeeRepository,
        private readonly DocumentTemplateRepositoryInterface $templateRepository,
        private readonly BadgeRenderService $badgeRenderService,
    ) {
    }

    public function __invoke(Request $request, int $eventId): Response|JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $validated = $request->validate([
            'attendee_ids' => 'required|array|min:1|max:200',
            'attendee_ids.*' => 'required|integer',
            'template_id' => 'required|integer',
        ]);

        $template = $this->templateRepository->findFirstWhere([
            'id' => $validated['template_id'],
            'account_id' => $this->getAuthenticatedAccountId(),
        ]);

        if (!$template) {
            return $this->errorResponse('Template not found', 404);
        }

        $attendees = [];
        foreach ($validated['attendee_ids'] as $attendeeId) {
            $attendee = $this->attendeeRepository->findFirstWhere([
                'id' => $attendeeId,
                'event_id' => $eventId,
            ]);
            if ($attendee) {
                $attendees[] = $attendee;
            }
        }

        if (empty($attendees)) {
            return $this->errorResponse('No matching attendees found', 404);
        }

        $html = $this->badgeRenderService->renderBadges(
            $template->getContent(),
            $attendees,
        );

        return new Response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }
}
