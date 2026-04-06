import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import {formatDateWithLocale} from "../../../../../utilites/dates.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const CheckInByProductReport = () => {
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
            key: 'total_attendees' as const,
            label: t`Total Attendees`,
            sortable: true
        },
        {
            key: 'checked_in_count' as const,
            label: t`Checked In`,
            sortable: true
        },
        {
            key: 'not_checked_in_count' as const,
            label: t`Not Checked In`,
            sortable: true
        },
        {
            key: 'check_in_rate' as const,
            label: t`Check-in Rate %`,
            sortable: true,
            render: (value: number) => `${value}%`
        },
        {
            key: 'first_check_in' as const,
            label: t`First Check-in`,
            sortable: true,
            render: (value: string) => value ? formatDateWithLocale(value, 'shortDateTime', event?.timezone) : '-'
        },
        {
            key: 'last_check_in' as const,
            label: t`Last Check-in`,
            sortable: true,
            render: (value: string) => value ? formatDateWithLocale(value, 'shortDateTime', event?.timezone) : '-'
        }
    ];

    return (
        <ReportTable
            title={t`Check-in by Product Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="check_in_by_product_report.csv"
            event={event}
        />
    );
};

export default CheckInByProductReport;
