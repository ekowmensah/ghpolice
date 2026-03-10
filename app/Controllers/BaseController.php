<?php

namespace App\Controllers;

class BaseController
{
    protected array $data = [];
    
    protected function view(string $view, array $data = []): string
    {
        return view($view, array_merge($this->data, $data));
    }
    
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect(string $url): void
    {
        header("Location: " . url($url));
        exit;
    }
    
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
        header("Location: " . $referer);
        exit;
    }
    
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);
            
            foreach ($ruleList as $r) {
                if ($r === 'required' && empty($data[$field])) {
                    $errors[$field] = ucfirst($field) . ' is required';
                    break;
                }
                
                if (str_starts_with($r, 'min:')) {
                    $min = (int)substr($r, 4);
                    if (strlen($data[$field] ?? '') < $min) {
                        $errors[$field] = ucfirst($field) . " must be at least {$min} characters";
                        break;
                    }
                }
                
                if (str_starts_with($r, 'max:')) {
                    $max = (int)substr($r, 4);
                    if (strlen($data[$field] ?? '') > $max) {
                        $errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
                        break;
                    }
                }
                
                if ($r === 'email' && !empty($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email address';
                    break;
                }
            }
        }
        
        return $errors;
    }
    
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }
    
    protected function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
}
