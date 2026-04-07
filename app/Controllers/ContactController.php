<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\Contact;
use DoubleE\Models\ContactAddress;
use DoubleE\Models\Invoice;
use DoubleE\Models\Payment;
use DoubleE\Core\Auth;

class ContactController extends BaseController
{
    private Contact $contactModel;
    private ContactAddress $addressModel;
    private Invoice $invoiceModel;
    private Payment $paymentModel;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->contactModel = new Contact();
        $this->addressModel = new ContactAddress();
        $this->invoiceModel = new Invoice();
        $this->paymentModel = new Payment();
    }

    /**
     * List contacts with optional type filter.
     */
    public function index(): Response
    {
        Auth::getInstance()->requirePermission('invoices.view');
        $canCreate = Auth::getInstance()->hasPermission('invoices.create');
        $canEdit = Auth::getInstance()->hasPermission('invoices.edit');

        $type = trim((string) $this->request->get('type', ''));

        $contacts = $this->contactModel->getAll(false, $type !== '' ? $type : null);

        // Attach outstanding balance to each contact
        foreach ($contacts as &$contact) {
            $contact['outstanding'] = $this->contactModel->getOutstandingBalance((int) $contact['id']);
        }
        unset($contact);

        return $this->render('contacts/index', [
            'pageTitle' => 'Contacts',
            'contacts'   => $contacts,
            'typeFilter'  => $type,
            'canCreate'   => $canCreate,
            'canEdit'     => $canEdit,
        ]);
    }

    /**
     * Show the create contact form.
     */
    public function create(): Response
    {
        Auth::getInstance()->requirePermission('invoices.create');

        return $this->render('contacts/create', [
            'pageTitle' => 'New Contact',
        ]);
    }

    /**
     * Store a new contact.
     */
    public function store(): Response
    {
        Auth::getInstance()->requirePermission('invoices.create');
        $this->validateCsrf();

        $type        = trim((string) $this->request->post('type', 'customer'));
        $companyName = trim((string) $this->request->post('company_name', ''));
        $firstName   = trim((string) $this->request->post('first_name', ''));
        $lastName    = trim((string) $this->request->post('last_name', ''));
        $displayName = trim((string) $this->request->post('display_name', ''));
        $email       = trim((string) $this->request->post('email', ''));
        $phone       = trim((string) $this->request->post('phone', ''));
        $taxId       = trim((string) $this->request->post('tax_id', ''));
        $website     = trim((string) $this->request->post('website', ''));
        $terms       = (int) $this->request->post('payment_terms', 30);
        $creditLimit = trim((string) $this->request->post('credit_limit', ''));
        $notes       = trim((string) $this->request->post('notes', ''));

        // Validation
        $errors = [];

        if (!in_array($type, ['customer', 'vendor', 'both'], true)) {
            $errors[] = 'Invalid contact type.';
        }

        if ($displayName === '') {
            $errors[] = 'Display name is required.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/contacts/create');
        }

        $contactData = [
            'type'          => $type,
            'company_name'  => $companyName !== '' ? $companyName : null,
            'first_name'    => $firstName !== '' ? $firstName : null,
            'last_name'     => $lastName !== '' ? $lastName : null,
            'display_name'  => $displayName,
            'email'         => $email !== '' ? $email : null,
            'phone'         => $phone !== '' ? $phone : null,
            'tax_id'        => $taxId !== '' ? $taxId : null,
            'website'       => $website !== '' ? $website : null,
            'payment_terms' => $terms,
            'credit_limit'  => $creditLimit !== '' ? $creditLimit : null,
            'notes'         => $notes !== '' ? $notes : null,
        ];

        $contactId = $this->contactModel->create($contactData);

        // Save address if provided
        $line1 = trim((string) $this->request->post('address_line1', ''));
        if ($line1 !== '') {
            $addressData = [
                'contact_id'  => $contactId,
                'type'        => 'billing',
                'line1'       => $line1,
                'line2'       => trim((string) $this->request->post('address_line2', '')) ?: null,
                'city'        => trim((string) $this->request->post('address_city', '')),
                'state'       => trim((string) $this->request->post('address_state', '')) ?: null,
                'postal_code' => trim((string) $this->request->post('address_postal_code', '')) ?: null,
                'country'     => trim((string) $this->request->post('address_country', 'US')),
                'is_default'  => 1,
            ];
            $this->addressModel->create($addressData);
        }

        $this->flash('success', 'Contact created successfully.');
        return $this->redirect('/contacts/' . $contactId);
    }

    /**
     * Display a single contact with related data.
     */
    public function show(string $id): Response
    {
        Auth::getInstance()->requirePermission('invoices.view');
        $canCreate = Auth::getInstance()->hasPermission('invoices.create');
        $canEdit = Auth::getInstance()->hasPermission('invoices.edit');

        $contact = $this->contactModel->find((int) $id);

        if ($contact === null) {
            $this->flash('error', 'Contact not found.');
            return $this->redirect('/contacts');
        }

        $addresses  = $this->addressModel->getByContact((int) $id);
        $invoices   = $this->invoiceModel->getByContact((int) $id);
        $payments   = $this->paymentModel->getByContact((int) $id);
        $outstanding = $this->contactModel->getOutstandingBalance((int) $id);

        return $this->render('contacts/show', [
            'pageTitle'   => $contact['display_name'],
            'contact'     => $contact,
            'addresses'   => $addresses,
            'invoices'    => $invoices,
            'payments'    => $payments,
            'outstanding' => $outstanding,
            'canCreate'   => $canCreate,
            'canEdit'     => $canEdit,
        ]);
    }

    /**
     * Show the edit contact form.
     */
    public function edit(string $id): Response
    {
        Auth::getInstance()->requirePermission('invoices.edit');

        $contact = $this->contactModel->find((int) $id);

        if ($contact === null) {
            $this->flash('error', 'Contact not found.');
            return $this->redirect('/contacts');
        }

        $addresses = $this->addressModel->getByContact((int) $id);
        $address   = !empty($addresses) ? $addresses[0] : null;

        return $this->render('contacts/edit', [
            'pageTitle' => 'Edit Contact',
            'contact'   => $contact,
            'address'   => $address,
        ]);
    }

    /**
     * Update an existing contact.
     */
    public function update(string $id): Response
    {
        Auth::getInstance()->requirePermission('invoices.edit');
        $this->validateCsrf();

        $contactId = (int) $id;
        $contact   = $this->contactModel->find($contactId);

        if ($contact === null) {
            $this->flash('error', 'Contact not found.');
            return $this->redirect('/contacts');
        }

        $type        = trim((string) $this->request->post('type', 'customer'));
        $companyName = trim((string) $this->request->post('company_name', ''));
        $firstName   = trim((string) $this->request->post('first_name', ''));
        $lastName    = trim((string) $this->request->post('last_name', ''));
        $displayName = trim((string) $this->request->post('display_name', ''));
        $email       = trim((string) $this->request->post('email', ''));
        $phone       = trim((string) $this->request->post('phone', ''));
        $taxId       = trim((string) $this->request->post('tax_id', ''));
        $website     = trim((string) $this->request->post('website', ''));
        $terms       = (int) $this->request->post('payment_terms', 30);
        $creditLimit = trim((string) $this->request->post('credit_limit', ''));
        $notes       = trim((string) $this->request->post('notes', ''));

        // Validation
        $errors = [];

        if (!in_array($type, ['customer', 'vendor', 'both'], true)) {
            $errors[] = 'Invalid contact type.';
        }

        if ($displayName === '') {
            $errors[] = 'Display name is required.';
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/contacts/' . $contactId . '/edit');
        }

        $contactData = [
            'type'          => $type,
            'company_name'  => $companyName !== '' ? $companyName : null,
            'first_name'    => $firstName !== '' ? $firstName : null,
            'last_name'     => $lastName !== '' ? $lastName : null,
            'display_name'  => $displayName,
            'email'         => $email !== '' ? $email : null,
            'phone'         => $phone !== '' ? $phone : null,
            'tax_id'        => $taxId !== '' ? $taxId : null,
            'website'       => $website !== '' ? $website : null,
            'payment_terms' => $terms,
            'credit_limit'  => $creditLimit !== '' ? $creditLimit : null,
            'notes'         => $notes !== '' ? $notes : null,
        ];

        $this->contactModel->update($contactId, $contactData);

        // Update address
        $line1 = trim((string) $this->request->post('address_line1', ''));
        $addressId = (int) $this->request->post('address_id', 0);

        if ($line1 !== '') {
            $addressData = [
                'line1'       => $line1,
                'line2'       => trim((string) $this->request->post('address_line2', '')) ?: null,
                'city'        => trim((string) $this->request->post('address_city', '')),
                'state'       => trim((string) $this->request->post('address_state', '')) ?: null,
                'postal_code' => trim((string) $this->request->post('address_postal_code', '')) ?: null,
                'country'     => trim((string) $this->request->post('address_country', 'US')),
            ];

            if ($addressId > 0) {
                $this->addressModel->update($addressId, $addressData);
            } else {
                $addressData['contact_id'] = $contactId;
                $addressData['type'] = 'billing';
                $addressData['is_default'] = 1;
                $this->addressModel->create($addressData);
            }
        }

        $this->flash('success', 'Contact updated successfully.');
        return $this->redirect('/contacts/' . $contactId);
    }
}
