<?php

namespace HiEvents\Http\Actions\Events;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Repository\Interfaces\EventRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UpdateEventSlugAction extends BaseAction
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
    ) {
    }

    public function __invoke(Request $request, int $eventId): JsonResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $validated = $request->validate([
            'slug' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
        ]);

        $slug = $validated['slug'];

        $existing = $this->eventRepository->findFirstWhere([
            'slug' => $slug,
        ]);

        if ($existing && $existing->getId() !== $eventId) {
            throw ValidationException::withMessages([
                'slug' => __('This slug is already taken by another event.'),
            ]);
        }

        $this->eventRepository->updateWhere(
            ['id' => $eventId],
            ['slug' => $slug]
        );

        return $this->jsonResponse(['slug' => $slug]);
    }
}
