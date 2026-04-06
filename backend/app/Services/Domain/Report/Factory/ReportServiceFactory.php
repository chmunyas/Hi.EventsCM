<?php

namespace HiEvents\Services\Domain\Report\Factory;

use HiEvents\DomainObjects\Enums\ReportTypes;
use HiEvents\Services\Domain\Report\AbstractReportService;
use HiEvents\Services\Domain\Report\Reports\AttendeesByProductReport;
use HiEvents\Services\Domain\Report\Reports\AttendeeGeographicReport;
use HiEvents\Services\Domain\Report\Reports\CapacityUtilizationReport;
use HiEvents\Services\Domain\Report\Reports\CheckInByProductReport;
use HiEvents\Services\Domain\Report\Reports\DailySalesReport;
use HiEvents\Services\Domain\Report\Reports\PaymentMethodRevenueReport;
use HiEvents\Services\Domain\Report\Reports\ProductCategoryPerformanceReport;
use HiEvents\Services\Domain\Report\Reports\ProductSalesReport;
use HiEvents\Services\Domain\Report\Reports\PromoCodesReport;
use HiEvents\Services\Domain\Report\Reports\QuestionResponseAnalyticsReport;
use HiEvents\Services\Domain\Report\Reports\RefundAnalyticsReport;
use HiEvents\Services\Domain\Report\Reports\RevenueByDiscountReport;
use Illuminate\Support\Facades\App;

class ReportServiceFactory
{
    public function create(ReportTypes $reportType): AbstractReportService
    {
        return match ($reportType) {
            ReportTypes::PRODUCT_SALES => App::make(ProductSalesReport::class),
            ReportTypes::DAILY_SALES_REPORT => App::make(DailySalesReport::class),
            ReportTypes::PROMO_CODES_REPORT => App::make(PromoCodesReport::class),
            ReportTypes::ATTENDEES_BY_PRODUCT => App::make(AttendeesByProductReport::class),
            ReportTypes::CAPACITY_UTILIZATION => App::make(CapacityUtilizationReport::class),
            ReportTypes::REFUND_ANALYTICS => App::make(RefundAnalyticsReport::class),
            ReportTypes::CHECK_IN_BY_PRODUCT => App::make(CheckInByProductReport::class),
            ReportTypes::QUESTION_RESPONSE_ANALYTICS => App::make(QuestionResponseAnalyticsReport::class),
            ReportTypes::REVENUE_BY_DISCOUNT => App::make(RevenueByDiscountReport::class),
            ReportTypes::PAYMENT_METHOD_REVENUE => App::make(PaymentMethodRevenueReport::class),
            ReportTypes::PRODUCT_CATEGORY_PERFORMANCE => App::make(ProductCategoryPerformanceReport::class),
            ReportTypes::ATTENDEE_GEOGRAPHIC => App::make(AttendeeGeographicReport::class),
        };
    }
}
