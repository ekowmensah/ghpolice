<?php

namespace App\Controllers;

use App\Models\User;
use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;
    
    public function __construct()
    {
        $this->authService = new AuthService();
    }
    
    public function loginForm(): string
    {
        return view('auth/login', [
            'title' => 'Login - GHPIMS'
        ]);
    }
    
    public function login(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token. Please try again.');
            $this->redirect('/login');
        }
        
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $errors = $this->validate([
            'username' => $username,
            'password' => $password
        ], [
            'username' => 'required',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old'] = ['username' => $username];
            $this->redirect('/login');
        }
        
        $result = $this->authService->login($username, $password);
        
        if ($result['success']) {
            $_SESSION['user'] = $result['user'];
            $_SESSION['last_activity'] = time();
            
            // Redirect to intended URL or dashboard
            $intendedUrl = $_SESSION['intended_url'] ?? '/dashboard';
            unset($_SESSION['intended_url']);
            $this->redirect($intendedUrl);
        } else {
            $this->setFlash('error', $result['message']);
            $_SESSION['old'] = ['username' => $username];
            $this->redirect('/login');
        }
    }
    
    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
    
    /**
     * Show forgot password form
     */
    public function forgotPasswordForm(): string
    {
        return $this->view('auth/forgot-password', [
            'title' => 'Forgot Password'
        ]);
    }
    
    public function forgotPassword(): void
    {
        if (!verify_csrf()) {
            $this->setFlash('error', 'Invalid security token. Please try again.');
            $this->redirect('/forgot-password');
        }
        
        $email = $_POST['email'] ?? '';
        
        $errors = $this->validate(['email' => $email], ['email' => 'required|email']);
        
        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $this->redirect('/forgot-password');
        }
        
        $result = $this->authService->requestPasswordReset($email);
        
        $this->setFlash('success', 'If the email exists, a password reset link has been sent.');
        $this->redirect('/login');
    }
}
