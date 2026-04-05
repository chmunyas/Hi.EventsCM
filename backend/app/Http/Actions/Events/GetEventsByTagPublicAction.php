<?php

namespace HiEvents\Http\Actions\Events;

use HiEvents\DomainObjects\EventDomainObject;
use HiEvents\Http\Actions\BaseAction;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetEventsByTagPublicAction extends BaseAction
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $tags = $request->query('tags');
        $category = $request->query('category');
        $limit = min((int)($request->query('limit', 20)), 100);
        $offset = max((int)($request->query('offset', 0)), 0);

        $params = [];
        $conditions = ["e.deleted_at IS NULL", "e.status = 'LIVE'"];

        if ($tags) {
            $tagList = array_map('trim', explode(',', $tags));
            $placeholders = [];
            foreach ($tagList as $i => $tag) {
                $key = "tag_{$i}";
                $placeholders[] = ":$key";
                $params[$key] = strtolower($tag);
            }
            $conditions[] = "EXISTS (
                SELECT 1 FROM jsonb_array_elements_text(COALESCE(e.tags, '[]'::jsonb)) t
                WHERE LOWER(t) IN (" . implode(',', $placeholders) . ")
            )";
        }

        if ($category) {
            $params['category'] = $category;
            $conditions[] = "e.event_category = :category";
        }

        $whereClause = implode(' AND ', $conditions);

        $results = $this->db->select(<<<SQL
            SELECT
                e.id,
                e.title,
                e.description,
                e.start_date,
                e.end_date,
                e.status,
                e.currency,
                e.timezone,
                e.slug,
                e.tags,
                e.event_category,
                o.name AS organizer_name
            FROM events e
            LEFT JOIN organizers o ON e.organizer_id = o.id
            WHERE $whereClause
            ORDER BY e.start_date ASC
            LIMIT $limit OFFSET $offset
SQL
        , $params);

        return $this->jsonResponse($results);
    }
}
