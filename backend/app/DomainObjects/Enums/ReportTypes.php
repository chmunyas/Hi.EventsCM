<?php

namespace HiEvents\DomainObjects\Enums;

enum ReportTypes: string
{
    use BaseEnum;

    case PRODUCT_SALES = 'product_sales';
    case DAILY_SALES_REPORT = 'daily_sales_report';
    case PROMO_CODES_REPORT = 'promo_codes_report';
    case ATTENDEES_BY_PRODUCT = 'attendees_by_product';
    case CAPACITY_UTILIZATION = 'capacity_utilization';
    case REFUND_ANALYTICS = 'refund_analytics';
    case CHECK_IN_BY_PRODUCT = 'check_in_by_product';
    case QUESTION_RESPONSE_ANALYTICS = 'question_response_analytics';
    case REVENUE_BY_DISCOUNT = 'revenue_by_discount';
    case PAYMENT_METHOD_REVENUE = 'payment_method_revenue';
    case PRODUCT_CATEGORY_PERFORMANCE = 'product_category_performance';
    case ATTENDEE_GEOGRAPHIC = 'attendee_geographic';
}
