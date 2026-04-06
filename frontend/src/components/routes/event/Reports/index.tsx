import {PageTitle} from "../../../common/PageTitle";
import {t} from "@lingui/macro";
import {PageBody} from "../../../common/PageBody";
import {
    IconChartBar,
    IconChevronRight,
    IconReportMoney,
    IconGauge,
    IconReceiptRefund,
    IconUserCheck,
    IconQuestionMark,
    IconDiscount2,
    IconCreditCard,
    IconCategory,
    IconMapPin
} from "@tabler/icons-react";
import classes from './Reports.module.scss';
import {Card} from "../../../common/Card";
import {Avatar, UnstyledButton} from "@mantine/core";
import {Link, useParams} from "react-router";
import {ReportTypes} from "../../../../types.ts";

const Reports = () => {
    const {eventId} = useParams();

    const reports = [
        {
            id: ReportTypes.ProductSales,
            title: t`Product Sales`,
            description: t`Product sales, revenue, and tax breakdown`,
            icon: <Avatar size={40} color={'#831781'}><IconReportMoney/></Avatar>
        },
        {
            id: ReportTypes.DailySales,
            title: t`Daily Sales Report`,
            description: t`Daily sales, tax, and fee breakdown`,
            icon: <Avatar size={40} color={'#00a3e0'}><IconChartBar/></Avatar>
        },
        {
            id: ReportTypes.PromoCodes,
            title: t`Promo Codes Report`,
            description: t`Promo code usage and discount breakdown`,
            icon: <Avatar size={40} color={'#634fc0'}><IconReportMoney/></Avatar>
        },
        {
            id: ReportTypes.CapacityUtilization,
            title: t`Capacity Utilization`,
            description: t`Product capacity usage and remaining availability`,
            icon: <Avatar size={40} color={'#e67c63'}><IconGauge/></Avatar>
        },
        {
            id: ReportTypes.RefundAnalytics,
            title: t`Refund Analytics`,
            description: t`Refund trends, counts, and amounts over time`,
            icon: <Avatar size={40} color={'#d94f4f'}><IconReceiptRefund/></Avatar>
        },
        {
            id: ReportTypes.CheckInByProduct,
            title: t`Check-in by Product`,
            description: t`Check-in rates and timing per product`,
            icon: <Avatar size={40} color={'#5fb98b'}><IconUserCheck/></Avatar>
        },
        {
            id: ReportTypes.QuestionResponseAnalytics,
            title: t`Question Response Analytics`,
            description: t`Response rates and completion for event questions`,
            icon: <Avatar size={40} color={'#49a6b7'}><IconQuestionMark/></Avatar>
        },
        {
            id: ReportTypes.RevenueByDiscount,
            title: t`Revenue by Discount`,
            description: t`Revenue breakdown by discount type and source`,
            icon: <Avatar size={40} color={'#b5a642'}><IconDiscount2/></Avatar>
        },
        {
            id: ReportTypes.PaymentMethodRevenue,
            title: t`Payment Method Revenue`,
            description: t`Revenue breakdown by payment method`,
            icon: <Avatar size={40} color={'#2196f3'}><IconCreditCard/></Avatar>
        },
        {
            id: ReportTypes.ProductCategoryPerformance,
            title: t`Product Category Performance`,
            description: t`Sales performance by product category`,
            icon: <Avatar size={40} color={'#ff9800'}><IconCategory/></Avatar>
        },
        {
            id: ReportTypes.AttendeeGeographic,
            title: t`Attendee Geographic Distribution`,
            description: t`Attendee distribution by country and city`,
            icon: <Avatar size={40} color={'#4caf50'}><IconMapPin/></Avatar>
        }
    ];

    return (
        <PageBody>
            <PageTitle
                subheading={t`Download sales, attendee, and financial reports for all completed orders.`}>
                {t`Reports`}
            </PageTitle>

            {reports.map((report) => (
                <UnstyledButton component={Link} key={report.id} to={`/manage/event/${eventId}/report/${report.id}`}>
                    <Card className={classes.reportType}>
                        <div className={classes.icon}>
                            {report.icon}
                        </div>
                        <div className={classes.content}>
                            <h3>{report.title}</h3>
                            <p>{report.description}</p>
                        </div>
                        <div className={classes.rightCaret}>
                            <IconChevronRight/>
                        </div>
                    </Card>
                </UnstyledButton>
            ))}
        </PageBody>
    )
}

export default Reports;
