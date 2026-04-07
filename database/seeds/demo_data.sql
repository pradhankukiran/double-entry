-- =============================================================================
-- Demo Data Seed: Apex Consulting Group
-- =============================================================================
-- Fictional consulting company operating Jan-Mar 2026.
-- Prerequisites: default roles, default admin (id=1), account types, and
--                default chart of accounts must already be seeded.
--
-- IMPORTANT: Every journal entry balances (debits = credits).
--            Invoice totals = sum(line_totals).
--            amount_paid + balance_due = total on every invoice.
--            Payment amounts = sum(allocations).
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 0. Account ID Variables
--    Look up once, reuse everywhere.
-- -----------------------------------------------------------------------------
SET @acct_cash       = (SELECT id FROM accounts WHERE account_number = '1000');
SET @acct_ar         = (SELECT id FROM accounts WHERE account_number = '1100');
SET @acct_ap         = (SELECT id FROM accounts WHERE account_number = '2000');
SET @acct_owners_eq  = (SELECT id FROM accounts WHERE account_number = '3000');
SET @acct_svc_rev    = (SELECT id FROM accounts WHERE account_number = '4100');
SET @acct_salaries   = (SELECT id FROM accounts WHERE account_number = '6000');
SET @acct_rent       = (SELECT id FROM accounts WHERE account_number = '6100');
SET @acct_utilities  = (SELECT id FROM accounts WHERE account_number = '6200');
SET @acct_supplies   = (SELECT id FROM accounts WHERE account_number = '6300');
SET @acct_insurance  = (SELECT id FROM accounts WHERE account_number = '6500');
SET @acct_prof_fees  = (SELECT id FROM accounts WHERE account_number = '6700');


-- =============================================================================
-- 1. USERS
-- =============================================================================
-- bcrypt hash for 'demo123'
SET @demo_hash = '$2y$12$wM.sqx2Q4XUceH5P2GrrcuqgYDrMEdV.oop8iBexzq8rn51UFzJBW';

INSERT INTO users (id, email, password_hash, first_name, last_name, phone, is_active, created_at, updated_at)
VALUES
    (2, 'sarah@apex-consulting.com', @demo_hash, 'Sarah', 'Chen',   '(212) 555-0198', 1, '2026-01-02 09:00:00', '2026-01-02 09:00:00'),
    (3, 'mike@apex-consulting.com',  @demo_hash, 'Mike',  'Torres', '(212) 555-0217', 1, '2026-01-02 09:15:00', '2026-01-02 09:15:00');

INSERT INTO user_roles (user_id, role_id, created_at) VALUES
    (2, 2, '2026-01-02 09:00:00'),   -- Sarah -> Accountant
    (3, 3, '2026-01-02 09:15:00');    -- Mike  -> Viewer


-- =============================================================================
-- 2. SETTINGS
-- =============================================================================
INSERT INTO settings (setting_key, setting_value, setting_group) VALUES
    ('company_name',      'Apex Consulting Group',              'company'),
    ('legal_name',        'Apex Consulting Group LLC',          'company'),
    ('tax_id',            '47-1234567',                         'company'),
    ('address',           '350 Fifth Avenue, Suite 4200',       'company'),
    ('city',              'New York',                           'company'),
    ('state',             'NY',                                 'company'),
    ('postal_code',       '10118',                              'company'),
    ('country',           'US',                                 'company'),
    ('phone',             '(212) 555-0142',                     'company'),
    ('email',             'info@apex-consulting.com',           'company'),
    ('website',           'www.apex-consulting.com',            'company'),
    ('default_currency',  'USD',                                'general'),
    ('fiscal_year_start', '1',                                  'general'),
    ('date_format',       'm/d/Y',                              'general');


-- =============================================================================
-- 3. FISCAL YEAR 2026 + 12 MONTHLY PERIODS
-- =============================================================================
INSERT INTO fiscal_years (id, name, start_date, end_date, status) VALUES
    (1, 'FY 2026', '2026-01-01', '2026-12-31', 'open');

INSERT INTO fiscal_periods (id, fiscal_year_id, period_number, name, start_date, end_date, status) VALUES
    ( 1, 1,  1, 'January 2026',   '2026-01-01', '2026-01-31', 'open'),
    ( 2, 1,  2, 'February 2026',  '2026-02-01', '2026-02-28', 'open'),
    ( 3, 1,  3, 'March 2026',     '2026-03-01', '2026-03-31', 'open'),
    ( 4, 1,  4, 'April 2026',     '2026-04-01', '2026-04-30', 'open'),
    ( 5, 1,  5, 'May 2026',       '2026-05-01', '2026-05-31', 'open'),
    ( 6, 1,  6, 'June 2026',      '2026-06-01', '2026-06-30', 'open'),
    ( 7, 1,  7, 'July 2026',      '2026-07-01', '2026-07-31', 'open'),
    ( 8, 1,  8, 'August 2026',    '2026-08-01', '2026-08-31', 'open'),
    ( 9, 1,  9, 'September 2026', '2026-09-01', '2026-09-30', 'open'),
    (10, 1, 10, 'October 2026',   '2026-10-01', '2026-10-31', 'open'),
    (11, 1, 11, 'November 2026',  '2026-11-01', '2026-11-30', 'open'),
    (12, 1, 12, 'December 2026',  '2026-12-01', '2026-12-31', 'open');

-- Period ID variables
SET @period_jan = 1;
SET @period_feb = 2;
SET @period_mar = 3;


-- =============================================================================
-- 4. CONTACTS
-- =============================================================================

