<?php

namespace HiEvents\Http\Actions\SelfService;

use HiEvents\Exceptions\RefundNotPossibleException;
use HiEvents\Exceptions\ResourceConflictException;
use HiEvents\Exceptions\SelfServiceDisabledException;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Services\Application\Handlers\SelfService\CancelOrderPublicHandler;
use HiEvents\Services\Application\Handlers\SelfService\DTO\CancelOrderPublicDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class CancelOrderPublicAction extends BaseAction
{
    public function __construct(
        private readonly CancelOrderPublicHandler $handler
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(Request $request, int $eventId, string $orderShortId): JsonResponse
    {
        try {
            $result = $this->handler->handle(CancelOrderPublicDTO::from([
                'eventId' => $eventId,
                'orderShortId' => $orderShortId,
                'ipAddress' => $this->getClientIp($request),
                'userAgent' => $request->userAgent(),
            ]));

            return $this->jsonResponse([
                'message' => $result->refunded
                    ? __('Order cancelled and refunded successfully')
                    : __('Order cancelled successfully'),
                'refunded' => $result->refunded,
            ]);
        } catch (SelfServiceDisabledException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        } catch (ResourceNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), 404);
        } catch (ResourceConflictException $e) {
            return $this->errorResponse($e->getMessage(), HttpResponse::HTTP_CONFLICT);
        } catch (ApiErrorException|RefundNotPossibleException $exception) {
            throw ValidationException::withMessages([
                'order' => $exception instanceof ApiErrorException
                    ? 'Stripe error: ' . $exception->getMessage()
                    : $exception->getMessage(),
            ]);
        }
    }
}
