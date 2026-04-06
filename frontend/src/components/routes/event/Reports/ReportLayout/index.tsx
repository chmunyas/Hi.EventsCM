import {Link, useParams} from "react-router";
import {PageBody} from "../../../../common/PageBody";
import {Button} from "@mantine/core";
import {IconChevronLeft} from "@tabler/icons-react";
import ProductSalesReport from "../ProductSalesReport";
import {ReportTypes} from "../../../../../types.ts";
import {DailySalesReport} from "../DailySalesReport";
import PromoCodesReport from "../PromoCodesReport";
import CapacityUtilizationReport from "../CapacityUtilizationReport";
import RefundAnalyticsReport from "../RefundAnalyticsReport";
import CheckInByProductReport from "../CheckInByProductReport";
import QuestionResponseAnalyticsReport from "../QuestionResponseAnalyticsReport";
import RevenueByDiscountReport from "../RevenueByDiscountReport";
import PaymentMethodRevenueReport from "../PaymentMethodRevenueReport";
import ProductCategoryPerformanceReport from "../ProductCategoryPerformanceReport";
import AttendeeGeographicReport from "../AttendeeGeographicReport";

const renderReport = (reportType: string) => {
    switch (reportType) {
        case ReportTypes.ProductSales:
            return <ProductSalesReport/>;
        case ReportTypes.DailySales:
            return <DailySalesReport/>;
        case ReportTypes.PromoCodes:
            return <PromoCodesReport/>;
        case ReportTypes.CapacityUtilization:
            return <CapacityUtilizationReport/>;
        case ReportTypes.RefundAnalytics:
            return <RefundAnalyticsReport/>;
        case ReportTypes.CheckInByProduct:
            return <CheckInByProductReport/>;
        case ReportTypes.QuestionResponseAnalytics:
            return <QuestionResponseAnalyticsReport/>;
        case ReportTypes.RevenueByDiscount:
            return <RevenueByDiscountReport/>;
        case ReportTypes.PaymentMethodRevenue:
            return <PaymentMethodRevenueReport/>;
        case ReportTypes.ProductCategoryPerformance:
            return <ProductCategoryPerformanceReport/>;
        case ReportTypes.AttendeeGeographic:
            return <AttendeeGeographicReport/>;
        default:
            return <div>Report not found</div>;
    }
};

const ReportLayout = () => {
    const {eventId, reportType} = useParams();

    return (
        <PageBody>
            <Button mb={20}
                    leftSection={<IconChevronLeft/>}
                    variant={'transparent'}
                    component={Link}
                    to={`/manage/event/${eventId}/reports`}
                    pl={0}
            >
                Back to Reports
            </Button>
            <div>
                {renderReport(reportType as string)}
            </div>
        </PageBody>
    );
}

export default ReportLayout;
