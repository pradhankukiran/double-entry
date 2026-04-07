<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Models\Contact;
use DoubleE\Models\Invoice;
use DoubleE\Models\InvoiceLine;
use DoubleE\Models\PaymentAllocation;
use DoubleE\Services\InvoiceService;
use DoubleE\Core\Auth;

class InvoiceController extends BaseController
{
    private Invoice $invoiceModel;
    private InvoiceLine $lineModel;
    private Contact $contactModel;
    private Account $accountModel;
    private PaymentAllocation $allocationModel;
    private InvoiceService $invoiceService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->invoiceModel = new Invoice();
        $this->lineModel = new InvoiceLine();
        $this->contactModel = new Contact();
        $this->accountModel = new Account();
        $this->allocationModel = new PaymentAllocation();
        $this->invoiceService = new InvoiceService();
    }

    /**
     * List invoices with optional filters.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('invoices.view');
        $canCreate = Auth::getInstance()->hasPermission('invoices.create');
        $canEdit = Auth::getInstance()->hasPermission('invoices.edit');
        $canPost = Auth::getInstance()->hasPermission('journal.post');
        $canVoid = Auth::getInstance()->hasPermission('journal.void');

        $docType   = trim((string) $this->request->get('document_type', ''));
        $status    = trim((string) $this->request->get('status', ''));
        $contactId = trim((string) $this->request->get('contact_id', ''));

        $filters = [];
        if ($docType !== '') {
            $filters['document_type'] = $docType;
        }
        if ($status !== '') {
            $filters['status'] = $status;
        }
        if ($contactId !== '') {
            $filters['contact_id'] = $contactId;
        }

        $invoices = $this->invoiceModel->getAll($filters);
        $contacts = $this->contactModel->getAll(true);

        return $this->render('invoices/index', [
            'pageTitle' => 'Invoices',
            'invoices'  => $invoices,
            'contacts'  => $contacts,
            'filters'   => $filters,
            'canCreate' => $canCreate,
            'canEdit'   => $canEdit,
            'canPost'   => $canPost,
            'canVoid'   => $canVoid,
        ]);
    }

    /**
     * Show the create invoice/bill form.
     */
    public function create(): Response
    {
        Auth::getInstance()->requirePermission('invoices.create');

        $docType = trim((string) $this->request->get('type', 'invoice'));

        if (!in_array($docType, ['invoice', 'bill'], true)) {
            $docType = 'invoice';
        }

        $contacts = $docType === 'invoice'
            ? $this->contactModel->getCustomers()
            : $this->contactModel->getVendors();

        $accounts = $this->accountModel->getLeafAccounts();

        return $this->render('invoices/create', [
            'pageTitle'    => $docType === 'invoice' ? 'New Invoice' : 'New Bill',
            'documentType' => $docType,
            'contacts'     => $contacts,
            'accounts'     => $accounts,
        ]);
    }

    /**
     * Store a new invoice or bill.
     */
    public function store(): Response
    {
        Auth::getInstance()->requirePermission('invoices.create');
        $this->validateCsrf();

        $docType      = trim((string) $this->request->post('document_type', 'invoice'));
        $contactId    = (int) $this->request->post('contact_id', 0);
        $issueDate    = trim((string) $this->request->post('issue_date', ''));
        $dueDate      = trim((string) $this->request->post('due_date', ''));
        $reference    = trim((string) $this->request->post('reference', ''));
        $arApAccount  = (int) $this->request->post('ar_ap_account_id', 0);
        $notes        = trim((string) $this->request->post('notes', ''));
        $terms        = trim((string) $this->request->post('terms', ''));
        $action       = trim((string) $this->request->post('action', 'draft'));

        // Collect line items
        $lineDescriptions = $this->request->post('line_description', []);
        $lineAccounts     = $this->request->post('line_account', []);
        $lineQuantities   = $this->request->post('line_quantity', []);
        $lineUnitPrices   = $this->request->post('line_unit_price', []);
        $lineTaxAmounts   = $this->request->post('line_tax', []);

        // Validation
        $errors = [];

        if (!in_array($docType, ['invoice', 'bill'], true)) {
            $errors[] = 'Invalid document type.';
        }
        if ($contactId === 0) {
            $errors[] = 'Contact is required.';
        }
        if ($issueDate === '') {
            $errors[] = 'Issue date is required.';
        }
        if ($dueDate === '') {
            $errors[] = 'Due date is required.';
        }
        if ($arApAccount === 0) {
            $errors[] = 'AR/AP account is required.';
        }

        // Build lines
        $lines = [];
        $subtotal = '0.00';
        $taxTotal = '0.00';

        if (is_array($lineDescriptions)) {
            for ($i = 0, $count = count($lineDescriptions); $i < $count; $i++) {
                $desc      = trim((string) ($lineDescriptions[$i] ?? ''));
                $acctId    = (int) ($lineAccounts[$i] ?? 0);
                $qty       = round((float) ($lineQuantities[$i] ?? 1), 4);
                $unitPrice = round((float) ($lineUnitPrices[$i] ?? 0), 4);
                $tax       = round((float) ($lineTaxAmounts[$i] ?? 0), 2);

                if ($desc === '' && $acctId === 0 && $unitPrice == 0) {
                    continue; // Skip empty lines
                }

                if ($desc === '') {
                    $errors[] = 'Line ' . ($i + 1) . ': Description is required.';
                }
                if ($acctId === 0) {
                    $errors[] = 'Line ' . ($i + 1) . ': Account is required.';
                }

                $lineTotal = round($qty * $unitPrice, 2);

                $lines[] = [
                    'description' => $desc,
                    'account_id'  => $acctId,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'tax_amount'  => $tax,
                    'line_total'  => $lineTotal,
                    'line_order'  => $i,
                ];

                $subtotal = bcadd($subtotal, (string) $lineTotal, 2);
                $taxTotal = bcadd($taxTotal, (string) $tax, 2);
            }
        }

        if (count($lines) === 0) {
            $errors[] = 'At least one line item is required.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/invoices/create?type=' . $docType);
        }

        $total = bcadd($subtotal, $taxTotal, 2);

        $headerData = [
            'document_type'   => $docType,
            'contact_id'      => $contactId,
            'issue_date'      => $issueDate,
            'due_date'        => $dueDate,
            'subtotal'        => $subtotal,
            'tax_amount'      => $taxTotal,
            'total'           => $total,
            'balance_due'     => $total,
            'ar_ap_account_id' => $arApAccount,
            'reference'       => $reference !== '' ? $reference : null,
            'notes'           => $notes !== '' ? $notes : null,
            'terms'           => $terms !== '' ? $terms : null,
        ];

        $autoPost = ($action === 'post');

        try {
            $invoiceId = $this->invoiceService->create($headerData, $lines, $autoPost);

            if ($autoPost) {
                $this->flash('success', ucfirst($docType) . ' created and posted successfully.');
            } else {
                $this->flash('success', ucfirst($docType) . ' saved as draft.');
            }

            return $this->redirect('/invoices/' . $invoiceId);
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            return $this->redirect('/invoices/create?type=' . $docType);
        }
    }

    /**
     * Display a single invoice with lines and payment history.
     */
    public function show(string $id): Response
    {
        Auth::getInstance()->requirePermission('invoices.view');
        $canCreate = Auth::getInstance()->hasPermission('invoices.create');
        $canEdit = Auth::getInstance()->hasPermission('invoices.edit');
        $canPost = Auth::getInstance()->hasPermission('journal.post');
        $canVoid = Auth::getInstance()->hasPermission('journal.void');

        $invoice = $this->invoiceModel->getWithLines((int) $id);

        if ($invoice === null) {
            $this->flash('error', 'Invoice not found.');
            return $this->redirect('/invoices');
        }

        // Get contact details
        $contact = $this->contactModel->find((int) $invoice['contact_id']);

        // Get payment allocations for this invoice
        $allocations = $this->allocationModel->getByInvoice((int) $id);

        return $this->render('invoices/show', [
            'pageTitle'   => ($invoice['document_type'] === 'bill' ? 'Bill' : 'Invoice') . ' ' . $invoice['document_number'],
            'invoice'     => $invoice,
            'contact'     => $contact,
            'allocations' => $allocations,
            'canCreate'   => $canCreate,
            'canEdit'     => $canEdit,
            'canPost'     => $canPost,
            'canVoid'     => $canVoid,
        ]);
    }

    /**
     * Post a draft invoice.
     */
    public function post(string $id): Response
    {
        Auth::getInstance()->requirePermission('journal.post');
        $this->validateCsrf();

        try {
            $this->invoiceService->post((int) $id);
            $this->flash('success', 'Invoice posted successfully.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect('/invoices/' . $id);
    }

    /**
     * Void a posted invoice.
     */
    public function void(string $id): Response
    {
        Auth::getInstance()->requirePermission('journal.void');
        $this->validateCsrf();

        try {
            $this->invoiceService->void((int) $id);
            $this->flash('success', 'Invoice voided successfully.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        return $this->redirect('/invoices/' . $id);
    }
}
