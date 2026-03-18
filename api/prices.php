<?php
require_once __DIR__ . '/../includes/bootstrap.php';
use HubTrend\Services\MarketDataService;
header('Content-Type: application/json; charset=utf-8');
$service = new MarketDataService();
echo json_encode($service->getSnapshot(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
