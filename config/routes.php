<?php

/**
 * Route definitions.
 *
 * Each route: ['method' => ..., 'path' => ..., 'controller' => ..., 'action' => ..., 'middleware' => [...]]
 */
return [
    // Home
    ['method' => 'GET', 'path' => '/', 'controller' => 'DashboardController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],

    // Auth
    ['method' => 'GET', 'path' => '/login', 'controller' => 'AuthController', 'action' => 'showLogin'],
    ['method' => 'POST', 'path' => '/login', 'controller' => 'AuthController', 'action' => 'login'],
    ['method' => 'POST', 'path' => '/logout', 'controller' => 'AuthController', 'action' => 'logout'],

    // Users
    ['method' => 'GET', 'path' => '/users', 'controller' => 'UserController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/profile', 'controller' => 'UserController', 'action' => 'profile', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/profile', 'controller' => 'UserController', 'action' => 'updateProfile', 'middleware' => ['AuthMiddleware']],

    // Chart of Accounts
    ['method' => 'GET', 'path' => '/accounts', 'controller' => 'AccountController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/accounts/create', 'controller' => 'AccountController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/accounts', 'controller' => 'AccountController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/accounts/{id}', 'controller' => 'AccountController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/accounts/{id}/edit', 'controller' => 'AccountController', 'action' => 'edit', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/accounts/{id}', 'controller' => 'AccountController', 'action' => 'update', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/accounts/{id}/toggle', 'controller' => 'AccountController', 'action' => 'toggleActive', 'middleware' => ['AuthMiddleware']],

    // Fiscal Years
    ['method' => 'GET', 'path' => '/fiscal-years', 'controller' => 'FiscalYearController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/fiscal-years/create', 'controller' => 'FiscalYearController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/fiscal-years/{id}/close', 'controller' => 'FiscalCloseController', 'action' => 'preview', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/fiscal-years/{id}/close', 'controller' => 'FiscalCloseController', 'action' => 'execute', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/fiscal-years', 'controller' => 'FiscalYearController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],

    // Journal Entries
    ['method' => 'GET', 'path' => '/journal-entries', 'controller' => 'JournalController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/journal-entries/create', 'controller' => 'JournalController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/journal-entries', 'controller' => 'JournalController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/journal-entries/{id}', 'controller' => 'JournalController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/journal-entries/{id}/post', 'controller' => 'JournalController', 'action' => 'post', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/journal-entries/{id}/void', 'controller' => 'JournalController', 'action' => 'void', 'middleware' => ['AuthMiddleware']],

    // General Ledger
    ['method' => 'GET', 'path' => '/ledger', 'controller' => 'LedgerController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/ledger/account/{id}', 'controller' => 'LedgerController', 'action' => 'account', 'middleware' => ['AuthMiddleware']],

    // Reports
    ['method' => 'GET', 'path' => '/reports', 'controller' => 'ReportController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reports/trial-balance', 'controller' => 'ReportController', 'action' => 'trialBalance', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reports/balance-sheet', 'controller' => 'ReportController', 'action' => 'balanceSheet', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reports/income-statement', 'controller' => 'ReportController', 'action' => 'incomeStatement', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reports/cash-flow', 'controller' => 'ReportController', 'action' => 'cashFlow', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reports/aging', 'controller' => 'ReportController', 'action' => 'aging', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reports/{report}/pdf', 'controller' => 'ReportController', 'action' => 'exportPdf', 'middleware' => ['AuthMiddleware']],

    // Contacts
    ['method' => 'GET', 'path' => '/contacts', 'controller' => 'ContactController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/contacts/create', 'controller' => 'ContactController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/contacts', 'controller' => 'ContactController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/contacts/{id}', 'controller' => 'ContactController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/contacts/{id}/edit', 'controller' => 'ContactController', 'action' => 'edit', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/contacts/{id}', 'controller' => 'ContactController', 'action' => 'update', 'middleware' => ['AuthMiddleware']],

    // Invoices & Bills
    ['method' => 'GET', 'path' => '/invoices', 'controller' => 'InvoiceController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/invoices/create', 'controller' => 'InvoiceController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/invoices', 'controller' => 'InvoiceController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/invoices/{id}/pdf', 'controller' => 'InvoiceController', 'action' => 'pdf', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/invoices/{id}', 'controller' => 'InvoiceController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/invoices/{id}/post', 'controller' => 'InvoiceController', 'action' => 'post', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/invoices/{id}/void', 'controller' => 'InvoiceController', 'action' => 'void', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/bills', 'controller' => 'InvoiceController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],

    // Payments
    ['method' => 'GET', 'path' => '/payments', 'controller' => 'PaymentController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/payments/create', 'controller' => 'PaymentController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/payments', 'controller' => 'PaymentController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/payments/{id}', 'controller' => 'PaymentController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],

    // Bank Accounts
    ['method' => 'GET', 'path' => '/bank-accounts', 'controller' => 'BankAccountController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/bank-accounts/create', 'controller' => 'BankAccountController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/bank-accounts', 'controller' => 'BankAccountController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/bank-accounts/{id}', 'controller' => 'BankAccountController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],

    // Bank Import
    ['method' => 'GET', 'path' => '/bank-accounts/{id}/import', 'controller' => 'BankImportController', 'action' => 'upload', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/bank-import/preview', 'controller' => 'BankImportController', 'action' => 'preview', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/bank-import/import', 'controller' => 'BankImportController', 'action' => 'import', 'middleware' => ['AuthMiddleware']],

    // Reconciliation
    ['method' => 'GET', 'path' => '/reconciliation', 'controller' => 'ReconciliationController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reconciliation/{id}/start', 'controller' => 'ReconciliationController', 'action' => 'start', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/reconciliation', 'controller' => 'ReconciliationController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/reconciliation/{id}', 'controller' => 'ReconciliationController', 'action' => 'reconcile', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/reconciliation/toggle', 'controller' => 'ReconciliationController', 'action' => 'toggle', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/reconciliation/{id}/complete', 'controller' => 'ReconciliationController', 'action' => 'complete', 'middleware' => ['AuthMiddleware']],

    // Tax Management
    ['method' => 'GET', 'path' => '/tax', 'controller' => 'TaxController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/tax/rates/create', 'controller' => 'TaxController', 'action' => 'createRate', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/tax/rates', 'controller' => 'TaxController', 'action' => 'storeRate', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/tax/groups/create', 'controller' => 'TaxController', 'action' => 'createGroup', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/tax/groups', 'controller' => 'TaxController', 'action' => 'storeGroup', 'middleware' => ['AuthMiddleware']],

    // Recurring Transactions
    ['method' => 'GET', 'path' => '/recurring', 'controller' => 'RecurringController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/recurring/create', 'controller' => 'RecurringController', 'action' => 'create', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/recurring', 'controller' => 'RecurringController', 'action' => 'store', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/recurring/{id}', 'controller' => 'RecurringController', 'action' => 'show', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/recurring/{id}/run', 'controller' => 'RecurringController', 'action' => 'run', 'middleware' => ['AuthMiddleware']],

    // Audit Trail
    ['method' => 'GET', 'path' => '/audit', 'controller' => 'AuditController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],

    // Settings
    ['method' => 'GET', 'path' => '/settings', 'controller' => 'SettingsController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'POST', 'path' => '/settings', 'controller' => 'SettingsController', 'action' => 'update', 'middleware' => ['AuthMiddleware']],

    // Search
    ['method' => 'GET', 'path' => '/search', 'controller' => 'SearchController', 'action' => 'index', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/search/autocomplete', 'controller' => 'SearchController', 'action' => 'autocomplete', 'middleware' => ['AuthMiddleware']],

    // CSV Export
    ['method' => 'GET', 'path' => '/export/accounts', 'controller' => 'ExportController', 'action' => 'accounts', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/export/journal-entries', 'controller' => 'ExportController', 'action' => 'journalEntries', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/export/contacts', 'controller' => 'ExportController', 'action' => 'contacts', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/export/invoices', 'controller' => 'ExportController', 'action' => 'invoices', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/export/ledger', 'controller' => 'ExportController', 'action' => 'ledger', 'middleware' => ['AuthMiddleware']],
    ['method' => 'GET', 'path' => '/export/trial-balance', 'controller' => 'ExportController', 'action' => 'trialBalance', 'middleware' => ['AuthMiddleware']],
];
