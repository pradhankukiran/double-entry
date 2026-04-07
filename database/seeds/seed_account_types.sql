-- Account Types
INSERT INTO account_types (code, name, normal_balance, financial_statement, display_order) VALUES
('ASSET',     'Asset',     'debit',  'balance_sheet',     1),
('LIABILITY', 'Liability', 'credit', 'balance_sheet',     2),
('EQUITY',    'Equity',    'credit', 'balance_sheet',     3),
('REVENUE',   'Revenue',   'credit', 'income_statement',  4),
('EXPENSE',   'Expense',   'debit',  'income_statement',  5);

-- Account Subtypes
INSERT INTO account_subtypes (account_type_id, code, name, display_order) VALUES
-- Asset subtypes
((SELECT id FROM account_types WHERE code = 'ASSET'), 'CURRENT_ASSET', 'Current Asset', 1),
((SELECT id FROM account_types WHERE code = 'ASSET'), 'FIXED_ASSET',   'Fixed Asset',   2),
((SELECT id FROM account_types WHERE code = 'ASSET'), 'OTHER_ASSET',   'Other Asset',   3),
-- Liability subtypes
((SELECT id FROM account_types WHERE code = 'LIABILITY'), 'CURRENT_LIABILITY',   'Current Liability',   1),
((SELECT id FROM account_types WHERE code = 'LIABILITY'), 'LONG_TERM_LIABILITY', 'Long-Term Liability', 2),
-- Equity subtypes
((SELECT id FROM account_types WHERE code = 'EQUITY'), 'OWNERS_EQUITY',     'Owner\'s Equity',    1),
((SELECT id FROM account_types WHERE code = 'EQUITY'), 'RETAINED_EARNINGS', 'Retained Earnings',  2),
-- Revenue subtypes
((SELECT id FROM account_types WHERE code = 'REVENUE'), 'OPERATING_REVENUE', 'Operating Revenue', 1),
((SELECT id FROM account_types WHERE code = 'REVENUE'), 'OTHER_REVENUE',     'Other Revenue',     2),
-- Expense subtypes
((SELECT id FROM account_types WHERE code = 'EXPENSE'), 'OPERATING_EXPENSE', 'Operating Expense', 1),
((SELECT id FROM account_types WHERE code = 'EXPENSE'), 'COGS',              'Cost of Goods Sold', 2),
((SELECT id FROM account_types WHERE code = 'EXPENSE'), 'OTHER_EXPENSE',     'Other Expense',     3);
