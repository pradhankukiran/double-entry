<?php

declare(strict_types=1);

namespace DoubleE\Core;

use DoubleE\Models\AuditLog;
use DoubleE\Models\User;

class Auth
{
    private static ?Auth $instance = null;
    private User $userModel;

    /** @var string[]|null Cached permission codes for the current user */
    private ?array $permissions = null;

    private function __construct()
    {
        $this->userModel = new User();
    }

    public static function getInstance(): static
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    /**
     * Attempt to log a user in with email and password.
     */
    public function login(string $email, string $password): bool
    {
        $user = $this->userModel->findByEmail($email);

        if ($user === null) {
            return false;
        }

        // Check account lock
        if ($this->userModel->isLocked($user)) {
            return false;
        }

        // Check if the account is active
        if (isset($user['is_active']) && !$user['is_active']) {
            return false;
        }

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->userModel->incrementFailedLogin((int) $user['id']);
            return false;
        }

        // Successful login -- update tracking and start authenticated session
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $this->userModel->updateLastLogin((int) $user['id'], $ip);

        // Regenerate session ID to prevent fixation
        session_regenerate_id(true);

        Session::set('user_id', (int) $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_name', trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));

        AuditLog::log('login', 'user', (int) $user['id']);

        return true;
    }

    /**
     * Log the current user out and destroy the session.
     */
    public function logout(): void
    {
        $userId = $this->userId();

        if ($userId !== null) {
            AuditLog::log('logout', 'user', $userId);
        }

        Session::destroy();
        $this->permissions = null;
    }

    /**
     * Check whether a user is currently authenticated.
     */
    public function check(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Get the full user record for the currently authenticated user.
     */
    public function user(): ?array
    {
        $id = $this->userId();
        if ($id === null) {
            return null;
        }

        return $this->userModel->find($id);
    }

    /**
     * Get the current user's ID from the session.
     */
    public function userId(): ?int
    {
        $id = Session::get('user_id');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Determine if the current user holds a specific permission.
     *
     * Permission codes are loaded once per request and cached in memory.
     */
    public function hasPermission(string $permissionCode): bool
    {
        if (!$this->check()) {
            return false;
        }

        // Load permissions once per request
        if ($this->permissions === null) {
            $this->loadPermissions();
        }

        return in_array($permissionCode, $this->permissions, true);
    }

    /**
     * Redirect to the login page if the user is not authenticated.
     */
    public function requireAuth(): void
    {
        if (!$this->check()) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: /login');
            exit;
        }
    }

    /**
     * Halt with a 403 response if the user lacks the given permission.
     */
    public function requirePermission(string $code): void
    {
        $this->requireAuth();

        if (!$this->hasPermission($code)) {
            http_response_code(403);
            include __DIR__ . '/../views/errors/403.php';
            exit;
        }
    }

    /**
     * Load all permission codes for the current user's roles into memory.
     */
    private function loadPermissions(): void
    {
        $this->permissions = [];
        $userId = $this->userId();

        if ($userId === null) {
            return;
        }

        $roles = $this->userModel->getRoles($userId);

        $roleModel = new \DoubleE\Models\Role();
        foreach ($roles as $role) {
            $perms = $roleModel->getPermissions((int) $role['id']);
            foreach ($perms as $perm) {
                $this->permissions[] = $perm['code'];
            }
        }

        $this->permissions = array_unique($this->permissions);
    }
}
