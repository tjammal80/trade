<?php
declare(strict_types=1);

namespace HubTrend\Services;

class MarketDataService
{
    public function getSnapshot(): array
    {
        if ($cached = $this->loadFreshCache()) {
            return $cached;
        }

        $data = [];
        $errors = [];
        foreach (MARKET_SYMBOLS as $key => $meta) {
            $quote = $this->fetchQuote($meta['symbol']);
            if (!$quote) {
                $errors[] = "Failed to fetch {$key}";
                continue;
            }
            $data[$key] = [
                'name' => $meta['name'],
                'label' => $meta['label'],
                'symbol' => $meta['symbol'],
                'unit' => $meta['unit'],
                'price' => $quote['price'],
                'change' => $quote['change'],
                'change_pct' => $quote['change_pct'],
                'as_of' => $quote['as_of'],
            ];
        }

        $snapshot = [
            'generated_at' => date('c'),
            'status' => empty($errors) ? 'ok' : (empty($data) ? 'error' : 'partial'),
            'errors' => $errors,
            'data' => $data,
        ];

        if (!empty($data)) {
            file_put_contents(PRICE_CACHE_FILE, json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        if (empty($data) && is_file(PRICE_CACHE_FILE)) {
            $fallback = json_decode((string) file_get_contents(PRICE_CACHE_FILE), true);
            if (is_array($fallback)) {
                $fallback['status'] = 'stale-cache';
                $fallback['errors'] = array_merge($fallback['errors'] ?? [], $errors);
                return $fallback;
            }
        }

        return $snapshot;
    }

    private function loadFreshCache(): ?array
    {
        if (!is_file(PRICE_CACHE_FILE)) return null;
        if ((time() - filemtime(PRICE_CACHE_FILE)) > PRICE_CACHE_TTL) return null;
        $json = json_decode((string) file_get_contents(PRICE_CACHE_FILE), true);
        return is_array($json) ? $json : null;
    }

    private function fetchQuote(string $symbol): ?array
    {
        $url = YAHOO_BASE_URL . rawurlencode($symbol) . '?interval=1d&range=5d';
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 2,
                'header' => "User-Agent: Mozilla/5.0\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) return null;
        $json = json_decode($raw, true);
        $result = $json['chart']['result'][0] ?? null;
        if (!$result) return null;
        $meta = $result['meta'] ?? [];
        $close = $result['indicators']['quote'][0]['close'] ?? [];
        $timestamps = $result['timestamp'] ?? [];

        $filtered = array_values(array_filter($close, fn($v) => $v !== null));
        if (count($filtered) < 1) return null;
        $last = (float) end($filtered);
        $prev = count($filtered) > 1 ? (float) $filtered[count($filtered) - 2] : $last;
        $change = $last - $prev;
        $changePct = $prev != 0.0 ? round(($change / $prev) * 100, 2) : 0.0;
        $asOf = !empty($timestamps) ? date('c', (int) end($timestamps)) : date('c');

        return [
            'price' => round($last, 2),
            'change' => round($change, 2),
            'change_pct' => $changePct,
            'as_of' => $asOf,
        ];
    }
}
