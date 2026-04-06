<?php

namespace HiEvents\Http\Actions\Reports;

use HiEvents\DomainObjects\Enums\ReportTypes;
use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Http\Actions\BaseAction;
use HiEvents\Http\Request\Report\GetReportRequest;
use HiEvents\Services\Application\Handlers\Reports\DTO\GetReportDTO;
use HiEvents\Services\Application\Handlers\Reports\GetReportHandler;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ExportEventReportAction extends BaseAction
{
    private const MAX_EXPORT_ROWS = 15000;

    public function __construct(private readonly GetReportHandler $reportHandler)
    {
    }

    /**
     * @throws ValidationException
     */
    public function __invoke(GetReportRequest $request, int $eventId, string $reportType): StreamedResponse
    {
        $this->isActionAuthorized($eventId, EventDomainObject::class);

        if (!in_array($reportType, ReportTypes::valuesArray(), true)) {
            throw new BadRequestHttpException(__('Invalid report type.'));
        }

        $reportData = $this->reportHandler->handle(
            reportData: new GetReportDTO(
                eventId: $eventId,
                reportType: ReportTypes::from($reportType),
                startDate: $request->validated('start_date'),
                endDate: $request->validated('end_date'),
            ),
        );

        $filename = $reportType . '_' . date('Y-m-d_H-i-s') . '.csv';

        return new StreamedResponse(function () use ($reportData, $reportType) {
            $handle = fopen('php://output', 'w');

            $headers = $this->getHeadersForReportType($reportType);
            fputcsv($handle, $headers);

            foreach ($reportData as $row) {
                fputcsv($handle, array_values((array)$row));
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    private function getHeadersForReportType(string $reportType): array
    {
        return match ($reportType) {
            ReportTypes::DAILY_SALES_REPORT->value => [
                'Date', 'Gross Sales', 'Total Tax', 'Sales Before Additions',
                'Products Sold', 'Orders Created', 'Total Fee', 'Total Refunded', 'Total Views',
            ],
            ReportTypes::PRODUCT_SALES->value => [
                'Product ID', 'Product Title', 'Quantity Sold', 'Gross Revenue',
                'Net Revenue', 'Total Tax', 'Total Fee', 'Avg Price',
            ],
            ReportTypes::PROMO_CODES_REPORT->value => [
                'Promo Code', 'Times Used', 'Unique Customers', 'Total Gross Sales',
                'Total Before Discounts', 'Total Discount Amount', 'Status',
            ],
            ReportTypes::ATTENDEES_BY_PRODUCT->value => [
                'Product', 'Attendee', 'Email', 'Price Label',
                'Ticket Price', 'Order Ref', 'Checked In', 'Order Date',
            ],
            ReportTypes::CAPACITY_UTILIZATION->value => [
                'Product', 'Type', 'Total Capacity', 'Quantity Sold',
                'Remaining', 'Utilization %',
            ],
            ReportTypes::REFUND_ANALYTICS->value => [
                'Date', 'Refund Count', 'Total Refunded', 'Full Refunds',
                'Partial Refunds', 'Avg Refund Amount', 'Currency',
            ],
            ReportTypes::CHECK_IN_BY_PRODUCT->value => [
                'Product', 'Total Attendees', 'Checked In', 'Not Checked In',
                'Check-in Rate %', 'First Check-in', 'Last Check-in',
            ],
            ReportTypes::QUESTION_RESPONSE_ANALYTICS->value => [
                'Question', 'Type', 'Required', 'Answered',
                'Unanswered', 'Total', 'Response Rate %',
            ],
            ReportTypes::REVENUE_BY_DISCOUNT->value => [
                'Discount Type', 'Order Count', 'Gross Revenue', 'Net Before Additions',
                'Total Refunded', 'Total Tax', 'Total Fee', 'Currency',
            ],
            ReportTypes::PAYMENT_METHOD_REVENUE->value => [
                'Payment Method', 'Order Count', 'Gross Revenue', 'Net Revenue',
                'Total Tax', 'Total Fee', 'Total Refunded', 'Currency',
            ],
            ReportTypes::PRODUCT_CATEGORY_PERFORMANCE->value => [
                'Category', 'Product Count', 'Total Sold', 'Gross Revenue',
                'Total Tax', 'Total Fees', 'Avg Price', 'Currency',
            ],
            ReportTypes::ATTENDEE_GEOGRAPHIC->value => [
                'Country', 'City', 'State/Region', 'Order Count',
                'Attendee Count', 'Total Revenue', 'Currency',
            ],
            default => ['Data'],
        };
    }
}
