<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Models\JournalEntry;
use DoubleE\Services\JournalEntryService;
use DoubleE\Core\Auth;

class JournalController extends BaseController
{
    private JournalEntry $journalModel;
    private Account $accountModel;
    private JournalEntryService $journalService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->journalModel = new JournalEntry();
        $this->accountModel = new Account();
        $this->journalService = new JournalEntryService();
    }

    /**
     * List all journal entries with optional filters.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('journal.view');
        $canCreate = Auth::getInstance()->hasPermission('journal.create');
        $canPost = Auth::getInstance()->hasPermission('journal.post');
        $canVoid = Auth::getInstance()->hasPermission('journal.void');

        $status   = trim((string) $this->request->get('status', ''));
        $dateFrom = trim((string) $this->request->get('date_from', ''));
        $dateTo   = trim((string) $this->request->get('date_to', ''));
        $search   = trim((string) $this->request->get('search', ''));

        $filters = [];
        if ($status !== '') {
            $filters['status'] = $status;
        }
        if ($dateFrom !== '') {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $filters['date_to'] = $dateTo;
        }
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $entries = $this->journalModel->getAll($filters);

        return $this->render('journal/index', [
            'pageTitle' => 'Journal Entries',
            'entries'   => $entries,
            'filters'   => $filters,
            'canCreate' => $canCreate,
            'canPost'   => $canPost,
            'canVoid'   => $canVoid,
        ]);
    }

    /**
     * Show the create journal entry form.
     */
    public function create(): Response
    {
        Auth::getInstance()->requirePermission('journal.create');

        $accounts = $this->accountModel->getLeafAccounts();

        return $this->render('journal/create', [
            'pageTitle' => 'New Journal Entry',
            'accounts'  => $accounts,
        ]);
    }

    /**
     * Store a new journal entry from POST data.
     */
    public function store(): Response
    {
        Auth::getInstance()->requirePermission('journal.create');
        $this->validateCsrf();

        $entryDate   = trim((string) $this->request->post('entry_date', ''));
        $description = trim((string) $this->request->post('description', ''));
        $reference   = trim((string) $this->request->post('reference', ''));
        $notes       = trim((string) $this->request->post('notes', ''));
        $action      = trim((string) $this->request->post('action', 'draft'));

        $lineAccounts     = $this->request->post('line_account', []);
        $lineDescriptions = $this->request->post('line_description', []);
        $lineDebits       = $this->request->post('line_debit', []);
        $lineCredits      = $this->request->post('line_credit', []);

        // Validation
        $errors = [];

        if ($entryDate === '') {
            $errors[] = 'Entry date is required.';
        }
        if ($description === '') {
            $errors[] = 'Description is required.';
        }

        // Build lines array
        $lines = [];
        if (is_array($lineAccounts)) {
            for ($i = 0, $count = count($lineAccounts); $i < $count; $i++) {
                $accountId  = (int) ($lineAccounts[$i] ?? 0);
                $lineDesc   = trim((string) ($lineDescriptions[$i] ?? ''));
                $debit      = round((float) ($lineDebits[$i] ?? 0), 2);
                $credit     = round((float) ($lineCredits[$i] ?? 0), 2);

                if ($accountId === 0 && $debit == 0 && $credit == 0) {
                    continue; // Skip empty lines
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
            $errors[] = 'At least two lines are required.';
        }

        // Check that debits equal credits
        $totalDebits  = array_sum(array_column($lines, 'debit'));
        $totalCredits = array_sum(array_column($lines, 'credit'));
        if (round($totalDebits, 2) !== round($totalCredits, 2)) {
            $errors[] = 'Total debits must equal total credits.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/journal/create');
        }

        $headerData = [
            'entry_date'  => $entryDate,
            'description' => $description,
            'reference'   => $reference !== '' ? $reference : null,
            'notes'       => $notes !== '' ? $notes : null,
        ];

        $autoPost = ($action === 'post');

        $entryId = $this->journalService->create($headerData, $lines, $autoPost);

        if ($autoPost) {
            $this->flash('success', 'Journal entry created and posted successfully.');
        } else {
            $this->flash('success', 'Journal entry saved as draft.');
        }

        return $this->redirect('/journal/' . $entryId);
    }

    /**
     * Display a single journal entry with its lines.
     */
    public function show(string $id): Response
    {
        Auth::getInstance()->requirePermission('journal.view');
        $canCreate = Auth::getInstance()->hasPermission('journal.create');
        $canPost = Auth::getInstance()->hasPermission('journal.post');
        $canVoid = Auth::getInstance()->hasPermission('journal.void');

        $entry = $this->journalModel->getWithLines((int) $id);

        if ($entry === null) {
            $this->flash('error', 'Journal entry not found.');
            return $this->redirect('/journal');
        }

        // If this entry was reversed, load the reversing entry info
        $reversingEntry = null;
        if (!empty($entry['reversing_entry_id'])) {
            $reversingEntry = $this->journalModel->find((int) $entry['reversing_entry_id']);
        }

        // Check if another entry reversed this one
        $reversedBy = $this->journalModel->findBy('reversing_entry_id', (int) $id);

        return $this->render('journal/show', [
            'pageTitle'      => 'Journal Entry ' . ($entry['entry_number'] ?? $id),
            'entry'          => $entry,
            'reversingEntry' => $reversingEntry,
            'reversedBy'     => $reversedBy,
            'canCreate'      => $canCreate,
            'canPost'        => $canPost,
            'canVoid'        => $canVoid,
        ]);
    }

    /**
     * Post a draft journal entry.
     */
    public function post(string $id): Response
    {
        Auth::getInstance()->requirePermission('journal.post');
        $this->validateCsrf();

        try {
            $this->journalService->post((int) $id);
            $this->flash('success', 'Journal entry posted successfully.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect('/journal/' . $id);
    }

    /**
     * Void a posted journal entry.
     */
    public function void(string $id): Response
    {
        Auth::getInstance()->requirePermission('journal.void');
        $this->validateCsrf();

        $voidReason = trim((string) $this->request->post('void_reason', ''));

        if ($voidReason === '') {
            $this->flash('error', 'A void reason is required.');
            return $this->redirect('/journal/' . $id);
        }

        try {
            $this->journalService->void((int) $id, $voidReason);
            $this->flash('success', 'Journal entry voided successfully.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect('/journal/' . $id);
    }
}