-- Customers
INSERT INTO contacts (id, type, company_name, first_name, last_name, display_name, email, phone, website, payment_terms, credit_limit, is_active, created_by, created_at) VALUES
    (1, 'customer', 'Meridian Tech Solutions',  'David',   'Park',     'Meridian Tech Solutions',  'david.park@meridian-tech.com',   '(646) 555-0301', 'www.meridian-tech.com',   30, 50000.00, 1, 1, '2026-01-02 10:00:00'),
    (2, 'customer', 'Brightpath Education',     'Lisa',    'Nguyen',   'Brightpath Education',     'lisa.nguyen@brightpathedu.org',  '(212) 555-0412', NULL,                       30, 30000.00, 1, 1, '2026-01-02 10:15:00'),
    (3, 'customer', 'Ironclad Security',        'James',   'Morrison', 'Ironclad Security',        'jmorrison@ironcladsec.com',      '(718) 555-0528', 'www.ironcladsec.com',      15, 75000.00, 1, 1, '2026-01-02 10:30:00'),
    (4, 'customer', 'Greenfield Properties',    'Amanda',  'Torres',   'Greenfield Properties',    'atorres@greenfieldprop.com',     '(914) 555-0634', 'www.greenfieldprop.com',   45, 25000.00, 1, 1, '2026-01-15 11:00:00');

-- Vendors
INSERT INTO contacts (id, type, company_name, first_name, last_name, display_name, email, phone, website, payment_terms, is_active, created_by, created_at) VALUES
    (5, 'vendor', 'WeWork Spaces',       'Rachel', 'Kim',     'WeWork Spaces',       'billing@wework.com',          '(212) 555-0700', 'www.wework.com',        30, 1, 1, '2026-01-02 10:45:00'),
    (6, 'vendor', 'CloudStack Hosting',  'Tyler',  'Reed',    'CloudStack Hosting',  'accounts@cloudstack.io',      '(888) 555-0801', 'www.cloudstack.io',     30, 1, 1, '2026-01-02 11:00:00'),
    (7, 'vendor', 'Metro Power & Light', NULL,     NULL,      'Metro Power & Light', 'business@metropower.com',     '(800) 555-0900', 'www.metropowerlight.com', 15, 1, 1, '2026-01-02 11:15:00');

-- Billing Addresses
INSERT INTO contact_addresses (contact_id, type, line1, line2, city, state, postal_code, country, is_default) VALUES
    (1, 'billing', '200 Park Avenue',          'Floor 18',   'New York',    'NY', '10166', 'US', 1),
    (2, 'billing', '45 Rockefeller Plaza',     'Suite 2000', 'New York',    'NY', '10111', 'US', 1),
    (3, 'billing', '1 Liberty Plaza',          'Floor 35',   'New York',    'NY', '10006', 'US', 1),
    (4, 'billing', '500 Mamaroneck Avenue',    'Suite 320',  'Harrison',    'NY', '10528', 'US', 1),
    (5, 'billing', '115 Broadway',             NULL,         'New York',    'NY', '10006', 'US', 1),
    (6, 'billing', '100 Innovation Drive',     NULL,         'San Jose',    'CA', '95134', 'US', 1),
    (7, 'billing', 'PO Box 5100',             NULL,         'New York',    'NY', '10008', 'US', 1);


-- =============================================================================
-- 5. JOURNAL ENTRIES
-- =============================================================================
-- All entries: status='posted', posted_by=1, created_by=1.
-- fiscal_year_id=1 for all.
-- Dates and fiscal_period_id must match.
-- =============================================================================

