<?php

namespace HiEvents\Http\Actions\Events;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\Status\EventStatus;
use HiEvents\Exceptions\AccountNotVerifiedException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\ResponseCodes;
use HiEvents\Services\Application\Handlers\Event\DTO\UpdateEventStatusDTO;
use HiEvents\Services\Application\Handlers\Event\UpdateEventStatusHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BulkUpdateEventStatusAction extends BaseAction
{
    public function __construct(
        private readonly UpdateEventStatusHandler $updateEventStatusHandler,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_ids' => 'required|array|min:1|max:50',
            'event_ids.*' => 'required|integer',
            'status' => 'required|string|in:' . implode(',', EventStatus::valuesArray()),
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), ResponseCodes::HTTP_UNPROCESSABLE_ENTITY);
        }

        $eventIds = $request->input('event_ids');
        $status = $request->input('status');
        $accountId = $this->getAuthenticatedAccountId();

        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($eventIds as $eventId) {
            try {
                $this->isActionAuthorized($eventId, EventDomainObject::class);

                $this->updateEventStatusHandler->handle(UpdateEventStatusDTO::fromArray([
                    'status' => $status,
                    'eventId' => $eventId,
                    'accountId' => $accountId,
                ]));

                $results['success'][] = $eventId;
            } catch (AccountNotVerifiedException $e) {
                $results['failed'][] = ['event_id' => $eventId, 'reason' => $e->getMessage()];
            } catch (\Throwable $e) {
                $results['failed'][] = ['event_id' => $eventId, 'reason' => 'Failed to update status'];
            }
        }

        return $this->jsonResponse([
            'status' => $status,
            'total_requested' => count($eventIds),
            'total_success' => count($results['success']),
            'total_failed' => count($results['failed']),
            'success' => $results['success'],
            'failed' => $results['failed'],
        ]);
    }
}
