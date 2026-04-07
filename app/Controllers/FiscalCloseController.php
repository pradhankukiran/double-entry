<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Auth;
use DoubleE\Core\Request;
use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Models\FiscalYear;
use DoubleE\Services\FiscalYearService;
use DoubleE\Services\LedgerService;

class FiscalCloseController extends BaseController
{
    private FiscalYear $fiscalYearModel;
    private FiscalYearService $fiscalYearService;
    private LedgerService $ledgerService;
    private Account $accountModel;

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->fiscalYearModel = new FiscalYear();
        $this->fiscalYearService = new FiscalYearService();
        $this->ledgerService = new LedgerService();
        $this->accountModel = new Account();
    }

    /**
     * Preview the fiscal year closing — show proposed closing journal entry.
     */
    public function preview(string $id): Response
    {
        Auth::getInstance()->requirePermission('period.close');

        bcscale(2);

        $year = $this->fiscalYearModel->find((int) $id);
        if ($year === null) {
            $this->flash('error', 'Fiscal year not found.');
            return $this->redirect('/fiscal-years');
        }

        if ($year['status'] !== 'open') {
            $this->flash('error', 'This fiscal year is already closed.');
            return $this->redirect('/fiscal-years');
        }

        $endDate = $year['end_date'];

        // Gather revenue account balances
        $revenueAccounts = $this->accountModel->getByType(4, false);
        $revenueLines = [];
        $totalRevenue = '0.00';

        foreach ($revenueAccounts as $account) {
            if ((int) ($account['is_header'] ?? 0) === 1) {
                continue;
            }
            $balance = $this->ledgerService->getAccountBalance((int) $account['id'], $endDate);
            if (bccomp($balance, '0.00') !== 0) {
                $revenueLines[] = [
                    'account_id'     => (int) $account['id'],
                    'account_number' => $account['account_number'],
                    'account_name'   => $account['name'],
                    'debit'          => $balance,
                    'credit'         => '0.00',
                ];
                $totalRevenue = bcadd($totalRevenue, $balance);
            }
        }

        // Gather expense account balances
        $expenseAccounts = $this->accountModel->getByType(5, false);
        $expenseLines = [];
        $totalExpenses = '0.00';

        foreach ($expenseAccounts as $account) {
            if ((int) ($account['is_header'] ?? 0) === 1) {
                continue;
            }
            $balance = $this->ledgerService->getAccountBalance((int) $account['id'], $endDate);
            if (bccomp($balance, '0.00') !== 0) {
                $expenseLines[] = [
                    'account_id'     => (int) $account['id'],
                    'account_number' => $account['account_number'],
                    'account_name'   => $account['name'],
                    'debit'          => '0.00',
                    'credit'         => $balance,
                ];
                $totalExpenses = bcadd($totalExpenses, $balance);
            }
        }

        // Net income and Retained Earnings line
        $netIncome = bcsub($totalRevenue, $totalExpenses);
        $retainedEarnings = $this->accountModel->findByNumber('3100');

        $reLine = null;
        if ($retainedEarnings !== null && bccomp($netIncome, '0.00') !== 0) {
            $reLine = [
                'account_id'     => (int) $retainedEarnings['id'],
                'account_number' => $retainedEarnings['account_number'],
                'account_name'   => $retainedEarnings['name'],
                'debit'          => bccomp($netIncome, '0.00') < 0 ? bcmul($netIncome, '-1') : '0.00',
                'credit'         => bccomp($netIncome, '0.00') > 0 ? $netIncome : '0.00',
            ];
        }

        return $this->render('fiscal-years/close-preview', [
            'pageTitle'        => "Close Fiscal Year: {$year['name']}",
            'year'             => $year,
            'revenueLines'     => $revenueLines,
            'expenseLines'     => $expenseLines,
            'reLine'           => $reLine,
            'totalRevenue'     => $totalRevenue,
            'totalExpenses'    => $totalExpenses,
            'netIncome'        => $netIncome,
        ]);
    }

    /**
     * Execute the fiscal year close.
     */
    public function execute(string $id): Response
    {
        Auth::getInstance()->requirePermission('period.close');
        $this->validateCsrf();

        $userId = Auth::getInstance()->userId();

        try {
            $entryId = $this->fiscalYearService->closeYearWithEntries((int) $id, $userId);
            $this->flash(
                'success',
                "Fiscal year closed successfully. <a href=\"/journal-entries/{$entryId}\" class=\"alert-link\">View closing journal entry</a>."
            );
        } catch (\RuntimeException $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect('/fiscal-years');
    }
}
