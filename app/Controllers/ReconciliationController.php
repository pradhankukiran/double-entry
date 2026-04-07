<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\BankAccount;
use DoubleE\Models\BankReconciliation;
use DoubleE\Services\ReconciliationService;

class ReconciliationController extends BaseController
{
    private BankAccount $bankAccountModel;
    private BankReconciliation $reconciliationModel;
    private ReconciliationService $reconciliationService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->bankAccountModel = new BankAccount();
        $this->reconciliationModel = new BankReconciliation();
        $this->reconciliationService = new ReconciliationService();
    }

    /**
     * List bank accounts with reconciliation status.
     */
    public function index(): Response
    {
        $accounts = $this->bankAccountModel->getAllWithReconciliationStatus();

        return $this->render('banking/reconcile/index', [
            'pageTitle' => 'Bank Reconciliation',
            'accounts'  => $accounts,
        ]);
    }

    /**
     * Show the form to start a new reconciliation.
     */
    public function start(string $id): Response
    {
        $account = $this->bankAccountModel->find((int) $id);

        if ($account === null) {
            $this->flash('error', 'Bank account not found.');
            return $this->redirect('/banking/reconcile');
        }

        return $this->render('banking/reconcile/start', [
            'pageTitle' => 'Start Reconciliation - ' . $account['account_name'],
            'account'   => $account,
        ]);
    }

    /**
     * Create a new reconciliation session.
     */
    public function create(): Response
    {
        $this->validateCsrf();

        $bankAccountId   = (int) $this->request->post('bank_account_id', 0);
        $statementDate   = trim((string) $this->request->post('statement_date', ''));
        $statementBalance = trim((string) $this->request->post('statement_balance', ''));

        $account = $this->bankAccountModel->find($bankAccountId);

        if ($account === null) {
            $this->flash('error', 'Bank account not found.');
            return $this->redirect('/banking/reconcile');
        }

        $errors = [];

        if ($statementDate === '') {
            $errors[] = 'Statement date is required.';
        }
        if ($statementBalance === '') {
            $errors[] = 'Statement ending balance is required.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/banking/reconcile/' . $bankAccountId . '/start');
        }

        $reconciliation = $this->reconciliationService->start(
            $bankAccountId,
            $statementDate,
            $statementBalance
        );

        $this->flash('success', 'Reconciliation started.');
        return $this->redirect('/banking/reconcile/' . $reconciliation['id']);
    }

    /**
     * Display the reconciliation working screen.
     */
    public function reconcile(string $id): Response
    {
        $reconciliation = $this->reconciliationModel->find((int) $id);

        if ($reconciliation === null) {
            $this->flash('error', 'Reconciliation not found.');
            return $this->redirect('/banking/reconcile');
        }

        $account = $this->bankAccountModel->find((int) $reconciliation['bank_account_id']);
        $data = $this->reconciliationService->getReconciliationData((int) $id);

        return $this->render('banking/reconcile/reconcile', [
            'pageTitle'      => 'Reconcile - ' . ($account['account_name'] ?? ''),
            'reconciliation' => $reconciliation,
            'account'        => $account,
            'unclearedItems' => $data['uncleared'] ?? [],
            'clearedItems'   => $data['cleared'] ?? [],
            'clearedBalance' => $data['cleared_balance'] ?? '0.00',
            'difference'     => $data['difference'] ?? '0.00',
        ]);
    }

    /**
     * Toggle the cleared status of a transaction (AJAX).
     */
    public function toggle(): Response
    {
        $this->validateCsrf();

        $reconciliationId = (int) $this->request->post('reconciliation_id', 0);
        $transactionId    = (int) $this->request->post('transaction_id', 0);

        $result = $this->reconciliationService->toggleCleared($reconciliationId, $transactionId);

        return $this->json([
            'success'        => true,
            'cleared'        => $result['cleared'],
            'clearedBalance' => $result['cleared_balance'],
            'difference'     => $result['difference'],
        ]);
    }

    /**
     * Complete the reconciliation.
     */
    public function complete(string $id): Response
    {
        $this->validateCsrf();

        $reconciliation = $this->reconciliationModel->find((int) $id);

        if ($reconciliation === null) {
            $this->flash('error', 'Reconciliation not found.');
            return $this->redirect('/banking/reconcile');
        }

        $difference = $this->reconciliationService->calculateDifference((int) $id);

        if (bccomp($difference, '0.00', 2) !== 0) {
            $this->flash('error', 'Cannot complete reconciliation. The difference must be zero.');
            return $this->redirect('/banking/reconcile/' . $id);
        }

        $this->reconciliationService->complete((int) $id);

        $this->flash('success', 'Reconciliation completed successfully.');
        return $this->redirect('/banking/reconcile');
    }
}
