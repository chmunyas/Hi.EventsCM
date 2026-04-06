import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import {formatCurrency} from "../../../../../utilites/currency.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const PaymentMethodRevenueReport = () => {
    const {eventId} = useParams();
    const eventQuery = useGetEvent(eventId);
    const event = eventQuery.data;

    if (!event) {
        return null;
    }

    const columns = [
        {
            key: 'payment_method' as const,
            label: t`Payment Method`,
            sortable: true
        },
        {
            key: 'order_count' as const,
            label: t`Orders`,
            sortable: true
        },
        {
            key: 'gross_revenue' as const,
            label: t`Gross Revenue`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        },
        {
            key: 'net_revenue' as const,
            label: t`Net Revenue`,
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
        },
        {
            key: 'total_refunded' as const,
            label: t`Total Refunded`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        }
    ];

    return (
        <ReportTable
            title={t`Payment Method Revenue Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="payment_method_revenue_report.csv"
            showDateFilter={true}
            event={event}
        />
    );
};

export default PaymentMethodRevenueReport;
