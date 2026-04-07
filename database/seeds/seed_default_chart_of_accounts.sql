-- =============================================================================
-- Default Chart of Accounts for Small Business
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 1000-1999: Assets
-- -----------------------------------------------------------------------------
INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id, is_bank_account) VALUES
('1000', 'Cash',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_ASSET'), 1);

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id, is_bank_account) VALUES
('1010', 'Petty Cash',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_ASSET'), 1);

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id, is_system) VALUES
('1100', 'Accounts Receivable',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_ASSET'), 1);

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('1200', 'Inventory',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_ASSET'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('1300', 'Prepaid Expenses',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_ASSET'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('1500', 'Equipment',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'FIXED_ASSET'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('1510', 'Accumulated Depreciation - Equipment',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'FIXED_ASSET'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('1600', 'Buildings',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'FIXED_ASSET'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('1610', 'Accumulated Depreciation - Buildings',
    (SELECT id FROM account_types WHERE code = 'ASSET'),
    (SELECT id FROM account_subtypes WHERE code = 'FIXED_ASSET'));

-- -----------------------------------------------------------------------------
-- 2000-2999: Liabilities
-- -----------------------------------------------------------------------------
INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id, is_system) VALUES
('2000', 'Accounts Payable',
    (SELECT id FROM account_types WHERE code = 'LIABILITY'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_LIABILITY'), 1);

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('2100', 'Accrued Liabilities',
    (SELECT id FROM account_types WHERE code = 'LIABILITY'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_LIABILITY'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('2200', 'Sales Tax Payable',
    (SELECT id FROM account_types WHERE code = 'LIABILITY'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_LIABILITY'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('2300', 'Wages Payable',
    (SELECT id FROM account_types WHERE code = 'LIABILITY'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_LIABILITY'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('2400', 'Unearned Revenue',
    (SELECT id FROM account_types WHERE code = 'LIABILITY'),
    (SELECT id FROM account_subtypes WHERE code = 'CURRENT_LIABILITY'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('2500', 'Notes Payable',
    (SELECT id FROM account_types WHERE code = 'LIABILITY'),
    (SELECT id FROM account_subtypes WHERE code = 'LONG_TERM_LIABILITY'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('2600', 'Mortgage Payable',
    (SELECT id FROM account_types WHERE code = 'LIABILITY'),
    (SELECT id FROM account_subtypes WHERE code = 'LONG_TERM_LIABILITY'));

-- -----------------------------------------------------------------------------
-- 3000-3999: Equity
-- -----------------------------------------------------------------------------
INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('3000', 'Owner''s Equity',
    (SELECT id FROM account_types WHERE code = 'EQUITY'),
    (SELECT id FROM account_subtypes WHERE code = 'OWNERS_EQUITY'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id, is_system) VALUES
('3100', 'Retained Earnings',
    (SELECT id FROM account_types WHERE code = 'EQUITY'),
    (SELECT id FROM account_subtypes WHERE code = 'RETAINED_EARNINGS'), 1);

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('3200', 'Owner''s Drawings',
    (SELECT id FROM account_types WHERE code = 'EQUITY'),
    (SELECT id FROM account_subtypes WHERE code = 'OWNERS_EQUITY'));

-- -----------------------------------------------------------------------------
-- 4000-4999: Revenue
-- -----------------------------------------------------------------------------
INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('4000', 'Sales Revenue',
    (SELECT id FROM account_types WHERE code = 'REVENUE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_REVENUE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('4100', 'Service Revenue',
    (SELECT id FROM account_types WHERE code = 'REVENUE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_REVENUE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('4200', 'Interest Income',
    (SELECT id FROM account_types WHERE code = 'REVENUE'),
    (SELECT id FROM account_subtypes WHERE code = 'OTHER_REVENUE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('4300', 'Other Revenue',
    (SELECT id FROM account_types WHERE code = 'REVENUE'),
    (SELECT id FROM account_subtypes WHERE code = 'OTHER_REVENUE'));

-- -----------------------------------------------------------------------------
-- 5000-5999: Cost of Goods Sold
-- -----------------------------------------------------------------------------
INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('5000', 'Cost of Goods Sold',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'COGS'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('5100', 'Purchase Discounts',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'COGS'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('5200', 'Freight In',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'COGS'));

-- -----------------------------------------------------------------------------
-- 6000-6999: Operating Expenses
-- -----------------------------------------------------------------------------
INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6000', 'Salaries & Wages',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6100', 'Rent Expense',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6200', 'Utilities',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6300', 'Office Supplies',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6400', 'Depreciation Expense',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6500', 'Insurance',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6600', 'Advertising',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6700', 'Professional Fees',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6800', 'Travel & Entertainment',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OPERATING_EXPENSE'));

INSERT INTO accounts (account_number, name, account_type_id, account_subtype_id) VALUES
('6900', 'Miscellaneous Expense',
    (SELECT id FROM account_types WHERE code = 'EXPENSE'),
    (SELECT id FROM account_subtypes WHERE code = 'OTHER_EXPENSE'));
