<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Setting;
use DoubleE\Core\Auth;

class SettingsController extends BaseController
{
    private Setting $settingModel;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->settingModel = new Setting();
    }

    /**
     * Show settings form with current values.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('settings.view');
        $canEdit = Auth::getInstance()->hasPermission('settings.edit');

        $settings = $this->settingModel->getAll();

        return $this->render('settings/index', [
            'pageTitle' => 'Settings',
            'settings'  => $settings,
            'canEdit'   => $canEdit,
        ]);
    }

    /**
     * Validate and save settings.
     */
    public function update(): Response
    {
        Auth::getInstance()->requirePermission('settings.edit');
        $this->validateCsrf();

        $fields = [
            // Company Information
            'company_name'       => trim((string) $this->request->post('company_name', '')),
            'legal_name'         => trim((string) $this->request->post('legal_name', '')),
            'tax_id'             => trim((string) $this->request->post('tax_id', '')),
            'address'            => trim((string) $this->request->post('address', '')),
            'phone'              => trim((string) $this->request->post('phone', '')),
            'email'              => trim((string) $this->request->post('email', '')),
            'website'            => trim((string) $this->request->post('website', '')),
            // Accounting Preferences
            'default_currency'   => trim((string) $this->request->post('default_currency', 'USD')),
            'fiscal_year_start'  => trim((string) $this->request->post('fiscal_year_start', '1')),
            'date_format'        => trim((string) $this->request->post('date_format', 'Y-m-d')),
            'number_format'      => trim((string) $this->request->post('number_format', '1,234.56')),
        ];

        $errors = [];

        if ($fields['company_name'] === '') {
            $errors[] = 'Company name is required.';
        }

        $validMonths = range(1, 12);
        if (!in_array((int) $fields['fiscal_year_start'], $validMonths, true)) {
            $errors[] = 'A valid fiscal year start month is required.';
        }

        if ($fields['email'] !== '' && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/settings');
        }

        foreach ($fields as $key => $value) {
            $this->settingModel->set($key, $value);
        }

        $this->flash('success', 'Settings saved successfully.');
        return $this->redirect('/settings');
    }
}
