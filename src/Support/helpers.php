<?php
declare(strict_types=1);

function app_bootstrap(): void
{
    require_once __DIR__ . '/config.php';
    date_default_timezone_set(TIMEZONE);
    foreach ([STORAGE_DIR, REPORTS_DIR, CACHE_DIR, BACKUPS_DIR] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path): string
{
    return '/' . ltrim($path, '/');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function current_url_with(array $params = []): string
{
    $query = array_merge($_GET, $params);
    foreach ($query as $k => $v) {
        if ($v === null || $v === '') unset($query[$k]);
    }
    return strtok($_SERVER['REQUEST_URI'] ?? '', '?') . (empty($query) ? '' : '?' . http_build_query($query));
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function is_member(): bool
{
    return !empty($_SESSION['member_logged_in']);
}

function member_plan(): string
{
    return $_SESSION['member_plan'] ?? 'free';
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function format_ar_date(?string $date): string
{
    if (!$date) return '';
    $ts = strtotime($date);
    if (!$ts) return $date;
    return date('d/m/Y', $ts);
}

function report_status_badge(string $access): string
{
    return $access === 'premium' ? 'premium' : 'free';
}

function report_access_label(string $access): string
{
    return $access === 'premium' ? 'للأعضاء' : 'مجاني';
}

function signal_class(string $signal): string
{
    $signal = trim($signal);
    return match($signal) {
        'صاعد', 'إيجابي', 'Bullish' => 'bullish',
        'هابط', 'سلبي', 'Bearish' => 'bearish',
        'مختلط', 'Mixed' => 'mixed',
        default => 'neutral',
    };
}

function parse_lines(?string $text): array
{
    $text = (string) $text;
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = trim($text);
    if ($text === '') {
        return [];
    }
    $lines = explode("\n", $text);
    return array_values(array_filter(array_map('trim', $lines), fn($line) => $line !== ''));
}

function parse_diagnostic_rows(?string $text): array
{
    $rows = [];
    foreach (parse_lines($text) as $line) {
        // Limit to 4 parts so price_impact can safely contain the | character.
        $parts = array_map('trim', explode('|', $line, 4));
        if (count($parts) < 4) continue;
        $rows[] = [
            'indicator' => $parts[0],
            'current_reading' => $parts[1],
            'signal' => $parts[2],
            'price_impact' => $parts[3],
        ];
    }
    return $rows;
}

function stringify_diagnostic_rows(array $rows): string
{
    $lines = [];
    foreach ($rows as $row) {
        $lines[] = implode(' | ', [
            $row['indicator'] ?? '',
            $row['current_reading'] ?? '',
            $row['signal'] ?? '',
            $row['price_impact'] ?? '',
        ]);
    }
    return implode("
", $lines);
}

function report_excerpt(string $text, int $width = 180): string
{
    if (function_exists('mb_strimwidth')) {
        return trim((string) mb_strimwidth($text, 0, $width, '...'));
    }
    return strlen($text) > $width ? trim(substr($text, 0, $width - 3)) . '...' : trim($text);
}

function report_visibility_class(string $access): string
{
    return $access === 'premium' ? 'is-premium' : 'is-free';
}

function can_view_report(array $report): bool
{
    return ($report['access'] ?? 'free') !== 'premium' || is_member() || is_admin();
}

function admin_flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['admin_flash'] = ['message' => $message, 'type' => $type];
        return null;
    }
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);
    return $flash;
}

function preview_store(array $payload): string
{
    $token = bin2hex(random_bytes(16));
    $_SESSION['report_previews'][$token] = [
        'payload' => $payload,
        'created_at' => time(),
    ];
    return $token;
}

function preview_get(string $token): ?array
{
    $item = $_SESSION['report_previews'][$token] ?? null;
    if (!$item || !is_array($item)) return null;
    if (($item['created_at'] ?? 0) < time() - 7200) {
        unset($_SESSION['report_previews'][$token]);
        return null;
    }
    return is_array($item['payload'] ?? null) ? $item['payload'] : null;
}
