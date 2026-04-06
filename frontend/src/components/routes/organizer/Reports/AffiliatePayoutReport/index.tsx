import {useParams} from "react-router";
import {useGetOrganizer} from "../../../../../queries/useGetOrganizer.ts";
import {formatCurrency} from "../../../../../utilites/currency.ts";
import OrganizerReportTable from "../../../../common/OrganizerReportTable";
import {t} from "@lingui/macro";

const AffiliatePayoutReport = () => {
    const {organizerId} = useParams();
    const organizerQuery = useGetOrganizer(organizerId);
    const organizer = organizerQuery.data;

    if (!organizer) {
        return null;
    }

    const columns = [
        {
            key: 'affiliate_name' as const,
            label: t`Affiliate Name`,
            sortable: true
        },
        {
            key: 'affiliate_code' as const,
            label: t`Affiliate Code`,
            sortable: true
        },
        {
            key: 'event_title' as const,
            label: t`Event`,
            sortable: true
        },
        {
            key: 'commission_rate' as const,
            label: t`Commission Rate`,
            sortable: true,
            render: (value: number) => `${value}%`
        },
        {
            key: 'order_count' as const,
            label: t`Orders`,
            sortable: true
        },
        {
            key: 'total_sales' as const,
            label: t`Total Sales`,
            sortable: true,
            render: (value: string, row: any) => formatCurrency(value, row.currency)
        },
        {
            key: 'commission_earned' as const,
            label: t`Commission Earned`,
            sortable: true,
            render: (value: string, row: any) => formatCurrency(value, row.currency)
        }
    ];

    return (
        <OrganizerReportTable
            title={t`Affiliate Payout Report`}
            columns={columns}
            isLoading={organizerQuery.isLoading}
            downloadFileName="affiliate_payout_report.csv"
            showDateFilter={true}
            organizer={organizer}
        />
    );
};

export default AffiliatePayoutReport;
