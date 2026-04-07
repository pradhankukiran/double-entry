-- ============================================================
-- Default admin user — CHANGE PASSWORD ON FIRST LOGIN
-- ============================================================

INSERT INTO users (id, email, password_hash, first_name, last_name, is_active)
VALUES (
    1,
    'admin@double-e.com',
    '$2y$12$LJ3m4ys3ZwjYrSPRVp.F2eV5f7E6f2Z8wQ6IZhGPJ5v5z5Y5Y5Y5Y',
    'System',
    'Administrator',
    1
);

-- Assign Admin role
INSERT INTO user_roles (user_id, role_id)
VALUES (1, 1);
