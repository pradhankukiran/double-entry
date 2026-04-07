<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Account;
use DoubleE\Models\Contact;
use DoubleE\Models\Invoice;
use DoubleE\Models\Payment;
use DoubleE\Services\PaymentService;

class PaymentController extends BaseController
{
    private Payment $paymentModel;
    private Contact $contactModel;
    private Account $accountModel;
    private Invoice $invoiceModel;
    private PaymentService $paymentService;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->paymentModel = new Payment();
        $this->contactModel = new Contact();
        $this->accountModel = new Account();
        $this->invoiceModel = new Invoice();
        $this->paymentService = new PaymentService();
    }

    /**
     * List payments with optional filters.
     */
    public function index(): Response
    {
        $type      = trim((string) $this->request->get('type', ''));
        $status    = trim((string) $this->request->get('status', ''));
        $contactId = trim((string) $this->request->get('contact_id', ''));

        $filters = [];
        if ($type !== '') {
            $filters['type'] = $type;
        }
        if ($status !== '') {
            $filters['status'] = $status;
        }
        if ($contactId !== '') {
            $filters['contact_id'] = $contactId;
        }

        $payments = $this->paymentModel->getAll($filters);

        return $this->render('payments/index', [
            'pageTitle' => 'Payments',
            'payments'  => $payments,
            'filters'   => $filters,
        ]);
    }

    /**
     * Show the create payment form.
     */
    public function create(): Response
    {
        $paymentType = trim((string) $this->request->get('type', 'received'));

        if (!in_array($paymentType, ['received', 'made'], true)) {
            $paymentType = 'received';
        }

        $contacts = $paymentType === 'received'
            ? $this->contactModel->getCustomers()
            : $this->contactModel->getVendors();

        // Get bank/cash accounts (Asset type accounts for deposits)
        $bankAccounts = $this->accountModel->getLeafAccounts();

        return $this->render('payments/create', [
            'pageTitle'    => $paymentType === 'received' ? 'Receive Payment' : 'Make Payment',
            'paymentType'  => $paymentType,
            'contacts'     => $contacts,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    /**
     * Store a new payment.
     */
    public function store(): Response
    {
        $this->validateCsrf();

        $paymentType    = trim((string) $this->request->post('type', 'received'));
        $contactId      = (int) $this->request->post('contact_id', 0);
        $paymentDate    = trim((string) $this->request->post('payment_date', ''));
        $amount         = trim((string) $this->request->post('amount', '0'));
        $paymentMethod  = trim((string) $this->request->post('payment_method', ''));
        $reference      = trim((string) $this->request->post('reference', ''));
        $depositAccount = (int) $this->request->post('deposit_account_id', 0);
        $notes          = trim((string) $this->request->post('notes', ''));

        // Collect allocations
        $allocInvoiceIds = $this->request->post('alloc_invoice_id', []);
        $allocAmounts    = $this->request->post('alloc_amount', []);

        // Validation
        $errors = [];

        if (!in_array($paymentType, ['received', 'made'], true)) {
            $errors[] = 'Invalid payment type.';
        }
        if ($contactId === 0) {
            $errors[] = 'Contact is required.';
        }
        if ($paymentDate === '') {
            $errors[] = 'Payment date is required.';
        }
        if ((float) $amount <= 0) {
            $errors[] = 'Payment amount must be greater than zero.';
        }
        if ($paymentMethod === '') {
            $errors[] = 'Payment method is required.';
        }
        if ($depositAccount === 0) {
            $errors[] = 'Deposit/bank account is required.';
        }

        // Build allocations
        $allocations = [];
        if (is_array($allocInvoiceIds)) {
            for ($i = 0, $count = count($allocInvoiceIds); $i < $count; $i++) {
                $invId     = (int) ($allocInvoiceIds[$i] ?? 0);
                $allocAmt  = round((float) ($allocAmounts[$i] ?? 0), 2);

                if ($invId === 0 || $allocAmt <= 0) {
                    continue;
                }

                $allocations[] = [
                    'invoice_id' => $invId,
                    'amount'     => $allocAmt,
                ];
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/payments/create?type=' . $paymentType);
        }

        $paymentData = [
            'type'               => $paymentType,
            'contact_id'         => $contactId,
            'payment_date'       => $paymentDate,
            'amount'             => $amount,
            'payment_method'     => $paymentMethod,
            'reference'          => $reference !== '' ? $reference : null,
            'deposit_account_id' => $depositAccount,
            'notes'              => $notes !== '' ? $notes : null,
        ];

        try {
            $paymentId = $this->paymentService->create($paymentData, $allocations);
            $this->flash('success', 'Payment recorded successfully.');
            return $this->redirect('/payments/' . $paymentId);
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
            return $this->redirect('/payments/create?type=' . $paymentType);
        }
    }

    /**
     * Display a single payment with allocations.
     */
    public function show(string $id): Response
    {
        $payment = $this->paymentModel->getWithAllocations((int) $id);

        if ($payment === null) {
            $this->flash('error', 'Payment not found.');
            return $this->redirect('/payments');
        }

        $contact = $this->contactModel->find((int) $payment['contact_id']);

        return $this->render('payments/show', [
            'pageTitle' => 'Payment ' . $payment['payment_number'],
            'payment'   => $payment,
            'contact'   => $contact,
        ]);
    }
}
