<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Database;
use DoubleE\Core\Response;
use DoubleE\Core\Auth;

class SearchController extends BaseController
{
    private Database $db;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->db = Database::getInstance();
    }

    /**
     * Full search results page.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('accounts.view');

        $q = trim((string) $this->request->get('q', ''));

        if ($q === '') {
            return $this->render('search/index', [
                'pageTitle' => 'Search',
                'query'     => '',
                'results'   => [],
            ]);
        }

        $results = $this->search($q, 10);

        return $this->render('search/index', [
            'pageTitle' => 'Search: ' . $q,
            'query'     => $q,
            'results'   => $results,
        ]);
    }

    /**
     * JSON autocomplete endpoint for the navbar search dropdown.
     */
    public function autocomplete(): Response
    {
        Auth::getInstance()->requirePermission('accounts.view');

        $q = trim((string) $this->request->get('q', ''));

        if ($q === '' || mb_strlen($q) < 2) {
            return $this->json([]);
        }

        $results = $this->search($q, 3);

        // Flatten and limit to 8 total
        $flat = [];
        foreach ($results as $group) {
            foreach ($group['items'] as $item) {
                $flat[] = [
                    'type'  => $item['type'],
                    'label' => $item['title'],
                    'url'   => $item['link'],
                ];
                if (count($flat) >= 8) {
                    break 2;
                }
            }
        }

        return $this->json($flat);
    }

    /**
     * Search across all entity tables.
     *
     * @param string $term  The search term
     * @param int    $limit Max results per entity type
     *
     * @return array Grouped results: [ ['type' => ..., 'label' => ..., 'items' => [...]], ... ]
     */
    private function search(string $term, int $limit): array
    {
        $like = '%' . $term . '%';
        $results = [];

        // Accounts
        $accounts = $this->db->query(
            "SELECT id, account_number, name
             FROM accounts
             WHERE account_number LIKE ? OR name LIKE ?
             ORDER BY account_number
             LIMIT ?",
            [$like, $like, $limit]
        );
        if (!empty($accounts)) {
            $items = [];
            foreach ($accounts as $row) {
                $items[] = [
                    'type'     => 'account',
                    'title'    => $row['account_number'] . ' - ' . $row['name'],
                    'subtitle' => 'Account',
                    'link'     => '/accounts/' . $row['id'],
                    'icon'     => 'bi-journal-text',
                ];
            }
            $results[] = ['type' => 'Accounts', 'label' => 'Accounts', 'items' => $items];
        }

        // Contacts
        $contacts = $this->db->query(
            "SELECT id, display_name, email, company_name
             FROM contacts
             WHERE display_name LIKE ? OR email LIKE ? OR company_name LIKE ?
             ORDER BY display_name
             LIMIT ?",
            [$like, $like, $like, $limit]
        );
        if (!empty($contacts)) {
            $items = [];
            foreach ($contacts as $row) {
                $subtitle = $row['company_name'] ?? $row['email'] ?? 'Contact';
                $items[] = [
                    'type'     => 'contact',
                    'title'    => $row['display_name'],
                    'subtitle' => $subtitle,
                    'link'     => '/contacts/' . $row['id'],
                    'icon'     => 'bi-person',
                ];
            }
            $results[] = ['type' => 'Contacts', 'label' => 'Contacts', 'items' => $items];
        }

        // Invoices
        $invoices = $this->db->query(
            "SELECT i.id, i.document_number, i.reference, i.document_type, c.display_name AS contact_name
             FROM invoices i
             INNER JOIN contacts c ON c.id = i.contact_id
             WHERE i.document_number LIKE ? OR i.reference LIKE ?
             ORDER BY i.issue_date DESC
             LIMIT ?",
            [$like, $like, $limit]
        );
        if (!empty($invoices)) {
            $items = [];
            foreach ($invoices as $row) {
                $items[] = [
                    'type'     => 'invoice',
                    'title'    => $row['document_number'],
                    'subtitle' => ucfirst($row['document_type']) . ' - ' . $row['contact_name'],
                    'link'     => '/invoices/' . $row['id'],
                    'icon'     => 'bi-receipt',
                ];
            }
            $results[] = ['type' => 'Invoices', 'label' => 'Invoices', 'items' => $items];
        }

        // Journal Entries
        $entries = $this->db->query(
            "SELECT id, entry_number, description, reference
             FROM journal_entries
             WHERE entry_number LIKE ? OR description LIKE ? OR reference LIKE ?
             ORDER BY entry_date DESC
             LIMIT ?",
            [$like, $like, $like, $limit]
        );
        if (!empty($entries)) {
            $items = [];
            foreach ($entries as $row) {
                $subtitle = $row['description'] ?: ($row['reference'] ?: 'Journal Entry');
                $items[] = [
                    'type'     => 'journal',
                    'title'    => $row['entry_number'],
                    'subtitle' => $subtitle,
                    'link'     => '/journal-entries/' . $row['id'],
                    'icon'     => 'bi-book',
                ];
            }
            $results[] = ['type' => 'Journal Entries', 'label' => 'Journal Entries', 'items' => $items];
        }

        // Payments
        $payments = $this->db->query(
            "SELECT p.id, p.payment_number, p.reference, c.display_name AS contact_name
             FROM payments p
             INNER JOIN contacts c ON c.id = p.contact_id
             WHERE p.payment_number LIKE ? OR p.reference LIKE ?
             ORDER BY p.payment_date DESC
             LIMIT ?",
            [$like, $like, $limit]
        );
        if (!empty($payments)) {
            $items = [];
            foreach ($payments as $row) {
                $items[] = [
                    'type'     => 'payment',
                    'title'    => $row['payment_number'],
                    'subtitle' => $row['contact_name'] ?: ($row['reference'] ?: 'Payment'),
                    'link'     => '/payments/' . $row['id'],
                    'icon'     => 'bi-credit-card',
                ];
            }
            $results[] = ['type' => 'Payments', 'label' => 'Payments', 'items' => $items];
        }

        return $results;
    }
}
