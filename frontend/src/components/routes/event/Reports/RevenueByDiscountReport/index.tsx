import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import {formatCurrency} from "../../../../../utilites/currency.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const RevenueByDiscountReport = () => {
    const {eventId} = useParams();
    const eventQuery = useGetEvent(eventId);
    const event = eventQuery.data;

    if (!event) {
        return null;
    }

    const columns = [
        {
            key: 'discount_type' as const,
            label: t`Discount Type`,
            sortable: true
        },
        {
            key: 'order_count' as const,
            label: t`Order Count`,
            sortable: true
        },
        {
            key: 'gross_revenue' as const,
            label: t`Gross Revenue`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        },
        {
            key: 'net_before_additions' as const,
            label: t`Net Before Additions`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        },
        {
            key: 'total_refunded' as const,
            label: t`Total Refunded`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        },
        {
            key: 'total_tax' as const,
            label: t`Total Tax`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        },
        {
            key: 'total_fee' as const,
            label: t`Total Fee`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        }
    ];

    return (
        <ReportTable
            title={t`Revenue by Discount Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="revenue_by_discount_report.csv"
            showDateFilter={true}
            event={event}
        />
    );
};

export default RevenueByDiscountReport;
