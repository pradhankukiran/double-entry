-- ============================================================
-- Default admin user — CHANGE PASSWORD ON FIRST LOGIN
-- ============================================================

INSERT INTO users (id, email, password_hash, first_name, last_name, is_active)
VALUES (
    1,
    'admin@double-e.com',
    '$2y$12$7gAKiufDJLG1Xp7YTHhtvuUHwQMRzH622t69EjFDpdzx4d9zPBuli',
    'System',
    'Administrator',
    1
);

-- Assign Admin role
INSERT INTO user_roles (user_id, role_id)
VALUES (1, 1);
