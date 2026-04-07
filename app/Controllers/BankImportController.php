<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\BankAccount;
use DoubleE\Services\BankImportService;
use DoubleE\Core\Auth;

class BankImportController extends BaseController
{
    private BankAccount $bankAccountModel;
    private BankImportService $importService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->bankAccountModel = new BankAccount();
        $this->importService = new BankImportService();
    }

    /**
     * Show the CSV upload form for a bank account.
     */
    public function upload(string $id): Response
    {
        Auth::getInstance()->requirePermission('banking.create');

        $account = $this->bankAccountModel->find((int) $id);

        if ($account === null) {
            $this->flash('error', 'Bank account not found.');
            return $this->redirect('/banking/accounts');
        }

        return $this->render('banking/import/upload', [
            'pageTitle' => 'Import Transactions - ' . $account['account_name'],
            'account'   => $account,
        ]);
    }

    /**
     * Handle CSV upload and show column mapping preview.
     */
    public function preview(): Response
    {
        Auth::getInstance()->requirePermission('banking.create');
        $this->validateCsrf();

        $bankAccountId = (int) $this->request->post('bank_account_id', 0);
        $account = $this->bankAccountModel->find($bankAccountId);

        if ($account === null) {
            $this->flash('error', 'Bank account not found.');
            return $this->redirect('/banking/accounts');
        }

        $file = $_FILES['csv_file'] ?? null;

        if ($file === null || $file['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Please select a valid CSV file to upload.');
            return $this->redirect('/banking/accounts/' . $bankAccountId . '/import');
        }

        $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($extension !== 'csv' && !in_array($file['type'], $allowedMimes, true)) {
            $this->flash('error', 'Only CSV files are accepted.');
            return $this->redirect('/banking/accounts/' . $bankAccountId . '/import');
        }

        // Move uploaded file to temp storage
        $tempPath = rtrim(sys_get_temp_dir(), '/') . '/dee_import_' . uniqid() . '.csv';
        move_uploaded_file($file['tmp_name'], $tempPath);

        // Read first rows for preview
        $previewRows = [];
        $headers = [];
        $handle = fopen($tempPath, 'r');
        if ($handle !== false) {
            $headers = fgetcsv($handle) ?: [];
            $rowCount = 0;
            while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
                $previewRows[] = $row;
                $rowCount++;
            }
            fclose($handle);
        }

        // Get column suggestions from service
        $suggestions = $this->importService->getColumnSuggestions($headers);

        return $this->render('banking/import/preview', [
            'pageTitle'   => 'Map Columns - ' . $account['account_name'],
            'account'     => $account,
            'headers'     => $headers,
            'previewRows' => $previewRows,
            'suggestions' => $suggestions,
            'tempPath'    => $tempPath,
        ]);
    }

    /**
     * Execute the CSV import with the selected column mapping.
     */
    public function import(): Response
    {
        Auth::getInstance()->requirePermission('banking.create');
        $this->validateCsrf();

        $bankAccountId = (int) $this->request->post('bank_account_id', 0);
        $account = $this->bankAccountModel->find($bankAccountId);

        if ($account === null) {
            $this->flash('error', 'Bank account not found.');
            return $this->redirect('/banking/accounts');
        }

        $tempPath   = (string) $this->request->post('temp_path', '');
        $dateCol    = (int) $this->request->post('date_col', 0);
        $descCol    = (int) $this->request->post('description_col', 0);
        $amountMode = (string) $this->request->post('amount_mode', 'single');
        $amountCol  = (int) $this->request->post('amount_col', 0);
        $debitCol   = (int) $this->request->post('debit_col', 0);
        $creditCol  = (int) $this->request->post('credit_col', 0);
        $refCol     = $this->request->post('reference_col', '');

        if (!file_exists($tempPath)) {
            $this->flash('error', 'Upload session expired. Please upload the file again.');
            return $this->redirect('/banking/accounts/' . $bankAccountId . '/import');
        }

        $mapping = [
            'date_col'        => $dateCol,
            'description_col' => $descCol,
            'amount_mode'     => $amountMode,
            'amount_col'      => $amountCol,
            'debit_col'       => $debitCol,
            'credit_col'      => $creditCol,
            'reference_col'   => $refCol !== '' ? (int) $refCol : null,
        ];

        $result = $this->importService->importCsv($bankAccountId, $tempPath, $mapping);

        // Clean up temp file
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        return $this->render('banking/import/results', [
            'pageTitle'    => 'Import Results - ' . $account['account_name'],
            'account'      => $account,
            'imported'     => $result['imported'] ?? 0,
            'duplicates'   => $result['duplicates'] ?? 0,
            'transactions' => $result['transactions'] ?? [],
        ]);
    }
}
