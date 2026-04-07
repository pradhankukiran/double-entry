<?php

declare(strict_types=1);

namespace DoubleE\Controllers;

use DoubleE\Core\Response;
use DoubleE\Core\Auth;

class AuthController extends BaseController
{
    /**
     * Show the login form.
     */
    public function showLogin(): Response
    {
        // If already logged in, redirect to dashboard
        if (Auth::getInstance()->check()) {
            return $this->redirect('/');
        }

        return $this->render('auth/login', [
            'pageTitle' => 'Sign In - Double-E',
        ], 'layouts/auth');
    }

    /**
     * Process the login form submission.
     */
    public function login(): Response
    {
        $this->validateCsrf();

        $email = trim((string) $this->request->post('email', ''));
        $password = (string) $this->request->post('password', '');

        if ($email === '' || $password === '') {
            $this->flash('error', 'Email and password are required.');
            return $this->redirect('/login');
        }

        $auth = Auth::getInstance();
        $ip = $this->request->ip();

        if ($auth->login($email, $password, $ip)) {
            $this->flash('success', 'Welcome back.');
            return $this->redirect('/');
        }

        $this->flash('error', 'Invalid email or password.');
        return $this->redirect('/login');
    }

    /**
     * Log the user out and redirect to the login page.
     */
    public function logout(): Response
    {
        $this->validateCsrf();

        Auth::getInstance()->logout();

        // Start a fresh session for flash message
        \DoubleE\Core\Session::start();
        $this->flash('success', 'You have been signed out.');

        return $this->redirect('/login');
    }
}
