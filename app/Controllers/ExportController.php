<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Core\Auth;
use DoubleE\Models\Account;
use DoubleE\Models\Contact;
use DoubleE\Models\Invoice;
use DoubleE\Models\JournalEntry;
use DoubleE\Services\CsvExportService;
use DoubleE\Services\LedgerService;
use DoubleE\Services\ReportService;

class ExportController extends BaseController
{
    private CsvExportService $csv;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->csv = new CsvExportService();
    }

    /**
     * Export chart of accounts as CSV.
     */
    public function accounts(): Response
    {
        Auth::getInstance()->requirePermission('accounts.view');

        $model = new Account();
        $accounts = $model->getAll(false);

        $headers = ['Account Number', 'Name', 'Type', 'Sub-Type', 'Active', 'Balance'];
        $rows = [];

        foreach ($accounts as $account) {
            $rows[] = [
                $account['account_number'],
                $account['name'],
                $account['type_name'] ?? '',
                $account['subtype_name'] ?? '',
                !empty($account['is_active']) ? 'Yes' : 'No',
                $account['opening_balance'] ?? '0.00',
            ];
        }

        $this->csv->export('accounts.csv', $headers, $rows);

        // Never reached due to exit in export(), but satisfies return type
        return $this->response;
    }

    /**
     * Export journal entries as CSV with optional date filters.
     */
    public function journalEntries(): Response
    {
        Auth::getInstance()->requirePermission('journal.view');

        $model = new JournalEntry();

        $filters = [];
        $dateFrom = trim((string) $this->request->get('date_from', ''));
        $dateTo   = trim((string) $this->request->get('date_to', ''));

        if ($dateFrom !== '') {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $filters['date_to'] = $dateTo;
        }

        $entries = $model->getAll($filters);

        $headers = ['Entry #', 'Date', 'Description', 'Reference', 'Status', 'Total Debit', 'Total Credit'];
        $rows = [];

        foreach ($entries as $entry) {
            $rows[] = [
                $entry['entry_number'],
                $entry['entry_date'],
                $entry['description'],
                $entry['reference'] ?? '',
                ucfirst($entry['status']),
                number_format((float) ($entry['total_debit'] ?? 0), 2),
                number_format((float) ($entry['total_credit'] ?? 0), 2),
            ];
        }

        $this->csv->export('journal-entries.csv', $headers, $rows);

        return $this->response;
    }

    /**
     * Export contacts as CSV.
     */
    public function contacts(): Response
    {
        Auth::getInstance()->requirePermission('invoices.view');

        $model = new Contact();
        $contacts = $model->getAll(false);

        $headers = ['Display Name', 'Type', 'Email', 'Phone', 'Payment Terms', 'Outstanding'];
        $rows = [];

        foreach ($contacts as $contact) {
            $outstanding = $model->getOutstandingBalance((int) $contact['id']);
            $rows[] = [
                $contact['display_name'],
                ucfirst($contact['type']),
                $contact['email'] ?? '',
                $contact['phone'] ?? '',
                ($contact['payment_terms'] ?? 0) . ' days',
                number_format((float) $outstanding, 2),
            ];
        }

        $this->csv->export('contacts.csv', $headers, $rows);

        return $this->response;
    }

    /**
     * Export invoices as CSV with optional type filter.
     */
    public function invoices(): Response
    {
        Auth::getInstance()->requirePermission('invoices.view');

        $model = new Invoice();

        $filters = [];
        $docType = trim((string) $this->request->get('document_type', ''));
        if ($docType !== '') {
            $filters['document_type'] = $docType;
        }

        $invoices = $model->getAll($filters);

        $headers = ['Document #', 'Type', 'Contact', 'Date', 'Due Date', 'Total', 'Paid', 'Balance', 'Status'];
        $rows = [];

        foreach ($invoices as $inv) {
            $rows[] = [
                $inv['document_number'],
                ucfirst($inv['document_type']),
                $inv['contact_name'] ?? '',
                $inv['issue_date'],
                $inv['due_date'],
                number_format((float) $inv['total'], 2),
                number_format((float) ($inv['amount_paid'] ?? 0), 2),
                number_format((float) ($inv['balance_due'] ?? 0), 2),
                ucfirst($inv['status']),
            ];
        }

        $this->csv->export('invoices.csv', $headers, $rows);

        return $this->response;
    }

    /**
     * Export account ledger as CSV with optional account and date filters.
     */
    public function ledger(): Response
    {
        Auth::getInstance()->requirePermission('journal.view');

        $accountId = (int) $this->request->get('account_id', '0');
        $dateFrom  = trim((string) $this->request->get('date_from', ''));
        $dateTo    = trim((string) $this->request->get('date_to', ''));

        if ($accountId <= 0) {
            $this->flash('error', 'Account ID is required for ledger export.');
            return $this->redirect('/ledger');
        }

        $accountModel = new Account();
        $account = $accountModel->find($accountId);

        if ($account === null) {
            $this->flash('error', 'Account not found.');
            return $this->redirect('/ledger');
        }

        $ledgerService = new LedgerService();
        $entries = $ledgerService->getAccountLedger(
            $accountId,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null
        );

        $headers = ['Date', 'Entry #', 'Description', 'Debit', 'Credit', 'Balance'];
        $rows = [];

        foreach ($entries as $entry) {
            $rows[] = [
                $entry['entry_date'],
                $entry['entry_number'],
                $entry['entry_description'] ?? $entry['description'] ?? '',
                number_format((float) ($entry['debit'] ?? 0), 2),
                number_format((float) ($entry['credit'] ?? 0), 2),
                number_format((float) ($entry['running_balance'] ?? 0), 2),
            ];
        }

        $filename = 'ledger-' . $account['account_number'] . '.csv';
        $this->csv->export($filename, $headers, $rows);

        return $this->response;
    }

    /**
     * Export trial balance as CSV.
     */
    public function trialBalance(): Response
    {
        Auth::getInstance()->requirePermission('reports.view');

        $asOfDate = trim((string) $this->request->get('as_of_date', ''));
        if ($asOfDate === '') {
            $asOfDate = date('Y-m-d');
        }

        $reportService = new ReportService();
        $report = $reportService->generateTrialBalance($asOfDate);

        $headers = ['Account #', 'Account Name', 'Type', 'Debit', 'Credit'];
        $rows = [];

        foreach ($report['accounts'] ?? [] as $account) {
            $debit = (float) ($account['debit_balance'] ?? $account['debit'] ?? 0);
            $credit = (float) ($account['credit_balance'] ?? $account['credit'] ?? 0);

            $rows[] = [
                $account['account_number'] ?? '',
                $account['name'] ?? '',
                $account['type_name'] ?? '',
                $debit > 0 ? number_format($debit, 2) : '',
                $credit > 0 ? number_format($credit, 2) : '',
            ];
        }

        // Add totals row
        $rows[] = [
            '',
            'TOTALS',
            '',
            number_format((float) ($report['total_debits'] ?? 0), 2),
            number_format((float) ($report['total_credits'] ?? 0), 2),
        ];

        $this->csv->export('trial-balance-' . $asOfDate . '.csv', $headers, $rows);

        return $this->response;
    }
}
