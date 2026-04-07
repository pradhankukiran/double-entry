<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Models\TaxRate;
use DoubleE\Models\TaxGroup;
use DoubleE\Services\TaxService;
use DoubleE\Core\Auth;

class TaxController extends BaseController
{
    private TaxRate $taxRateModel;
    private TaxGroup $taxGroupModel;
    private Account $accountModel;
    private TaxService $taxService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->taxRateModel = new TaxRate();
        $this->taxGroupModel = new TaxGroup();
        $this->accountModel = new Account();
        $this->taxService = new TaxService();
    }

    /**
     * List all tax rates and tax groups.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('accounts.view');
        $canCreate = Auth::getInstance()->hasPermission('accounts.create');

        $rates = $this->taxRateModel->getAll();
        $groups = $this->taxGroupModel->getAll();

        return $this->render('tax/index', [
            'pageTitle' => 'Tax Management',
            'rates'     => $rates,
            'groups'    => $groups,
            'canCreate' => $canCreate,
        ]);
    }

    /**
     * Show the form for creating a new tax rate.
     */
    public function createRate(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');

        $accounts = $this->accountModel->getLeafAccounts();

        return $this->render('tax/create-rate', [
            'pageTitle' => 'New Tax Rate',
            'accounts'  => $accounts,
        ]);
    }

    /**
     * Store a new tax rate.
     */
    public function storeRate(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');
        $this->validateCsrf();

        $name      = trim((string) $this->request->post('name', ''));
        $code      = trim((string) $this->request->post('code', ''));
        $rate      = trim((string) $this->request->post('rate', ''));
        $accountId = (int) $this->request->post('tax_account_id', 0);
        $isActive  = $this->request->post('is_active') ? 1 : 0;

        $errors = [];

        if ($name === '') {
            $errors[] = 'Tax rate name is required.';
        }
        if ($code === '') {
            $errors[] = 'Tax code is required.';
        }
        if ($rate === '' || !is_numeric($rate)) {
            $errors[] = 'A valid tax rate percentage is required.';
        }
        if ($accountId === 0) {
            $errors[] = 'Tax liability account is required.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/tax/rates/create');
        }

        $data = [
            'name'           => $name,
            'code'           => $code,
            'rate'           => round((float) $rate, 4),
            'tax_account_id' => $accountId,
            'is_active'      => $isActive,
        ];

        $this->taxRateModel->create($data);

        $this->flash('success', 'Tax rate created successfully.');
        return $this->redirect('/tax');
    }

    /**
     * Show the form for creating a new tax group.
     */
    public function createGroup(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');

        $rates = $this->taxRateModel->getAll();

        return $this->render('tax/create-group', [
            'pageTitle' => 'New Tax Group',
            'rates'     => $rates,
        ]);
    }

    /**
     * Store a new tax group with rate assignments.
     */
    public function storeGroup(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');
        $this->validateCsrf();

        $name     = trim((string) $this->request->post('name', ''));
        $code     = trim((string) $this->request->post('code', ''));
        $rateIds  = $this->request->post('rate_ids', []);
        $orders   = $this->request->post('rate_orders', []);

        $errors = [];

        if ($name === '') {
            $errors[] = 'Tax group name is required.';
        }
        if ($code === '') {
            $errors[] = 'Tax group code is required.';
        }
        if (!is_array($rateIds) || count($rateIds) === 0) {
            $errors[] = 'At least one tax rate must be selected.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/tax/groups/create');
        }

        // Build rate assignments with sort order
        $assignments = [];
        foreach ($rateIds as $rateId) {
            $rateId = (int) $rateId;
            $assignments[] = [
                'tax_rate_id' => $rateId,
                'sort_order'  => (int) ($orders[$rateId] ?? 0),
            ];
        }

        $groupData = [
            'name' => $name,
            'code' => $code,
        ];

        $this->taxService->createGroupWithRates($groupData, $assignments);

        $this->flash('success', 'Tax group created successfully.');
        return $this->redirect('/tax');
    }
}
