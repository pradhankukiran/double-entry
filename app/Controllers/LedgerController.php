<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Services\LedgerService;

class LedgerController extends BaseController
{
    private Account $accountModel;
    private LedgerService $ledgerService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->accountModel = new Account();
        $this->ledgerService = new LedgerService();
    }

    /**
     * Display the general ledger view.
     */
    public function index(): Response
    {
        $dateFrom = trim((string) $this->request->get('date_from', ''));
        $dateTo   = trim((string) $this->request->get('date_to', ''));

        $filters = [];
        if ($dateFrom !== '') {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $filters['date_to'] = $dateTo;
        }

        $entries = $this->ledgerService->getGeneralLedger(
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null
        );

        return $this->render('ledger/general', [
            'pageTitle' => 'General Ledger',
            'entries'   => $entries,
            'filters'   => $filters,
        ]);
    }

    /**
     * Display the ledger for a specific account with running balance.
     */
    public function account(string $id): Response
    {
        $account = $this->accountModel->find((int) $id);

        if ($account === null) {
            $this->flash('error', 'Account not found.');
            return $this->redirect('/ledger');
        }

        $dateFrom = trim((string) $this->request->get('date_from', ''));
        $dateTo   = trim((string) $this->request->get('date_to', ''));

        $filters = [];
        if ($dateFrom !== '') {
            $filters['date_from'] = $dateFrom;
        }
        if ($dateTo !== '') {
            $filters['date_to'] = $dateTo;
        }

        $ledger = $this->ledgerService->getAccountLedger((int) $id, $filters);

        return $this->render('ledger/account', [
            'pageTitle' => $account['account_number'] . ' - ' . $account['name'] . ' | Ledger',
            'account'   => $account,
            'ledger'    => $ledger,
            'filters'   => $filters,
        ]);
    }
}
