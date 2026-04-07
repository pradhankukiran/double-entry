<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Models\AccountType;
use DoubleE\Models\AccountSubType;
use DoubleE\Services\ChartOfAccountsService;
use DoubleE\Core\Auth;

class AccountController extends BaseController
{
    private Account $accountModel;
    private AccountType $accountTypeModel;
    private AccountSubType $accountSubTypeModel;
    private ChartOfAccountsService $coaService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->accountModel = new Account();
        $this->accountTypeModel = new AccountType();
        $this->accountSubTypeModel = new AccountSubType();
        $this->coaService = new ChartOfAccountsService();
    }

    /**
     * Display the chart of accounts hierarchy.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('accounts.view');
        $canCreate = Auth::getInstance()->hasPermission('accounts.create');
        $canEdit = Auth::getInstance()->hasPermission('accounts.edit');

        $tree = $this->coaService->getHierarchyTree();
        $accountTypes = $this->accountTypeModel->getAllOrdered();

        return $this->render('accounts/index', [
            'pageTitle'    => 'Chart of Accounts',
            'tree'         => $tree,
            'accountTypes' => $accountTypes,
            'canCreate'    => $canCreate,
            'canEdit'      => $canEdit,
            'breadcrumbs'  => [['label' => 'Dashboard', 'url' => '/'], ['label' => 'Chart of Accounts']],
        ]);
    }

    /**
     * Show the form for creating a new account.
     */
    public function create(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');

        $types = $this->accountTypeModel->getAllOrdered();
        $subtypes = $this->accountSubTypeModel->findAll([], 'account_type_id, name');
        $parentOptions = $this->coaService->buildAccountDropdown();

        return $this->render('accounts/create', [
            'pageTitle'     => 'New Account',
            'types'         => $types,
            'subtypes'      => $subtypes,
            'parentOptions' => $parentOptions,
            'breadcrumbs'   => [['label' => 'Dashboard', 'url' => '/'], ['label' => 'Chart of Accounts', 'url' => '/accounts'], ['label' => 'New Account']],
        ]);
    }

    /**
     * Store a newly created account.
     */
    public function store(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');
        $this->validateCsrf();

        $accountNumber = trim((string) $this->request->post('account_number', ''));
        $name = trim((string) $this->request->post('name', ''));
        $description = trim((string) $this->request->post('description', ''));
        $accountTypeId = (int) $this->request->post('account_type_id', 0);
        $accountSubtypeId = $this->request->post('account_subtype_id', '');
        $parentId = $this->request->post('parent_id', '');
        $currencyCode = trim((string) $this->request->post('currency_code', 'USD'));
        $openingBalance = (string) $this->request->post('opening_balance', '0.00');
        $isHeader = $this->request->post('is_header') ? 1 : 0;

        // Validation
        $errors = [];

        if ($accountNumber === '') {
            $errors[] = 'Account number is required.';
        } elseif (!$this->coaService->validateAccountNumber($accountNumber)) {
            $errors[] = 'This account number is already in use.';
        }

        if ($name === '') {
            $errors[] = 'Account name is required.';
        }

        if ($accountTypeId === 0) {
            $errors[] = 'Account type is required.';
        }

        if ($currencyCode === '') {
            $currencyCode = 'USD';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/accounts/create');
        }

        $data = [
            'account_number'    => $accountNumber,
            'name'              => $name,
            'description'       => $description !== '' ? $description : null,
            'account_type_id'   => $accountTypeId,
            'account_subtype_id'=> $accountSubtypeId !== '' ? (int) $accountSubtypeId : null,
            'parent_id'         => $parentId !== '' ? (int) $parentId : null,
            'currency_code'     => $currencyCode,
            'opening_balance'   => $openingBalance,
            'is_header'         => $isHeader,
        ];

        $this->accountModel->create($data);

        $this->flash('success', 'Account created successfully.');
        return $this->redirect('/accounts');
    }

    /**
     * Display a single account's details.
     */
    public function show(string $id): Response
    {
        Auth::getInstance()->requirePermission('accounts.view');
        $canCreate = Auth::getInstance()->hasPermission('accounts.create');
        $canEdit = Auth::getInstance()->hasPermission('accounts.edit');

        $account = $this->accountModel->find((int) $id);

        if ($account === null) {
            $this->flash('error', 'Account not found.');
            return $this->redirect('/accounts');
        }

        // Get type and subtype names
        $type = $this->accountTypeModel->find((int) $account['account_type_id']);
        $subtype = !empty($account['account_subtype_id'])
            ? $this->accountSubTypeModel->find((int) $account['account_subtype_id'])
            : null;

        // Get parent account if exists
        $parent = !empty($account['parent_id'])
            ? $this->accountModel->find((int) $account['parent_id'])
            : null;

        // Get direct children
        $children = $this->accountModel->getChildren((int) $account['id']);

        return $this->render('accounts/show', [
            'pageTitle'   => $account['account_number'] . ' - ' . $account['name'],
            'account'     => $account,
            'type'        => $type,
            'subtype'     => $subtype,
            'parent'      => $parent,
            'children'    => $children,
            'canCreate'   => $canCreate,
            'canEdit'     => $canEdit,
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => '/'], ['label' => 'Chart of Accounts', 'url' => '/accounts'], ['label' => $account['name']]],
        ]);
    }

    /**
     * Show the form for editing an existing account.
     */
    public function edit(string $id): Response
    {
        Auth::getInstance()->requirePermission('accounts.edit');

        $account = $this->accountModel->find((int) $id);

        if ($account === null) {
            $this->flash('error', 'Account not found.');
            return $this->redirect('/accounts');
        }

        $types = $this->accountTypeModel->getAllOrdered();
        $subtypes = $this->accountSubTypeModel->findAll([], 'account_type_id, name');
        $parentOptions = $this->coaService->buildAccountDropdown();

        return $this->render('accounts/edit', [
            'pageTitle'     => 'Edit Account',
            'account'       => $account,
            'types'         => $types,
            'subtypes'      => $subtypes,
            'parentOptions' => $parentOptions,
            'breadcrumbs'   => [['label' => 'Dashboard', 'url' => '/'], ['label' => 'Chart of Accounts', 'url' => '/accounts'], ['label' => $account['name'], 'url' => '/accounts/' . $id], ['label' => 'Edit']],
        ]);
    }

    /**
     * Update an existing account.
     */
    public function update(string $id): Response
    {
        Auth::getInstance()->requirePermission('accounts.edit');
        $this->validateCsrf();

        $accountId = (int) $id;
        $account = $this->accountModel->find($accountId);

        if ($account === null) {
            $this->flash('error', 'Account not found.');
            return $this->redirect('/accounts');
        }

        $name = trim((string) $this->request->post('name', ''));
        $description = trim((string) $this->request->post('description', ''));
        $accountTypeId = (int) $this->request->post('account_type_id', 0);
        $accountSubtypeId = $this->request->post('account_subtype_id', '');
        $parentId = $this->request->post('parent_id', '');
        $currencyCode = trim((string) $this->request->post('currency_code', 'USD'));
        $openingBalance = (string) $this->request->post('opening_balance', '0.00');
        $isHeader = $this->request->post('is_header') ? 1 : 0;

        // Validation
        $errors = [];

        if ($name === '') {
            $errors[] = 'Account name is required.';
        }

        if ($accountTypeId === 0) {
            $errors[] = 'Account type is required.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/accounts/' . $accountId . '/edit');
        }

        $data = [
            'name'              => $name,
            'description'       => $description !== '' ? $description : null,
            'account_type_id'   => $accountTypeId,
            'account_subtype_id'=> $accountSubtypeId !== '' ? (int) $accountSubtypeId : null,
            'parent_id'         => $parentId !== '' ? (int) $parentId : null,
            'currency_code'     => $currencyCode,
            'opening_balance'   => $openingBalance,
            'is_header'         => $isHeader,
        ];

        $this->accountModel->update($accountId, $data);

        $this->flash('success', 'Account updated successfully.');
        return $this->redirect('/accounts');
    }

    /**
     * Toggle an account's active status.
     */
    public function toggleActive(string $id): Response
    {
        Auth::getInstance()->requirePermission('accounts.edit');
        $this->validateCsrf();

        $accountId = (int) $id;
        $account = $this->accountModel->find($accountId);

        if ($account === null) {
            $this->flash('error', 'Account not found.');
            return $this->redirect('/accounts');
        }

        // Only check canDeactivate when deactivating (currently active)
        if (!empty($account['is_active']) && !$this->coaService->canDeactivate($accountId)) {
            $this->flash('error', 'This account cannot be deactivated. It may have child accounts, transactions, or be a system account.');
            return $this->redirect('/accounts/' . $accountId);
        }

        $this->accountModel->toggleActive($accountId);

        $newStatus = !empty($account['is_active']) ? 'deactivated' : 'activated';
        $this->flash('success', 'Account ' . $newStatus . ' successfully.');

        return $this->redirect('/accounts/' . $accountId);
    }
}
