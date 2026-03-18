<?php
require_once __DIR__ . '/../includes/bootstrap.php';
use HubTrend\Services\ReportRepository;
header('Content-Type: application/json; charset=utf-8');
$repo = new ReportRepository();
echo json_encode([
    'meta' => [
        'generated_at' => date('c'),
        'stats' => $repo->stats(),
    ],
    'reports' => $repo->all(true),
    'invalid_reports' => $repo->invalidReports(),
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
