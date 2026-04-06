import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import {formatCurrency} from "../../../../../utilites/currency.ts";
import {formatDateWithLocale} from "../../../../../utilites/dates.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const RefundAnalyticsReport = () => {
    const {eventId} = useParams();
    const eventQuery = useGetEvent(eventId);
    const event = eventQuery.data;

    if (!event) {
        return null;
    }

    const columns = [
        {
            key: 'refund_date' as const,
            label: t`Date`,
            sortable: true,
            render: (value: string) => formatDateWithLocale(value, 'shortDate', event?.timezone)
        },
        {
            key: 'refund_count' as const,
            label: t`Refund Count`,
            sortable: true
        },
        {
            key: 'total_refunded' as const,
            label: t`Total Refunded`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        },
        {
            key: 'full_refund_count' as const,
            label: t`Full Refunds`,
            sortable: true
        },
        {
            key: 'partial_refund_count' as const,
            label: t`Partial Refunds`,
            sortable: true
        },
        {
            key: 'avg_refund_amount' as const,
            label: t`Avg Refund Amount`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        }
    ];

    return (
        <ReportTable
            title={t`Refund Analytics Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="refund_analytics_report.csv"
            showDateFilter={true}
            event={event}
        />
    );
};

export default RefundAnalyticsReport;
