<?php

use App\Config\App;

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        return App::config($key, $default);
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return App::baseUrl($path);
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('static/' . ltrim($path, '/'));
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = []): string
    {
        extract($data);
        
        $viewPath = __DIR__ . '/../../views/' . str_replace('.', '/', $view) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new RuntimeException("View not found: {$view}");
        }
        
        ob_start();
        include $viewPath;
        return ob_get_clean();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header("Location: " . url($url));
        exit;
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = '')
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        $name = config('csrf.token_name');
        return "<input type='hidden' name='{$name}' value='{$token}'>";
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf(): bool
    {
        $name = config('csrf.token_name');
        $token = $_POST[$name] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('auth_id')) {
    function auth_id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in(): bool
    {
        return isset($_SESSION['user']);
    }
}

if (!function_exists('has_role')) {
    function has_role(string $role): bool
    {
        return ($_SESSION['user']['role_name'] ?? '') === $role;
    }
}

if (!function_exists('can')) {
    function can(string $permission): bool
    {
        $permissions = $_SESSION['user']['permissions'] ?? [];
        return in_array($permission, $permissions);
    }
}

if (!function_exists('sanitize')) {
    function sanitize(?string $data): string
    {
        if ($data === null) {
            return '';
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    }
}

if (!function_exists('logger')) {
    function logger(string $message, string $level = 'info'): void
    {
        $logFile = __DIR__ . '/../../' . config('log.file');
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}

if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'Y-m-d'): string
    {
        if (!$date) return '';
        return date($format, strtotime($date));
    }
}

if (!function_exists('time_ago')) {
    function time_ago(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;
        
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        if ($diff < 604800) return floor($diff / 86400) . ' days ago';
        
        return date('M d, Y', $timestamp);
    }
}

if (!function_exists('money_format')) {
    function money_format(float $amount, string $currency = 'GHS'): string
    {
        return $currency . ' ' . number_format($amount, 2);
    }
}
