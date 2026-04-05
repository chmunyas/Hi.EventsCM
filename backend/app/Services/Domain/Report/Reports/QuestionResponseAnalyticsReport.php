<?php

namespace HiEvents\Services\Domain\Report\Reports;

use HiEvents\Services\Domain\Report\AbstractReportService;
use Illuminate\Support\Carbon;

class QuestionResponseAnalyticsReport extends AbstractReportService
{
    protected function getSqlQuery(Carbon $startDate, Carbon $endDate): string
    {
        $startDateString = $startDate->format('Y-m-d H:i:s');
        $endDateString = $endDate->format('Y-m-d H:i:s');

        return <<<SQL
        WITH question_stats AS (
            SELECT
                q.id AS question_id,
                q.title AS question_title,
                q.type AS question_type,
                q.required,
                COUNT(DISTINCT CASE WHEN qa.answer IS NOT NULL AND qa.answer != '' AND qa.answer != '[]' THEN qa.id END) AS answered_count,
                COUNT(DISTINCT CASE WHEN qa.answer IS NULL OR qa.answer = '' OR qa.answer = '[]' THEN qa.id END) AS unanswered_count,
                COUNT(DISTINCT qa.id) AS total_responses
            FROM questions q
            LEFT JOIN question_answers qa ON qa.question_id = q.id
                AND qa.deleted_at IS NULL
                AND qa.created_at BETWEEN '$startDateString' AND '$endDateString'
            WHERE q.event_id = :event_id AND q.deleted_at IS NULL
            GROUP BY q.id, q.title, q.type, q.required
        )
        SELECT
            question_id,
            question_title,
            question_type,
            required,
            answered_count,
            unanswered_count,
            total_responses,
            CASE
                WHEN total_responses = 0 THEN 0
                ELSE ROUND((answered_count::numeric / total_responses) * 100, 1)
            END AS response_rate
        FROM question_stats
        ORDER BY question_title ASC
SQL;
    }
}
