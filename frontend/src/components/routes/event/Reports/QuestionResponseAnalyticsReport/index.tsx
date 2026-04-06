import {useParams} from "react-router";
import {useGetEvent} from "../../../../../queries/useGetEvent.ts";
import ReportTable from "../../../../common/ReportTable";
import {t} from "@lingui/macro";

const QuestionResponseAnalyticsReport = () => {
    const {eventId} = useParams();
    const eventQuery = useGetEvent(eventId);
    const event = eventQuery.data;

    if (!event) {
        return null;
    }

    const columns = [
        {
            key: 'question_title' as const,
            label: t`Question`,
            sortable: true
        },
        {
            key: 'question_type' as const,
            label: t`Type`,
            sortable: true
        },
        {
            key: 'required' as const,
            label: t`Required`,
            sortable: true,
            render: (value: boolean) => value ? t`Yes` : t`No`
        },
        {
            key: 'answered_count' as const,
            label: t`Answered`,
            sortable: true
        },
        {
            key: 'unanswered_count' as const,
            label: t`Unanswered`,
            sortable: true
        },
        {
            key: 'total_responses' as const,
            label: t`Total`,
            sortable: true
        },
        {
            key: 'response_rate' as const,
            label: t`Response Rate %`,
            sortable: true,
            render: (value: number) => `${value}%`
        }
    ];

    return (
        <ReportTable
            title={t`Question Response Analytics Report`}
            columns={columns}
            isLoading={eventQuery.isLoading}
            downloadFileName="question_response_analytics_report.csv"
            showDateFilter={true}
            event={event}
        />
    );
};

export default QuestionResponseAnalyticsReport;
