<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\FiscalYear;
use DoubleE\Services\FiscalYearService;
use DoubleE\Core\Auth;

class FiscalYearController extends BaseController
{
    private FiscalYear $fiscalYearModel;
    private FiscalYearService $fiscalYearService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->fiscalYearModel = new FiscalYear();
        $this->fiscalYearService = new FiscalYearService();
    }

    /**
     * Display all fiscal years with their periods.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('accounts.view');

        $fiscalYears = $this->fiscalYearModel->getAllWithPeriods();

        return $this->render('fiscal-years/index', [
            'pageTitle'   => 'Fiscal Years',
            'fiscalYears' => $fiscalYears,
        ]);
    }

    /**
     * Show the form for creating a new fiscal year.
     */
    public function create(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');

        return $this->render('fiscal-years/create', [
            'pageTitle' => 'Create Fiscal Year',
        ]);
    }

    /**
     * Store a newly created fiscal year.
     */
    public function store(): Response
    {
        Auth::getInstance()->requirePermission('accounts.create');
        $this->validateCsrf();

        $name = trim((string) $this->request->post('name', ''));
        $startDate = trim((string) $this->request->post('start_date', ''));
        $endDate = trim((string) $this->request->post('end_date', ''));

        // Validation
        $errors = [];

        if ($name === '') {
            $errors[] = 'Fiscal year name is required.';
        }

        if ($startDate === '') {
            $errors[] = 'Start date is required.';
        }

        if ($endDate === '') {
            $errors[] = 'End date is required.';
        }

        // Validate date formats
        if ($startDate !== '' && !$this->isValidDate($startDate)) {
            $errors[] = 'Start date must be a valid date (YYYY-MM-DD).';
        }

        if ($endDate !== '' && !$this->isValidDate($endDate)) {
            $errors[] = 'End date must be a valid date (YYYY-MM-DD).';
        }

        // Validate date range
        if ($startDate !== '' && $endDate !== '' && $startDate >= $endDate) {
            $errors[] = 'End date must be after the start date.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/fiscal-years/create');
        }

        $this->fiscalYearService->create($name, $startDate, $endDate);

        $this->flash('success', 'Fiscal year created successfully with monthly periods.');
        return $this->redirect('/fiscal-years');
    }

    /**
     * Validate a date string in YYYY-MM-DD format.
     */
    private function isValidDate(string $date): bool
    {
        $parts = explode('-', $date);
        if (count($parts) !== 3) {
            return false;
        }

        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }
}
