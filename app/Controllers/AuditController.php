<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Auth;
use DoubleE\Core\Database;
use DoubleE\Core\Request;
use DoubleE\Core\Response;

class AuditController extends BaseController
{
    private Database $db;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->db = Database::getInstance();
    }

    /**
     * Display the audit trail with optional filters and pagination.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('settings.view');

        $perPage = 50;
        $page = max(1, (int) $this->request->get('page', 1));
        $offset = ($page - 1) * $perPage;

        // Collect filters from query params
        $filters = [
            'user_id'     => $this->request->get('user_id', ''),
            'action'      => trim((string) $this->request->get('action', '')),
            'entity_type' => trim((string) $this->request->get('entity_type', '')),
            'date_from'   => trim((string) $this->request->get('date_from', '')),
            'date_to'     => trim((string) $this->request->get('date_to', '')),
        ];

        // Build filtered query
        $where = [];
        $params = [];

        if ($filters['user_id'] !== '') {
            $where[] = 'al.user_id = ?';
            $params[] = (int) $filters['user_id'];
        }

        if ($filters['action'] !== '') {
            $where[] = 'al.action = ?';
            $params[] = $filters['action'];
        }

        if ($filters['entity_type'] !== '') {
            $where[] = 'al.entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        if ($filters['date_from'] !== '') {
            $where[] = 'al.created_at >= ?';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if ($filters['date_to'] !== '') {
            $where[] = 'al.created_at <= ?';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereSql = '';
        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        // Count total matching rows for pagination
        $countSql = "SELECT COUNT(*) FROM audit_log al {$whereSql}";
        $totalRows = (int) $this->db->queryScalar($countSql, $params);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));

        // Fetch page of results with user details
        $sql = "SELECT al.*, CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email
                FROM audit_log al
                LEFT JOIN users u ON u.id = al.user_id
                {$whereSql}
                ORDER BY al.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $entries = $this->db->query($sql, $params);

        // Fetch all users for filter dropdown
        $users = $this->db->query(
            "SELECT id, first_name, last_name, email FROM users ORDER BY first_name, last_name"
        );

        return $this->render('audit/index', [
            'pageTitle'  => 'Audit Trail',
            'entries'    => $entries,
            'users'      => $users,
            'filters'    => $filters,
            'page'       => $page,
            'totalPages' => $totalPages,
            'totalRows'  => $totalRows,
        ]);
    }
}
