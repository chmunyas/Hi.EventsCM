import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import {formatCurrency} from "../../../../../utilites/currency.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const ProductCategoryPerformanceReport = () => {
    const {eventId} = useParams();
    const eventQuery = useGetEvent(eventId);
    const event = eventQuery.data;

    if (!event) {
        return null;
    }

    const columns = [
        {
            key: 'category_name' as const,
            label: t`Category`,
            sortable: true
        },
        {
            key: 'product_count' as const,
            label: t`Products`,
            sortable: true
        },
        {
            key: 'total_sold' as const,
            label: t`Total Sold`,
            sortable: true
        },
        {
            key: 'gross_revenue' as const,
            label: t`Gross Revenue`,
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
            key: 'total_fees' as const,
            label: t`Total Fees`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        },
        {
            key: 'avg_price' as const,
            label: t`Avg Price`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        }
    ];

    return (
        <ReportTable
            title={t`Product Category Performance Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="product_category_performance_report.csv"
            showDateFilter={true}
            event={event}
        />
    );
};

export default ProductCategoryPerformanceReport;
