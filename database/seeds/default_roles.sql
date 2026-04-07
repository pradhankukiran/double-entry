-- ============================================================
-- Default Roles
-- ============================================================

INSERT INTO roles (id, name, description, is_system) VALUES
(1, 'Admin',      'Full system access. Manages users, settings, and all accounting functions.', 1),
(2, 'Accountant', 'Day-to-day accounting operations. Cannot manage users or system settings.', 1),
(3, 'Viewer',     'Read-only access to accounts, journals, and reports.',                       1);

-- ============================================================
-- Permissions — 7 modules x 4 CRUD + 3 special
-- ============================================================

INSERT INTO permissions (id, code, module, description) VALUES
-- accounts
( 1, 'accounts.view',   'accounts', 'View chart of accounts'),
( 2, 'accounts.create', 'accounts', 'Create new accounts'),
( 3, 'accounts.edit',   'accounts', 'Edit existing accounts'),
( 4, 'accounts.delete', 'accounts', 'Delete accounts'),

-- journal
( 5, 'journal.view',    'journal',  'View journal entries'),
( 6, 'journal.create',  'journal',  'Create journal entries'),
( 7, 'journal.edit',    'journal',  'Edit draft journal entries'),
( 8, 'journal.delete',  'journal',  'Delete draft journal entries'),
( 9, 'journal.post',    'journal',  'Post journal entries to the ledger'),
(10, 'journal.void',    'journal',  'Void posted journal entries'),

-- reports
(11, 'reports.view',    'reports',  'View financial reports'),
(12, 'reports.create',  'reports',  'Create custom reports'),
(13, 'reports.edit',    'reports',  'Edit custom reports'),
(14, 'reports.delete',  'reports',  'Delete custom reports'),

-- banking
(15, 'banking.view',    'banking',  'View bank accounts and transactions'),
(16, 'banking.create',  'banking',  'Create bank transactions and imports'),
(17, 'banking.edit',    'banking',  'Edit bank transactions'),
(18, 'banking.delete',  'banking',  'Delete bank transactions'),

-- invoices
(19, 'invoices.view',   'invoices', 'View invoices'),
(20, 'invoices.create', 'invoices', 'Create invoices'),
(21, 'invoices.edit',   'invoices', 'Edit invoices'),
(22, 'invoices.delete', 'invoices', 'Delete invoices'),

-- users
(23, 'users.view',      'users',    'View user list and profiles'),
(24, 'users.create',    'users',    'Create new users'),
(25, 'users.edit',      'users',    'Edit user profiles and roles'),
(26, 'users.delete',    'users',    'Deactivate or delete users'),

-- settings
(27, 'settings.view',   'settings', 'View system settings'),
(28, 'settings.create', 'settings', 'Create configuration entries'),
(29, 'settings.edit',   'settings', 'Edit system settings'),
(30, 'settings.delete', 'settings', 'Delete configuration entries'),

-- special
(31, 'period.close',    'journal',  'Close an accounting period');

-- ============================================================
-- Role -> Permission mappings
-- ============================================================

-- Admin: every permission
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Accountant: all except users.* and settings.*
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions
WHERE module NOT IN ('users', 'settings');

-- Viewer: only *.view permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions
WHERE code LIKE '%.view';
