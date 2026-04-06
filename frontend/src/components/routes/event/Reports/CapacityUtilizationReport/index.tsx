import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const CapacityUtilizationReport = () => {
    const {eventId} = useParams();
    const eventQuery = useGetEvent(eventId);
    const event = eventQuery.data;

    if (!event) {
        return null;
    }

    const columns = [
        {
            key: 'product_title' as const,
            label: t`Product`,
            sortable: true
        },
        {
            key: 'product_type' as const,
            label: t`Type`,
            sortable: true
        },
        {
            key: 'total_capacity' as const,
            label: t`Total Capacity`,
            sortable: true,
            render: (value: number) => value === 0 ? t`Unlimited` : value
        },
        {
            key: 'quantity_sold' as const,
            label: t`Quantity Sold`,
            sortable: true
        },
        {
            key: 'remaining' as const,
            label: t`Remaining`,
            sortable: true
        },
        {
            key: 'utilization_percent' as const,
            label: t`Utilization %`,
            sortable: true,
            render: (value: number) => `${value}%`
        }
    ];

    return (
        <ReportTable
            title={t`Capacity Utilization Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="capacity_utilization_report.csv"
            event={event}
        />
    );
};

export default CapacityUtilizationReport;