-- -------------------------------------------------------------------------
-- JE-000001: Opening balance (Jan 1)
-- Debit Cash 50,000 / Credit Owner's Equity 50,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (1, 'JE-000001', '2026-01-01', 'Opening balance — initial capital contribution', 'OPENING', 'posted', 'manual', 1, @period_jan, '2026-01-01 08:00:00', 1, 1, '2026-01-01 08:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (1, @acct_cash,      'Cash — opening balance',             50000.00,     0.00, 1),
    (1, @acct_owners_eq, 'Owner''s Equity — opening balance',      0.00, 50000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000002: Jan rent (Jan 2)
-- Debit Rent Expense 4,500 / Credit Cash 4,500
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (2, 'JE-000002', '2026-01-02', 'January 2026 office rent — WeWork Spaces', 'RENT-JAN', 'posted', 'manual', 1, @period_jan, '2026-01-02 09:30:00', 1, 1, '2026-01-02 09:30:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (2, @acct_rent, 'Rent expense — January 2026',  4500.00,    0.00, 1),
    (2, @acct_cash, 'Cash — rent payment',               0.00, 4500.00, 2);

-- -------------------------------------------------------------------------
-- JE-000003: Feb rent (Feb 2)
-- Debit Rent Expense 4,500 / Credit Cash 4,500
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (3, 'JE-000003', '2026-02-02', 'February 2026 office rent — WeWork Spaces', 'RENT-FEB', 'posted', 'manual', 1, @period_feb, '2026-02-02 09:30:00', 1, 1, '2026-02-02 09:30:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (3, @acct_rent, 'Rent expense — February 2026', 4500.00,    0.00, 1),
    (3, @acct_cash, 'Cash — rent payment',               0.00, 4500.00, 2);

-- -------------------------------------------------------------------------
-- JE-000004: Mar rent (Mar 2)
-- Debit Rent Expense 4,500 / Credit Cash 4,500
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (4, 'JE-000004', '2026-03-02', 'March 2026 office rent — WeWork Spaces', 'RENT-MAR', 'posted', 'manual', 1, @period_mar, '2026-03-02 09:30:00', 1, 1, '2026-03-02 09:30:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (4, @acct_rent, 'Rent expense — March 2026', 4500.00,    0.00, 1),
    (4, @acct_cash, 'Cash — rent payment',            0.00, 4500.00, 2);

-- -------------------------------------------------------------------------
-- JE-000005: Meridian Jan invoice (Jan 10) — INV-000001
-- Debit AR 12,000 / Credit Service Revenue 12,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (5, 'JE-000005', '2026-01-10', 'Invoice INV-000001 — Meridian Tech Solutions', 'INV-000001', 'posted', 'invoice', 1, @period_jan, '2026-01-10 11:00:00', 1, 1, '2026-01-10 11:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (5, @acct_ar,      'Accounts Receivable — Meridian Tech', 12000.00,     0.00, 1),
    (5, @acct_svc_rev, 'Service Revenue — strategy consulting', 0.00, 12000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000006: Brightpath Jan invoice (Jan 12) — INV-000002
-- Debit AR 8,500 / Credit Service Revenue 8,500
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (6, 'JE-000006', '2026-01-12', 'Invoice INV-000002 — Brightpath Education', 'INV-000002', 'posted', 'invoice', 1, @period_jan, '2026-01-12 14:00:00', 1, 1, '2026-01-12 14:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (6, @acct_ar,      'Accounts Receivable — Brightpath',     8500.00,    0.00, 1),
    (6, @acct_svc_rev, 'Service Revenue — curriculum review',      0.00, 8500.00, 2);

-- -------------------------------------------------------------------------
-- JE-000007: Ironclad Jan invoice (Jan 15) — INV-000003
-- Debit AR 15,000 / Credit Service Revenue 15,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (7, 'JE-000007', '2026-01-15', 'Invoice INV-000003 — Ironclad Security', 'INV-000003', 'posted', 'invoice', 1, @period_jan, '2026-01-15 10:00:00', 1, 1, '2026-01-15 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (7, @acct_ar,      'Accounts Receivable — Ironclad',        15000.00,     0.00, 1),
    (7, @acct_svc_rev, 'Service Revenue — security assessment',      0.00, 15000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000008: Meridian Feb invoice (Feb 8) — INV-000004
-- Debit AR 12,000 / Credit Service Revenue 12,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (8, 'JE-000008', '2026-02-08', 'Invoice INV-000004 — Meridian Tech Solutions', 'INV-000004', 'posted', 'invoice', 1, @period_feb, '2026-02-08 11:00:00', 1, 1, '2026-02-08 11:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (8, @acct_ar,      'Accounts Receivable — Meridian Tech',  12000.00,     0.00, 1),
    (8, @acct_svc_rev, 'Service Revenue — process optimization',   0.00, 12000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000009: Brightpath Feb invoice (Feb 10) — INV-000005
-- Debit AR 9,200 / Credit Service Revenue 9,200
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (9, 'JE-000009', '2026-02-10', 'Invoice INV-000005 — Brightpath Education', 'INV-000005', 'posted', 'invoice', 1, @period_feb, '2026-02-10 14:00:00', 1, 1, '2026-02-10 14:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (9, @acct_ar,      'Accounts Receivable — Brightpath',      9200.00,    0.00, 1),
    (9, @acct_svc_rev, 'Service Revenue — training program',        0.00, 9200.00, 2);

-- -------------------------------------------------------------------------
-- JE-000010: Greenfield Feb invoice (Feb 15) — INV-000006
-- Debit AR 6,800 / Credit Service Revenue 6,800
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (10, 'JE-000010', '2026-02-15', 'Invoice INV-000006 — Greenfield Properties', 'INV-000006', 'posted', 'invoice', 1, @period_feb, '2026-02-15 16:00:00', 1, 1, '2026-02-15 16:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (10, @acct_ar,      'Accounts Receivable — Greenfield',     6800.00,    0.00, 1),
    (10, @acct_svc_rev, 'Service Revenue — market analysis',        0.00, 6800.00, 2);

-- -------------------------------------------------------------------------
-- JE-000011: Meridian Mar invoice (Mar 8) — INV-000007
-- Debit AR 12,000 / Credit Service Revenue 12,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (11, 'JE-000011', '2026-03-08', 'Invoice INV-000007 — Meridian Tech Solutions', 'INV-000007', 'posted', 'invoice', 1, @period_mar, '2026-03-08 11:00:00', 1, 1, '2026-03-08 11:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (11, @acct_ar,      'Accounts Receivable — Meridian Tech',  12000.00,     0.00, 1),
    (11, @acct_svc_rev, 'Service Revenue — IT roadmap',              0.00, 12000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000012: Ironclad Mar invoice (Mar 12) — INV-000008
-- Debit AR 18,500 / Credit Service Revenue 18,500
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (12, 'JE-000012', '2026-03-12', 'Invoice INV-000008 — Ironclad Security', 'INV-000008', 'posted', 'invoice', 1, @period_mar, '2026-03-12 10:00:00', 1, 1, '2026-03-12 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (12, @acct_ar,      'Accounts Receivable — Ironclad',        18500.00,     0.00, 1),
    (12, @acct_svc_rev, 'Service Revenue — compliance audit',         0.00, 18500.00, 2);

-- -------------------------------------------------------------------------
-- JE-000013: Meridian pays Jan invoice (Jan 28)
-- Debit Cash 12,000 / Credit AR 12,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (13, 'JE-000013', '2026-01-28', 'Payment received — Meridian Tech Solutions (INV-000001)', 'PMT-R-000001', 'posted', 'payment', 1, @period_jan, '2026-01-28 15:00:00', 1, 1, '2026-01-28 15:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (13, @acct_cash, 'Cash — payment from Meridian',        12000.00,     0.00, 1),
    (13, @acct_ar,   'Accounts Receivable — Meridian',           0.00, 12000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000014: Brightpath pays Jan invoice (Jan 30)
-- Debit Cash 8,500 / Credit AR 8,500
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (14, 'JE-000014', '2026-01-30', 'Payment received — Brightpath Education (INV-000002)', 'PMT-R-000002', 'posted', 'payment', 1, @period_jan, '2026-01-30 10:00:00', 1, 1, '2026-01-30 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (14, @acct_cash, 'Cash — payment from Brightpath',      8500.00,    0.00, 1),
    (14, @acct_ar,   'Accounts Receivable — Brightpath',        0.00, 8500.00, 2);

-- -------------------------------------------------------------------------
-- JE-000015: Ironclad pays Jan invoice (Jan 25 — Net 15 terms)
-- Debit Cash 15,000 / Credit AR 15,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (15, 'JE-000015', '2026-01-25', 'Payment received — Ironclad Security (INV-000003)', 'PMT-R-000003', 'posted', 'payment', 1, @period_jan, '2026-01-25 14:30:00', 1, 1, '2026-01-25 14:30:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (15, @acct_cash, 'Cash — payment from Ironclad',        15000.00,     0.00, 1),
    (15, @acct_ar,   'Accounts Receivable — Ironclad',           0.00, 15000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000016: Meridian pays Feb invoice (Mar 5)
-- Debit Cash 12,000 / Credit AR 12,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (16, 'JE-000016', '2026-03-05', 'Payment received — Meridian Tech Solutions (INV-000004)', 'PMT-R-000004', 'posted', 'payment', 1, @period_mar, '2026-03-05 11:00:00', 1, 1, '2026-03-05 11:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (16, @acct_cash, 'Cash — payment from Meridian',        12000.00,     0.00, 1),
    (16, @acct_ar,   'Accounts Receivable — Meridian',           0.00, 12000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000017: Brightpath partial payment on Feb invoice (Mar 10)
-- Debit Cash 5,000 / Credit AR 5,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (17, 'JE-000017', '2026-03-10', 'Partial payment received — Brightpath Education (INV-000005)', 'PMT-R-000005', 'posted', 'payment', 1, @period_mar, '2026-03-10 09:00:00', 1, 1, '2026-03-10 09:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (17, @acct_cash, 'Cash — partial payment from Brightpath', 5000.00,    0.00, 1),
    (17, @acct_ar,   'Accounts Receivable — Brightpath',           0.00, 5000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000018: CloudStack Jan hosting bill (Jan 20) — BILL-000001
-- Debit Professional Fees 850 / Credit AP 850
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (18, 'JE-000018', '2026-01-20', 'Bill BILL-000001 — CloudStack Hosting (January)', 'BILL-000001', 'posted', 'invoice', 1, @period_jan, '2026-01-20 10:00:00', 1, 1, '2026-01-20 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (18, @acct_prof_fees, 'Cloud hosting — January 2026',   850.00,   0.00, 1),
    (18, @acct_ap,        'Accounts Payable — CloudStack',    0.00, 850.00, 2);

-- -------------------------------------------------------------------------
-- JE-000019: CloudStack Feb hosting bill (Feb 20) — BILL-000002
-- Debit Professional Fees 850 / Credit AP 850
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (19, 'JE-000019', '2026-02-20', 'Bill BILL-000002 — CloudStack Hosting (February)', 'BILL-000002', 'posted', 'invoice', 1, @period_feb, '2026-02-20 10:00:00', 1, 1, '2026-02-20 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (19, @acct_prof_fees, 'Cloud hosting — February 2026',  850.00,   0.00, 1),
    (19, @acct_ap,        'Accounts Payable — CloudStack',    0.00, 850.00, 2);

-- -------------------------------------------------------------------------
-- JE-000020: Metro Power Jan utilities (Jan 22) — BILL-000003
-- Debit Utilities 320 / Credit AP 320
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (20, 'JE-000020', '2026-01-22', 'Bill BILL-000003 — Metro Power & Light (January)', 'BILL-000003', 'posted', 'invoice', 1, @period_jan, '2026-01-22 10:00:00', 1, 1, '2026-01-22 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (20, @acct_utilities, 'Utilities — January 2026',       320.00,   0.00, 1),
    (20, @acct_ap,        'Accounts Payable — Metro Power',   0.00, 320.00, 2);

-- -------------------------------------------------------------------------
-- JE-000021: Metro Power Feb utilities (Feb 22) — BILL-000004
-- Debit Utilities 345 / Credit AP 345
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (21, 'JE-000021', '2026-02-22', 'Bill BILL-000004 — Metro Power & Light (February)', 'BILL-000004', 'posted', 'invoice', 1, @period_feb, '2026-02-22 10:00:00', 1, 1, '2026-02-22 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (21, @acct_utilities, 'Utilities — February 2026',      345.00,   0.00, 1),
    (21, @acct_ap,        'Accounts Payable — Metro Power',   0.00, 345.00, 2);

-- -------------------------------------------------------------------------
-- JE-000022: Pay CloudStack Jan bill (Feb 5)
-- Debit AP 850 / Credit Cash 850
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (22, 'JE-000022', '2026-02-05', 'Payment made — CloudStack Hosting (BILL-000001)', 'PMT-M-000001', 'posted', 'payment', 1, @period_feb, '2026-02-05 10:00:00', 1, 1, '2026-02-05 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (22, @acct_ap,   'Accounts Payable — CloudStack',  850.00,   0.00, 1),
    (22, @acct_cash, 'Cash — bill payment',               0.00, 850.00, 2);

-- -------------------------------------------------------------------------
-- JE-000023: Pay Metro Power Jan bill (Feb 5)
-- Debit AP 320 / Credit Cash 320
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (23, 'JE-000023', '2026-02-05', 'Payment made — Metro Power & Light (BILL-000003)', 'PMT-M-000002', 'posted', 'payment', 1, @period_feb, '2026-02-05 10:15:00', 1, 1, '2026-02-05 10:15:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (23, @acct_ap,   'Accounts Payable — Metro Power',  320.00,   0.00, 1),
    (23, @acct_cash, 'Cash — bill payment',                0.00, 320.00, 2);

-- -------------------------------------------------------------------------
-- JE-000024: Pay CloudStack Feb bill (Mar 15)
-- Debit AP 850 / Credit Cash 850
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (24, 'JE-000024', '2026-03-15', 'Payment made — CloudStack Hosting (BILL-000002)', 'PMT-M-000003', 'posted', 'payment', 1, @period_mar, '2026-03-15 10:00:00', 1, 1, '2026-03-15 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (24, @acct_ap,   'Accounts Payable — CloudStack',  850.00,   0.00, 1),
    (24, @acct_cash, 'Cash — bill payment',               0.00, 850.00, 2);

-- -------------------------------------------------------------------------
-- JE-000025: Jan payroll (Jan 31)
-- Debit Salaries 18,000 / Credit Cash 18,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (25, 'JE-000025', '2026-01-31', 'January 2026 payroll', 'PAYROLL-JAN', 'posted', 'manual', 1, @period_jan, '2026-01-31 17:00:00', 1, 1, '2026-01-31 17:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (25, @acct_salaries, 'Salaries & Wages — January 2026',  18000.00,     0.00, 1),
    (25, @acct_cash,     'Cash — payroll disbursement',            0.00, 18000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000026: Feb payroll (Feb 28)
-- Debit Salaries 18,000 / Credit Cash 18,000
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (26, 'JE-000026', '2026-02-28', 'February 2026 payroll', 'PAYROLL-FEB', 'posted', 'manual', 1, @period_feb, '2026-02-28 17:00:00', 1, 1, '2026-02-28 17:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (26, @acct_salaries, 'Salaries & Wages — February 2026', 18000.00,     0.00, 1),
    (26, @acct_cash,     'Cash — payroll disbursement',            0.00, 18000.00, 2);

-- -------------------------------------------------------------------------
-- JE-000027: Mar payroll (Mar 31)
-- Debit Salaries 18,500 / Credit Cash 18,500
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (27, 'JE-000027', '2026-03-31', 'March 2026 payroll', 'PAYROLL-MAR', 'posted', 'manual', 1, @period_mar, '2026-03-31 17:00:00', 1, 1, '2026-03-31 17:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (27, @acct_salaries, 'Salaries & Wages — March 2026',  18500.00,     0.00, 1),
    (27, @acct_cash,     'Cash — payroll disbursement',          0.00, 18500.00, 2);

-- -------------------------------------------------------------------------
-- JE-000028: Q1 insurance premium (Jan 15)
-- Debit Insurance 2,400 / Credit Cash 2,400
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (28, 'JE-000028', '2026-01-15', 'Q1 2026 business insurance premium', 'INS-Q1-2026', 'posted', 'manual', 1, @period_jan, '2026-01-15 10:00:00', 1, 1, '2026-01-15 10:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (28, @acct_insurance, 'Insurance — Q1 2026 premium',   2400.00,    0.00, 1),
    (28, @acct_cash,      'Cash — insurance payment',          0.00, 2400.00, 2);

-- -------------------------------------------------------------------------
-- JE-000029: Office supplies purchase (Feb 12)
-- Debit Office Supplies 675 / Credit Cash 675
-- -------------------------------------------------------------------------
INSERT INTO journal_entries (id, entry_number, entry_date, description, reference, status, source_type, fiscal_year_id, fiscal_period_id, posted_at, posted_by, created_by, created_at)
VALUES (29, 'JE-000029', '2026-02-12', 'Office supplies — paper, toner, general supplies', 'SUPPLIES-FEB', 'posted', 'manual', 1, @period_feb, '2026-02-12 14:00:00', 1, 1, '2026-02-12 14:00:00');

INSERT INTO journal_entry_lines (journal_entry_id, account_id, description, debit, credit, line_order) VALUES
    (29, @acct_supplies, 'Office Supplies — general',   675.00,   0.00, 1),
    (29, @acct_cash,     'Cash — supplies purchase',      0.00, 675.00, 2);


-- =============================================================================
-- 6. INVOICES (8 customer invoices + 4 vendor bills)
-- =============================================================================
-- Invoice status logic:
--   INV-000001: total=12000, paid=12000, balance=0     -> 'paid'
--   INV-000002: total=8500,  paid=8500,  balance=0     -> 'paid'
--   INV-000003: total=15000, paid=15000, balance=0     -> 'paid'
--   INV-000004: total=12000, paid=12000, balance=0     -> 'paid'
--   INV-000005: total=9200,  paid=5000,  balance=4200  -> 'partial'
--   INV-000006: total=6800,  paid=0,     balance=6800  -> 'sent' (not yet due, Net 45 from Feb 15 = Apr 1)
--   INV-000007: total=12000, paid=0,     balance=12000 -> 'sent' (Net 30 from Mar 8 = Apr 7)
--   INV-000008: total=18500, paid=0,     balance=18500 -> 'sent' (Net 15 from Mar 12 = Mar 27 — overdue by Apr)
--
--   BILL-000001: total=850,  paid=850,  balance=0   -> 'paid'
--   BILL-000002: total=850,  paid=850,  balance=0   -> 'paid'
--   BILL-000003: total=320,  paid=320,  balance=0   -> 'paid'
--   BILL-000004: total=345,  paid=0,    balance=345 -> 'sent' (unpaid)
-- =============================================================================

-- Customer Invoices
INSERT INTO invoices (id, document_type, document_number, contact_id, issue_date, due_date, subtotal, tax_amount, total, amount_paid, balance_due, currency_code, status, terms, journal_entry_id, ar_ap_account_id, created_by, created_at) VALUES
    ( 1, 'invoice', 'INV-000001', 1, '2026-01-10', '2026-02-09', 12000.00, 0.00, 12000.00, 12000.00,     0.00, 'USD', 'paid',    'Net 30',  5, @acct_ar, 1, '2026-01-10 11:00:00'),
    ( 2, 'invoice', 'INV-000002', 2, '2026-01-12', '2026-02-11', 8500.00,  0.00,  8500.00,  8500.00,     0.00, 'USD', 'paid',    'Net 30',  6, @acct_ar, 1, '2026-01-12 14:00:00'),
    ( 3, 'invoice', 'INV-000003', 3, '2026-01-15', '2026-01-30', 15000.00, 0.00, 15000.00, 15000.00,     0.00, 'USD', 'paid',    'Net 15',  7, @acct_ar, 1, '2026-01-15 10:00:00'),
    ( 4, 'invoice', 'INV-000004', 1, '2026-02-08', '2026-03-10', 12000.00, 0.00, 12000.00, 12000.00,     0.00, 'USD', 'paid',    'Net 30',  8, @acct_ar, 1, '2026-02-08 11:00:00'),
    ( 5, 'invoice', 'INV-000005', 2, '2026-02-10', '2026-03-12', 9200.00,  0.00,  9200.00,  5000.00,  4200.00, 'USD', 'partial', 'Net 30',  9, @acct_ar, 1, '2026-02-10 14:00:00'),
    ( 6, 'invoice', 'INV-000006', 4, '2026-02-15', '2026-04-01', 6800.00,  0.00,  6800.00,     0.00,  6800.00, 'USD', 'sent',    'Net 45', 10, @acct_ar, 1, '2026-02-15 16:00:00'),
    ( 7, 'invoice', 'INV-000007', 1, '2026-03-08', '2026-04-07', 12000.00, 0.00, 12000.00,     0.00, 12000.00, 'USD', 'sent',    'Net 30', 11, @acct_ar, 1, '2026-03-08 11:00:00'),
    ( 8, 'invoice', 'INV-000008', 3, '2026-03-12', '2026-03-27', 18500.00, 0.00, 18500.00,     0.00, 18500.00, 'USD', 'overdue', 'Net 15', 12, @acct_ar, 1, '2026-03-12 10:00:00');

-- Vendor Bills
INSERT INTO invoices (id, document_type, document_number, contact_id, issue_date, due_date, subtotal, tax_amount, total, amount_paid, balance_due, currency_code, status, terms, journal_entry_id, ar_ap_account_id, created_by, created_at) VALUES
    ( 9, 'bill', 'BILL-000001', 6, '2026-01-20', '2026-02-19',  850.00, 0.00,  850.00, 850.00,   0.00, 'USD', 'paid', 'Net 30', 18, @acct_ap, 1, '2026-01-20 10:00:00'),
    (10, 'bill', 'BILL-000002', 6, '2026-02-20', '2026-03-22',  850.00, 0.00,  850.00, 850.00,   0.00, 'USD', 'paid', 'Net 30', 19, @acct_ap, 1, '2026-02-20 10:00:00'),
    (11, 'bill', 'BILL-000003', 7, '2026-01-22', '2026-02-06',  320.00, 0.00,  320.00, 320.00,   0.00, 'USD', 'paid', 'Net 15', 20, @acct_ap, 1, '2026-01-22 10:00:00'),
    (12, 'bill', 'BILL-000004', 7, '2026-02-22', '2026-03-09',  345.00, 0.00,  345.00,   0.00, 345.00, 'USD', 'overdue', 'Net 15', 21, @acct_ap, 1, '2026-02-22 10:00:00');


-- =============================================================================
-- 7. INVOICE LINES
-- =============================================================================

-- INV-000001: Meridian Tech — 12,000 (two service lines)
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (1, 'Strategy consulting — digital transformation',   @acct_svc_rev, 80.0000, 100.0000, 0.00, 8000.00, 1),
    (1, 'Executive workshop facilitation',                @acct_svc_rev, 20.0000, 200.0000, 0.00, 4000.00, 2);

-- INV-000002: Brightpath Education — 8,500
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (2, 'Curriculum review and assessment',               @acct_svc_rev, 50.0000, 120.0000, 0.00, 6000.00, 1),
    (2, 'Stakeholder interviews and report',              @acct_svc_rev, 25.0000, 100.0000, 0.00, 2500.00, 2);

-- INV-000003: Ironclad Security — 15,000
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (3, 'Security infrastructure assessment',             @acct_svc_rev, 60.0000, 175.0000, 0.00, 10500.00, 1),
    (3, 'Compliance gap analysis',                        @acct_svc_rev, 30.0000, 150.0000, 0.00,  4500.00, 2);

-- INV-000004: Meridian Tech — 12,000
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (4, 'Process optimization — Phase 1',                 @acct_svc_rev, 60.0000, 150.0000, 0.00, 9000.00, 1),
    (4, 'Change management advisory',                     @acct_svc_rev, 20.0000, 150.0000, 0.00, 3000.00, 2);

-- INV-000005: Brightpath Education — 9,200
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (5, 'Teacher training program design',                @acct_svc_rev, 40.0000, 150.0000, 0.00, 6000.00, 1),
    (5, 'Program materials development',                  @acct_svc_rev, 32.0000, 100.0000, 0.00, 3200.00, 2);

-- INV-000006: Greenfield Properties — 6,800
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (6, 'Commercial real estate market analysis',         @acct_svc_rev, 34.0000, 200.0000, 0.00, 6800.00, 1);

-- INV-000007: Meridian Tech — 12,000
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (7, 'IT roadmap and technology assessment',           @acct_svc_rev, 48.0000, 175.0000, 0.00, 8400.00, 1),
    (7, 'Vendor evaluation and selection',                @acct_svc_rev, 24.0000, 150.0000, 0.00, 3600.00, 2);

-- INV-000008: Ironclad Security — 18,500
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (8, 'SOC 2 compliance audit preparation',             @acct_svc_rev, 74.0000, 200.0000, 0.00, 14800.00, 1),
    (8, 'Policy documentation and remediation plan',      @acct_svc_rev, 37.0000, 100.0000, 0.00,  3700.00, 2);

-- BILL-000001: CloudStack Jan — 850
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (9, 'Cloud hosting — January 2026',                   @acct_prof_fees, 1.0000, 850.0000, 0.00, 850.00, 1);

-- BILL-000002: CloudStack Feb — 850
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (10, 'Cloud hosting — February 2026',                 @acct_prof_fees, 1.0000, 850.0000, 0.00, 850.00, 1);

-- BILL-000003: Metro Power Jan — 320
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (11, 'Electric utility service — January 2026',       @acct_utilities, 1.0000, 320.0000, 0.00, 320.00, 1);

-- BILL-000004: Metro Power Feb — 345
INSERT INTO invoice_lines (invoice_id, description, account_id, quantity, unit_price, tax_amount, line_total, line_order) VALUES
    (12, 'Electric utility service — February 2026',      @acct_utilities, 1.0000, 345.0000, 0.00, 345.00, 1);


-- =============================================================================
-- 8. PAYMENTS + PAYMENT ALLOCATIONS
-- =============================================================================

-- ---- Received Payments (from customers) ----

-- PMT-R-000001: Meridian pays INV-000001 (Jan 28) — 12,000
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (1, 'PMT-R-000001', 1, 'received', '2026-01-28', 12000.00, 'bank_transfer', 'Wire ref MT-20260128', @acct_cash, 13, 'posted', 1, '2026-01-28 15:00:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (1, 1, 12000.00);

-- PMT-R-000002: Brightpath pays INV-000002 (Jan 30) — 8,500
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (2, 'PMT-R-000002', 2, 'received', '2026-01-30', 8500.00, 'check', 'Check #4012', @acct_cash, 14, 'posted', 1, '2026-01-30 10:00:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (2, 2, 8500.00);

-- PMT-R-000003: Ironclad pays INV-000003 (Jan 25) — 15,000
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (3, 'PMT-R-000003', 3, 'received', '2026-01-25', 15000.00, 'bank_transfer', 'Wire ref IC-0125', @acct_cash, 15, 'posted', 1, '2026-01-25 14:30:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (3, 3, 15000.00);

-- PMT-R-000004: Meridian pays INV-000004 (Mar 5) — 12,000
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (4, 'PMT-R-000004', 1, 'received', '2026-03-05', 12000.00, 'bank_transfer', 'Wire ref MT-20260305', @acct_cash, 16, 'posted', 1, '2026-03-05 11:00:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (4, 4, 12000.00);

-- PMT-R-000005: Brightpath partial on INV-000005 (Mar 10) — 5,000
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (5, 'PMT-R-000005', 2, 'received', '2026-03-10', 5000.00, 'check', 'Check #4089', @acct_cash, 17, 'posted', 1, '2026-03-10 09:00:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (5, 5, 5000.00);

-- ---- Made Payments (to vendors) ----

-- PMT-M-000001: Pay CloudStack BILL-000001 (Feb 5) — 850
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (6, 'PMT-M-000001', 6, 'made', '2026-02-05', 850.00, 'bank_transfer', 'ACH batch 020526', @acct_cash, 22, 'posted', 1, '2026-02-05 10:00:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (6, 9, 850.00);

-- PMT-M-000002: Pay Metro Power BILL-000003 (Feb 5) — 320
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (7, 'PMT-M-000002', 7, 'made', '2026-02-05', 320.00, 'bank_transfer', 'ACH batch 020526', @acct_cash, 23, 'posted', 1, '2026-02-05 10:15:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (7, 11, 320.00);

-- PMT-M-000003: Pay CloudStack BILL-000002 (Mar 15) — 850
INSERT INTO payments (id, payment_number, contact_id, type, payment_date, amount, payment_method, reference, deposit_account_id, journal_entry_id, status, created_by, created_at) VALUES
    (8, 'PMT-M-000003', 6, 'made', '2026-03-15', 850.00, 'bank_transfer', 'ACH batch 031526', @acct_cash, 24, 'posted', 1, '2026-03-15 10:00:00');

INSERT INTO payment_allocations (payment_id, invoice_id, amount) VALUES
    (8, 10, 850.00);


-- =============================================================================
-- 9. BANK ACCOUNT + IMPORT BATCH + BANK TRANSACTIONS
-- =============================================================================

-- First National Bank checking — linked to Cash account (1000)
INSERT INTO bank_accounts (id, account_id, bank_name, account_name, account_number_last4, currency_code, account_type, is_active, current_balance, last_imported_at, last_reconciled_at) VALUES
    (1, @acct_cash, 'First National Bank', 'Apex Consulting — Operating', '4521', 'USD', 'checking', 1, NULL, '2026-03-25 08:00:00', NULL);

-- Import batch
INSERT INTO bank_import_batches (id, bank_account_id, file_name, file_format, transaction_count, duplicate_count, imported_by, imported_at) VALUES
    (1, 1, 'fnb_jan_mar_2026.csv', 'csv', 16, 0, 1, '2026-03-25 08:00:00');

-- Bank transactions — 16 entries matching cash movements
-- (Mar 31 payroll not yet on statement — import was Mar 25)
-- Positive amounts = money in, negative amounts = money out
-- Some matched to journal entries, some unmatched for demo variety
--
-- Cash flow summary for verification:
--   +50,000 opening capital
--   -4,500  Jan rent
--   -4,500  Feb rent
--   -4,500  Mar rent
--   +12,000 Meridian Jan payment
--   +8,500  Brightpath Jan payment
--   +15,000 Ironclad Jan payment
--   +12,000 Meridian Feb payment
--   +5,000  Brightpath partial
--   -850    CloudStack Jan
--   -320    Metro Power Jan
--   -850    CloudStack Feb
--   -18,000 Jan payroll
--   -18,000 Feb payroll
--   -18,500 Mar payroll
--   -2,400  Insurance Q1
--   -675    Office supplies
--   = 29,405 ending cash balance

INSERT INTO bank_transactions (bank_account_id, import_batch_id, transaction_date, description, reference, amount, fit_id, status, journal_entry_id, matched_at, matched_by) VALUES
    -- Matched transactions (10)
    (1, 1, '2026-01-01', 'OPENING DEPOSIT',                        'DEP',        50000.00, 'FNB20260101001', 'matched',   1,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-01-02', 'ACH DEBIT WEWORK SPACES',                'ACH',        -4500.00, 'FNB20260102001', 'matched',   2,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-01-15', 'ACH DEBIT INSURANCE PREMIUM',            'ACH',        -2400.00, 'FNB20260115001', 'matched',  28,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-01-25', 'WIRE IN IRONCLAD SEC',                   'WIRE',       15000.00, 'FNB20260125001', 'matched',  15,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-01-28', 'WIRE IN MERIDIAN TECH',                  'WIRE',       12000.00, 'FNB20260128001', 'matched',  13,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-01-30', 'CHECK DEPOSIT #4012',                    'DEP',         8500.00, 'FNB20260130001', 'matched',  14,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-01-31', 'ACH DEBIT PAYROLL',                      'ACH',       -18000.00, 'FNB20260131001', 'matched',  25,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-02-02', 'ACH DEBIT WEWORK SPACES',                'ACH',        -4500.00, 'FNB20260202001', 'matched',   3,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-02-05', 'ACH DEBIT CLOUDSTACK',                   'ACH',         -850.00, 'FNB20260205001', 'matched',  22,  '2026-03-25 08:15:00', 1),
    (1, 1, '2026-02-05', 'ACH DEBIT METRO POWER',                  'ACH',         -320.00, 'FNB20260205002', 'matched',  23,  '2026-03-25 08:15:00', 1),

    -- Unmatched transactions (6) — user needs to match these in the app
    (1, 1, '2026-02-12', 'POS PURCHASE STAPLES #1247',             'POS',         -675.00, 'FNB20260212001', 'unmatched', NULL, NULL, NULL),
    (1, 1, '2026-02-28', 'ACH DEBIT PAYROLL',                      'ACH',       -18000.00, 'FNB20260228001', 'unmatched', NULL, NULL, NULL),
    (1, 1, '2026-03-02', 'ACH DEBIT WEWORK SPACES',                'ACH',        -4500.00, 'FNB20260302001', 'unmatched', NULL, NULL, NULL),
    (1, 1, '2026-03-05', 'WIRE IN MERIDIAN TECH',                  'WIRE',       12000.00, 'FNB20260305001', 'unmatched', NULL, NULL, NULL),
    (1, 1, '2026-03-10', 'CHECK DEPOSIT #4089',                    'DEP',         5000.00, 'FNB20260310001', 'unmatched', NULL, NULL, NULL),
    (1, 1, '2026-03-15', 'ACH DEBIT CLOUDSTACK',                   'ACH',         -850.00, 'FNB20260315001', 'unmatched', NULL, NULL, NULL);

-- Update current_balance to bank statement balance (sum of imported transactions).
-- This is the bank's view — it does NOT include the Mar 31 payroll (-18,500) which
-- hasn't appeared on the statement yet (import was Mar 25).
-- Bank balance: 50000 - 4500 - 2400 + 15000 + 12000 + 8500 - 18000
--             - 4500 - 850 - 320 - 675 - 18000 - 4500 + 12000 + 5000 - 850
--             = 47,905
-- Book balance (Cash account per GL): 47905 - 18500 (Mar payroll) = 29,405
UPDATE bank_accounts SET current_balance = 47905.00 WHERE id = 1;


-- =============================================================================
-- VERIFICATION QUERIES (commented out — run manually to validate)
-- =============================================================================
-- Check 1: Every JE balances
-- SELECT je.entry_number,
--        SUM(jel.debit) AS total_debit,
--        SUM(jel.credit) AS total_credit,
--        SUM(jel.debit) - SUM(jel.credit) AS diff
-- FROM journal_entries je
-- JOIN journal_entry_lines jel ON jel.journal_entry_id = je.id
-- GROUP BY je.id
-- HAVING diff != 0;
--
-- Check 2: Invoice totals match line totals
-- SELECT i.document_number,
--        i.total AS invoice_total,
--        COALESCE(SUM(il.line_total), 0) AS sum_lines,
--        i.total - COALESCE(SUM(il.line_total), 0) AS diff
-- FROM invoices i
-- LEFT JOIN invoice_lines il ON il.invoice_id = i.id
-- GROUP BY i.id
-- HAVING diff != 0;
--
-- Check 3: amount_paid + balance_due = total
-- SELECT document_number, total, amount_paid, balance_due,
--        total - amount_paid - balance_due AS diff
-- FROM invoices
-- HAVING diff != 0;
--
-- Check 4: Payment amounts match sum of allocations
-- SELECT p.payment_number,
--        p.amount AS payment_amount,
--        COALESCE(SUM(pa.amount), 0) AS sum_allocations,
--        p.amount - COALESCE(SUM(pa.amount), 0) AS diff
-- FROM payments p
-- LEFT JOIN payment_allocations pa ON pa.payment_id = p.id
-- GROUP BY p.id
-- HAVING diff != 0;
--
-- Check 5: Trial balance (debits = credits across all posted JEs)
-- SELECT SUM(jel.debit) AS total_debits,
--        SUM(jel.credit) AS total_credits,
--        SUM(jel.debit) - SUM(jel.credit) AS diff
-- FROM journal_entry_lines jel
-- JOIN journal_entries je ON je.id = jel.journal_entry_id
-- WHERE je.status = 'posted';
