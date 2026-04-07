<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Models\RecurringTemplate;
use DoubleE\Services\RecurringTransactionService;
use DoubleE\Core\Auth;

class RecurringController extends BaseController
{
    private RecurringTemplate $templateModel;
    private Account $accountModel;
    private RecurringTransactionService $recurringService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->templateModel = new RecurringTemplate();
        $this->accountModel = new Account();
        $this->recurringService = new RecurringTransactionService();
    }

    /**
     * List all recurring templates.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('journal.view');
        $canCreate = Auth::getInstance()->hasPermission('journal.create');

        $templates = $this->templateModel->getAll();

        return $this->render('recurring/index', [
            'pageTitle'  => 'Recurring Transactions',
            'templates'  => $templates,
            'canCreate'  => $canCreate,
        ]);
    }

    /**
     * Show the form for creating a new recurring template.
     */
    public function create(): Response
    {
        Auth::getInstance()->requirePermission('journal.create');

        $accounts = $this->accountModel->getLeafAccounts();

        // Group accounts by type for optgroups
        $accountsByType = [];
        foreach ($accounts as $acct) {
            $typeName = $acct['type_name'] ?? 'Other';
            $accountsByType[$typeName][] = $acct;
        }

        return $this->render('recurring/create', [
            'pageTitle'      => 'New Recurring Transaction',
            'accounts'       => $accounts,
            'accountsByType' => $accountsByType,
        ]);
    }

    /**
     * Store a new recurring template with lines.
     */
    public function store(): Response
    {
        Auth::getInstance()->requirePermission('journal.create');
        $this->validateCsrf();

        $name       = trim((string) $this->request->post('name', ''));
        $type       = trim((string) $this->request->post('type', ''));
        $frequency  = trim((string) $this->request->post('frequency', ''));
        $startDate  = trim((string) $this->request->post('start_date', ''));
        $endDate    = trim((string) $this->request->post('end_date', ''));
        $autoPost   = $this->request->post('auto_post') ? 1 : 0;
        $description = trim((string) $this->request->post('description', ''));

        $lineAccounts     = $this->request->post('line_account', []);
        $lineDescriptions = $this->request->post('line_description', []);
        $lineDebits       = $this->request->post('line_debit', []);
        $lineCredits      = $this->request->post('line_credit', []);

        $errors = [];

        if ($name === '') {
            $errors[] = 'Template name is required.';
        }
        if (!in_array($type, ['journal_entry', 'invoice', 'bill'], true)) {
            $errors[] = 'Valid transaction type is required.';
        }
        if (!in_array($frequency, ['daily', 'weekly', 'biweekly', 'monthly', 'quarterly', 'annually'], true)) {
            $errors[] = 'Valid frequency is required.';
        }
        if ($startDate === '') {
            $errors[] = 'Start date is required.';
        }

        // Build lines array
        $lines = [];
        if (is_array($lineAccounts)) {
            for ($i = 0, $count = count($lineAccounts); $i < $count; $i++) {
                $accountId = (int) ($lineAccounts[$i] ?? 0);
                $lineDesc  = trim((string) ($lineDescriptions[$i] ?? ''));
                $debit     = round((float) ($lineDebits[$i] ?? 0), 2);
                $credit    = round((float) ($lineCredits[$i] ?? 0), 2);

                if ($accountId === 0 && $debit == 0 && $credit == 0) {
                    continue;
                }

                if ($accountId === 0) {
                    $errors[] = 'Line ' . ($i + 1) . ': Account is required.';
                }
                if ($debit == 0 && $credit == 0) {
                    $errors[] = 'Line ' . ($i + 1) . ': Debit or credit amount is required.';
                }
                if ($debit > 0 && $credit > 0) {
                    $errors[] = 'Line ' . ($i + 1) . ': A line cannot have both debit and credit.';
                }

                $lines[] = [
                    'account_id'  => $accountId,
                    'description' => $lineDesc,
                    'debit'       => $debit,
                    'credit'      => $credit,
                    'line_order'  => $i,
                ];
            }
        }

        if (count($lines) < 2) {
            $errors[] = 'At least two line items are required.';
        }

        // Check debits = credits
        $totalDebits  = array_sum(array_column($lines, 'debit'));
        $totalCredits = array_sum(array_column($lines, 'credit'));
        if (round($totalDebits, 2) !== round($totalCredits, 2)) {
            $errors[] = 'Total debits must equal total credits.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/recurring/create');
        }

        $templateData = [
            'name'        => $name,
            'type'        => $type,
            'frequency'   => $frequency,
            'start_date'  => $startDate,
            'end_date'    => $endDate !== '' ? $endDate : null,
            'auto_post'   => $autoPost,
            'description' => $description !== '' ? $description : null,
        ];

        $templateId = $this->recurringService->createTemplate($templateData, $lines);

        $this->flash('success', 'Recurring template created successfully.');
        return $this->redirect('/recurring/' . $templateId);
    }

    /**
     * Display a single recurring template with its lines.
     */
    public function show(string $id): Response
    {
        Auth::getInstance()->requirePermission('journal.view');
        $canCreate = Auth::getInstance()->hasPermission('journal.create');

        $template = $this->templateModel->getWithLines((int) $id);

        if ($template === null) {
            $this->flash('error', 'Recurring template not found.');
            return $this->redirect('/recurring');
        }

        return $this->render('recurring/show', [
            'pageTitle' => 'Recurring: ' . ($template['name'] ?? 'Template'),
            'template'  => $template,
            'canCreate' => $canCreate,
        ]);
    }

    /**
     * Manually trigger creating a transaction from a template.
     */
    public function run(string $id): Response
    {
        Auth::getInstance()->requirePermission('journal.post');
        $this->validateCsrf();

        try {
            $result = $this->recurringService->createFromTemplate((int) $id);

            $this->flash('success', 'Transaction created from template successfully.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect('/recurring/' . $id);
    }
}
