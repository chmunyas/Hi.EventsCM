import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import {formatCurrency} from "../../../../../utilites/currency.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const AttendeeGeographicReport = () => {
    const {eventId} = useParams();
    const eventQuery = useGetEvent(eventId);
    const event = eventQuery.data;

    if (!event) {
        return null;
    }

    const columns = [
        {
            key: 'country' as const,
            label: t`Country`,
            sortable: true
        },
        {
            key: 'city' as const,
            label: t`City`,
            sortable: true
        },
        {
            key: 'state_region' as const,
            label: t`State/Region`,
            sortable: true
        },
        {
            key: 'order_count' as const,
            label: t`Orders`,
            sortable: true
        },
        {
            key: 'attendee_count' as const,
            label: t`Attendees`,
            sortable: true
        },
        {
            key: 'total_revenue' as const,
            label: t`Total Revenue`,
            sortable: true,
            render: (value: string) => formatCurrency(value, event?.currency)
        }
    ];

    return (
        <ReportTable
            title={t`Attendee Geographic Distribution Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="attendee_geographic_report.csv"
            showDateFilter={true}
            event={event}
        />
    );
};

export default AttendeeGeographicReport;
