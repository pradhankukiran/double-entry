<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Models\User;
use DoubleE\Core\Auth;

class UserController extends BaseController
{
    private User $userModel;

    public function __construct(\DoubleE\Core\Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->userModel = new User();
    }

    /**
     * List all users (admin view).
     */
    public function index(): Response
    {
        $users = $this->userModel->findAll([], 'created_at DESC');

        // Attach role information to each user
        foreach ($users as &$user) {
            $roles = $this->userModel->getRoles((int) $user['id']);
            $user['roles'] = $roles;
            $user['role_name'] = !empty($roles) ? $roles[0]['name'] : 'No Role';
        }
        unset($user);

        return $this->render('users/index', [
            'pageTitle' => 'User Management',
            'users' => $users,
        ]);
    }

    /**
     * Show the current user's profile.
     */
    public function profile(): Response
    {
        $auth = Auth::getInstance();
        $user = $this->userModel->find($auth->id());

        return $this->render('users/profile', [
            'pageTitle' => 'My Profile',
            'user' => $user,
        ]);
    }

    /**
     * Update the current user's profile fields.
     */
    public function updateProfile(): Response
    {
        $this->validateCsrf();

        $auth = Auth::getInstance();
        $userId = $auth->id();

        $firstName = trim((string) $this->request->post('first_name', ''));
        $lastName = trim((string) $this->request->post('last_name', ''));
        $email = trim((string) $this->request->post('email', ''));
        $phone = trim((string) $this->request->post('phone', ''));

        // Validation
        $errors = [];

        if ($firstName === '') {
            $errors[] = 'First name is required.';
        }

        if ($lastName === '') {
            $errors[] = 'Last name is required.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'A valid email address is required.';
        }

        // Check if email is already taken by another user
        if ($email !== '') {
            $existing = $this->userModel->findByEmail($email);
            if ($existing !== null && (int) $existing['id'] !== $userId) {
                $errors[] = 'This email address is already in use.';
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            return $this->redirect('/profile');
        }

        $this->userModel->update($userId, [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
        ]);

        $this->flash('success', 'Profile updated successfully.');
        return $this->redirect('/profile');
    }
}
