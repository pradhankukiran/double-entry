<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\BankAccount;
use DoubleE\Models\BankTransaction;
use DoubleE\Models\Account;

class BankAccountController extends BaseController
{
    private BankAccount $bankAccountModel;
    private BankTransaction $bankTransactionModel;
    private Account $accountModel;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->bankAccountModel = new BankAccount();
        $this->bankTransactionModel = new BankTransaction();
        $this->accountModel = new Account();
    }

    /**
     * List all bank accounts with GL account info.
     */
    public function index(): Response
    {
        $accounts = $this->bankAccountModel->getAllWithGlAccount();

        return $this->render('banking/accounts/index', [
            'pageTitle' => 'Bank Accounts',
            'accounts'  => $accounts,
        ]);
    }

    /**
     * Show the form for creating a new bank account.
     */
    public function create(): Response
    {
        $glAccounts = $this->accountModel->getBankTypeAccounts();

        return $this->render('banking/accounts/create', [
            'pageTitle'  => 'Add Bank Account',
            'glAccounts' => $glAccounts,
        ]);
    }

    /**
     * Store a newly created bank account.
     */
    public function store(): Response
    {
        $this->validateCsrf();

        $bankName     = trim((string) $this->request->post('bank_name', ''));
        $accountName  = trim((string) $this->request->post('account_name', ''));
        $lastFour     = trim((string) $this->request->post('last_four', ''));
        $glAccountId  = (int) $this->request->post('gl_account_id', 0);
        $accountType  = trim((string) $this->request->post('account_type', ''));
        $currencyCode = trim((string) $this->request->post('currency_code', 'USD'));

        $errors = [];

        if ($bankName === '') {
            $errors[] = 'Bank name is required.';
        }
        if ($accountName === '') {
            $errors[] = 'Account name is required.';
        }
        if ($glAccountId === 0) {
            $errors[] = 'GL account is required.';
        }
        if ($accountType === '') {
            $errors[] = 'Account type is required.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/banking/accounts/create');
        }

        $data = [
            'bank_name'     => $bankName,
            'account_name'  => $accountName,
            'last_four'     => $lastFour !== '' ? $lastFour : null,
            'gl_account_id' => $glAccountId,
            'account_type'  => $accountType,
            'currency_code' => $currencyCode !== '' ? $currencyCode : 'USD',
        ];

        $this->bankAccountModel->create($data);

        $this->flash('success', 'Bank account created successfully.');
        return $this->redirect('/banking/accounts');
    }

    /**
     * Display a single bank account with recent transactions.
     */
    public function show(string $id): Response
    {
        $account = $this->bankAccountModel->find((int) $id);

        if ($account === null) {
            $this->flash('error', 'Bank account not found.');
            return $this->redirect('/banking/accounts');
        }

        $glAccount = $this->accountModel->find((int) $account['gl_account_id']);
        $transactions = $this->bankTransactionModel->getRecentByBankAccount((int) $id, 50);

        return $this->render('banking/accounts/show', [
            'pageTitle'    => $account['bank_name'] . ' - ' . $account['account_name'],
            'account'      => $account,
            'glAccount'    => $glAccount,
            'transactions' => $transactions,
        ]);
    }
}
