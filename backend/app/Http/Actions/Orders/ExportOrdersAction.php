<?php

namespace HiEvents\Http\Actions\Orders;

use HiEvents\DomainObjects\Enums\QuestionBelongsTo;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\DomainObjects\QuestionAndAnswerViewDomainObject;
use HiEvents\Exports\OrdersExport;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\DTO\QueryParamsDTO;
use HiEvents\Http\DTO\FilterFieldDTO;
use HiEvents\Repository\Interfaces\OrderRepositoryInterface;
use HiEvents\Repository\Interfaces\QuestionRepositoryInterface;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportOrdersAction extends BaseAction
{
    public function __construct(
        private readonly OrderRepositoryInterface    $orderRepository,
        private readonly QuestionRepositoryInterface $questionRepository,
        private readonly OrdersExport                $export
    )
    {
    }

    public function __invoke(Request $request, int $eventId): BinaryFileResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        $filterFields = collect();

        if ($request->filled('status')) {
            $filterFields->push(new FilterFieldDTO(
                field: 'status',
                operator: 'eq',
                value: $request->input('status'),
            ));
        }

        if ($request->filled('payment_status')) {
            $filterFields->push(new FilterFieldDTO(
                field: 'payment_status',
                operator: 'eq',
                value: $request->input('payment_status'),
            ));
        }

        if ($request->filled('date_from')) {
            $filterFields->push(new FilterFieldDTO(
                field: 'created_at',
                operator: 'gte',
                value: $request->input('date_from'),
            ));
        }

        if ($request->filled('date_to')) {
            $filterFields->push(new FilterFieldDTO(
                field: 'created_at',
                operator: 'lte',
                value: $request->input('date_to'),
            ));
        }

        if ($request->filled('promo_code')) {
            $filterFields->push(new FilterFieldDTO(
                field: 'promo_code',
                operator: 'eq',
                value: $request->input('promo_code'),
            ));
        }

        $orders = $this->orderRepository
            ->setMaxPerPage(10000)
            ->loadRelation(QuestionAndAnswerViewDomainObject::class)
            ->findByEventId($eventId, new QueryParamsDTO(
                page: 1,
                per_page: 10000,
                filter_fields: $filterFields->isNotEmpty() ? $filterFields : null,
            ));

        $questions = $this->questionRepository->findWhere([
            'event_id' => $eventId,
            'belongs_to' => QuestionBelongsTo::ORDER->name,
        ]);

        $filename = 'orders';
        if ($request->filled('status')) {
            $filename .= '_' . $request->input('status');
        }
        if ($request->filled('date_from')) {
            $filename .= '_from_' . $request->input('date_from');
        }
        if ($request->filled('date_to')) {
            $filename .= '_to_' . $request->input('date_to');
        }
        $filename .= '.xlsx';

        return Excel::download(
            $this->export->withData($orders, $questions),
            $filename
        );
    }
}
