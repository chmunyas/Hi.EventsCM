<?php

namespace HiEvents\Http\Actions\Organizers;

use HiEvents\DomainObjects\OrganizerDomainObject;
use HiEvents\Http\Actions\BaseAction;
use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetOrganizerAuditLogAction extends BaseAction
{
    public function __construct(private readonly DatabaseManager $db)
    {
    }

    public function __invoke(Request $request, int $organizerId): JsonResponse
    {
        $this->isActionAuthorized($organizerId, OrganizerDomainObject::class);

        $accountId = $this->getAuthenticatedAccountId();
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(1, (int) $request->get('per_page', 25)));
        $offset = ($page - 1) * $perPage;

        $logs = $this->db->select(<<<SQL
            SELECT
                oal.id,
                oal.action,
                oal.entity_type,
                oal.entity_id,
                oal.details,
                oal.ip_address,
                oal.created_at,
                u.first_name,
                u.last_name,
                u.email
            FROM organizer_audit_logs oal
            JOIN users u ON oal.user_id = u.id
            WHERE oal.account_id = :account_id
            ORDER BY oal.created_at DESC
            LIMIT :limit OFFSET :offset
SQL
        , [
            'account_id' => $accountId,
            'limit' => $perPage,
            'offset' => $offset,
        ]);

        $total = $this->db->selectOne(
            'SELECT COUNT(*) as count FROM organizer_audit_logs WHERE account_id = :account_id',
            ['account_id' => $accountId]
        );

        return $this->jsonResponse([
            'data' => $logs,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total->count,
                'last_page' => (int) ceil($total->count / $perPage),
            ],
        ]);
    }
}
