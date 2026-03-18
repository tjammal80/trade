<?php
declare(strict_types=1);

namespace HubTrend\Services;

class ReportRepository
{
    private array $scanCache = [];

    public function all(bool $publishedOnly = true): array
    {
        return $this->scan($publishedOnly)['valid'];
    }

    public function invalidReports(): array
    {
        return $this->scan(false)['invalid'];
    }

    public function stats(): array
    {
        $scan = $this->scan(false);
        $valid = $scan['valid'];
        return [
            'total' => count($valid) + count($scan['invalid']),
            'valid' => count($valid),
            'invalid' => count($scan['invalid']),
            'published' => count(array_filter($valid, fn($r) => ($r['status'] ?? 'draft') === 'published')),
            'draft' => count(array_filter($valid, fn($r) => ($r['status'] ?? 'draft') === 'draft')),
            'premium' => count(array_filter($valid, fn($r) => ($r['access'] ?? 'free') === 'premium')),
            'free' => count(array_filter($valid, fn($r) => ($r['access'] ?? 'free') !== 'premium')),
            'backups' => $this->backupCount(),
        ];
    }

    public function latestPublished(): ?array
    {
        return $this->all(true)[0] ?? null;
    }

    public function findBySlug(string $slug): ?array
    {
        foreach ($this->all(false) as $item) {
            if (($item['slug'] ?? '') === $slug) return $item;
        }
        return null;
    }

    /**
     * Load a report directly from the filesystem by slug, bypassing validation
     * gating. Use this in the admin edit form so that reports with validation
     * errors can still be opened and corrected instead of silently disappearing.
     */
    public function findRawBySlug(string $slug): ?array
    {
        $path = $this->reportPath($slug);
        if (!is_file($path)) return null;
        $raw = (string) file_get_contents($path);
        $data = json_decode($this->stripBom($raw), true);
        if (!is_array($data)) return null;
        $data = $this->normalize($data);
        // Always ensure slug is set from the filename so it can't be lost.
        if (($data['slug'] ?? '') === '') {
            $data['slug'] = $slug;
        }
        return $data;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        foreach ($this->all(true) as $item) {
            if (($item['slug'] ?? '') === $slug) return $item;
        }
        return null;
    }

    public function adjacent(string $slug): array
    {
        $reports = $this->all(true);
        $prev = $next = null;
        foreach ($reports as $i => $report) {
            if (($report['slug'] ?? '') !== $slug) continue;
            $next = $reports[$i - 1] ?? null;
            $prev = $reports[$i + 1] ?? null;
            break;
        }
        return ['prev' => $prev, 'next' => $next];
    }

    public function validate(array $payload, string $mode = 'auto'): array
    {
        $payload = $this->normalize($payload);
        $errors = [];

        if ($mode === 'auto') {
            $mode = (($payload['status'] ?? 'draft') === 'published') ? 'publish' : 'draft';
        }

        if ($mode !== 'draft' && $mode !== 'publish') {
            $mode = 'publish';
        }

        if (trim((string)($payload['title'] ?? '')) === '') {
            $errors[] = 'الحقل title مطلوب.';
        }

        if (trim((string)($payload['report_date'] ?? '')) === '') {
            $errors[] = 'الحقل report_date مطلوب.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$payload['report_date'])) {
            $errors[] = 'الحقل report_date يجب أن يكون بصيغة YYYY-MM-DD.';
        }

        if (!in_array(($payload['status'] ?? ''), ['draft', 'published'], true)) {
            $errors[] = 'الحقل status يجب أن يكون draft أو published.';
        }

        if (!in_array(($payload['access'] ?? ''), ['free', 'premium'], true)) {
            $errors[] = 'الحقل access يجب أن يكون free أو premium.';
        }

        $diag = $payload['diagnostic_table'] ?? [];
        if (!is_array($diag)) {
            $errors[] = 'الحقل diagnostic_table يجب أن يكون مصفوفة.';
        }

        $levels = $payload['technical_levels'] ?? null;
        if ($levels !== null && !is_array($levels)) {
            $errors[] = 'الحقل technical_levels يجب أن يكون كائنًا/مصفوفة.';
        }

        if ($mode === 'publish') {
            $required = ['executive_summary', 'current_interpretation', 'scenario_1m', 'scenario_3m', 'scenario_6m', 'conclusion'];
            foreach ($required as $field) {
                if (trim((string)($payload[$field] ?? '')) === '') {
                    $errors[] = "الحقل {$field} مطلوب عند النشر.";
                }
            }

            if ($this->textLength((string)($payload['executive_summary'] ?? '')) < 30) {
                $errors[] = 'الملخص التنفيذي قصير جدًا؛ أضف نصًا أوضح قبل النشر.';
            }

            if (!is_array($diag) || count($diag) < 2) {
                $errors[] = 'يجب أن يحتوي diagnostic_table على صفين على الأقل عند النشر.';
            } else {
                foreach ($diag as $idx => $row) {
                    if (!is_array($row)) {
                        $errors[] = "صف التشخيص رقم " . ($idx + 1) . " غير صالح.";
                        continue;
                    }
                    foreach (['indicator', 'current_reading', 'signal', 'price_impact'] as $key) {
                        if (trim((string)($row[$key] ?? '')) === '') {
                            $errors[] = "صف التشخيص رقم " . ($idx + 1) . " ينقصه {$key}.";
                        }
                    }
                }
            }

            foreach (['trigger_map', 'sources'] as $listField) {
                if (!is_array($payload[$listField] ?? null) || count($payload[$listField]) < 1) {
                    $errors[] = "الحقل {$listField} يجب أن يحتوي على عنصر واحد على الأقل عند النشر.";
                }
            }

            $levels = $payload['technical_levels'] ?? null;
            if (!is_array($levels)) {
                $errors[] = 'الحقل technical_levels يجب أن يكون كائنًا/مصفوفة عند النشر.';
            } else {
                foreach (['support', 'resistance', 'bull_confirmation', 'bear_confirmation', 'invalidation'] as $key) {
                    if (trim((string)($levels[$key] ?? '')) === '') {
                        $errors[] = "الحقل technical_levels.{$key} مطلوب عند النشر.";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Save a report. Pass $mode explicitly ('draft' or 'publish') so that the
     * caller's validation intent is honoured without re-deriving it from status.
     * Defaults to 'auto' for backward-compatible callers (e.g. saveFromJson).
     */
    public function save(array $payload, string $mode = 'auto'): string
    {
        $payload = $this->normalize($payload);
        $errors = $this->validate($payload, $mode);
        if (!empty($errors)) {
            throw new \RuntimeException(implode("\n", $errors));
        }

        $slug = $payload['slug'] ?: $this->slugify($payload['title'] ?? 'report');
        $payload['slug'] = $slug;
        $payload['schema_version'] = 2;
        $payload['updated_at'] = date('c');
        if (empty($payload['created_at'])) {
            $payload['created_at'] = date('c');
        }

        $path = $this->reportPath($slug);
        if (is_file($path)) {
            $this->backupFile($slug, $path, 'pre-save');
        }
        $this->writeJsonAtomic($path, $payload);
        $this->clearStatCache();
        return $slug;
    }

    public function saveFromJson(string $json): string
    {
        $json = $this->stripBom($json);
        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            throw new \RuntimeException('ملف JSON غير صالح أو لا يمكن قراءته.');
        }
        return $this->save($payload);
    }

    public function delete(string $slug): void
    {
        $file = $this->reportPath($slug);
        if (is_file($file)) {
            $this->backupFile($slug, $file, 'delete');
            unlink($file);
            $this->clearStatCache();
        }
    }

    public function backupHistory(string $slug): array
    {
        $dir = BACKUPS_DIR . '/' . $slug;
        if (!is_dir($dir)) return [];
        $items = [];
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $items[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => filesize($file) ?: 0,
                'modified_at' => date('c', filemtime($file) ?: time()),
            ];
        }
        usort($items, fn($a, $b) => strcmp($b['modified_at'], $a['modified_at']));
        return $items;
    }

    public function restoreBackup(string $slug, string $backupFileName): void
    {
        $source = BACKUPS_DIR . '/' . $slug . '/' . basename($backupFileName);
        if (!is_file($source)) {
            throw new \RuntimeException('النسخة الاحتياطية المطلوبة غير موجودة.');
        }
        $current = $this->reportPath($slug);
        if (is_file($current)) {
            $this->backupFile($slug, $current, 'pre-restore');
        }
        $json = (string) file_get_contents($source);
        $payload = json_decode($this->stripBom($json), true);
        if (!is_array($payload)) {
            throw new \RuntimeException('ملف النسخة الاحتياطية غير صالح.');
        }
        $payload['slug'] = $slug;
        $this->writeJsonAtomic($current, $payload);
        $this->clearStatCache();
    }

    public function template(string $date = ''): array
    {
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d');
        return [
            'title' => 'تقرير الذهب اليومي: تشخيص السوق والسيناريو السعري',
            'slug' => 'gold-daily-' . $date,
            'report_date' => $date,
            'status' => 'draft',
            'access' => 'premium',
            'executive_summary' => 'أضف هنا ملخصًا تنفيذيًا أوليًا. يمكن ترك بقية الحقول غير مكتملة أثناء المسودة.',
            'diagnostic_table' => [
                ['indicator' => 'XAU/USD', 'current_reading' => 'أضف القراءة الحالية', 'signal' => 'مختلط', 'price_impact' => 'صف الأثر السعري بإيجاز'],
                ['indicator' => 'مؤشر الدولار', 'current_reading' => 'أضف القراءة الحالية', 'signal' => 'محايد', 'price_impact' => 'صف أثر الدولار على الذهب بإيجاز'],
            ],
            'current_interpretation' => '',
            'scenario_1m' => '',
            'scenario_3m' => '',
            'scenario_6m' => '',
            'trigger_map' => ['أضف هنا أول محفز يجب مراقبته'],
            'technical_levels' => [
                'support' => 'أضف مستوى الدعم',
                'resistance' => 'أضف مستوى المقاومة',
                'bull_confirmation' => 'أضف شرط تأكيد الصعود',
                'bear_confirmation' => 'أضف شرط تأكيد الهبوط',
                'invalidation' => 'أضف مستوى أو شرط الإبطال',
            ],
            'conclusion' => '',
            'sources' => ['Reuters', 'World Gold Council'],
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'schema_version' => 2,
        ];
    }

    private function scan(bool $publishedOnly): array
    {
        $key = $publishedOnly ? 'published' : 'all';
        if (isset($this->scanCache[$key])) return $this->scanCache[$key];

        $valid = [];
        $invalid = [];
        foreach (glob(REPORTS_DIR . '/*.json') ?: [] as $file) {
            $raw = (string) file_get_contents($file);
            $data = json_decode($this->stripBom($raw), true);
            if (!is_array($data)) {
                $invalid[] = [
                    'file' => basename($file),
                    'errors' => ['ملف JSON غير قابل للقراءة.'],
                ];
                continue;
            }

            $data = $this->normalize($data);
            $errors = $this->validate($data, 'auto');
            if (!empty($errors)) {
                $invalid[] = [
                    'file' => basename($file),
                    'slug' => $data['slug'] ?? '',
                    'errors' => $errors,
                ];
                continue;
            }
            if ($publishedOnly && ($data['status'] ?? 'draft') !== 'published') continue;
            $valid[] = $data;
        }

        usort($valid, function ($a, $b) {
            $dateCompare = strcmp((string)($b['report_date'] ?? ''), (string)($a['report_date'] ?? ''));
            if ($dateCompare !== 0) return $dateCompare;
            return strcmp((string)($b['updated_at'] ?? ''), (string)($a['updated_at'] ?? ''));
        });

        return $this->scanCache[$key] = ['valid' => $valid, 'invalid' => $invalid];
    }

    private function normalize(array $payload): array
    {
        $payload['title'] = trim((string)($payload['title'] ?? ''));
        $payload['slug'] = trim((string)($payload['slug'] ?? ''));
        $payload['report_date'] = trim((string)($payload['report_date'] ?? ''));
        $payload['status'] = trim((string)($payload['status'] ?? 'draft'));
        $payload['access'] = trim((string)($payload['access'] ?? 'free'));
        $payload['executive_summary'] = trim((string)($payload['executive_summary'] ?? ''));
        $payload['current_interpretation'] = trim((string)($payload['current_interpretation'] ?? ''));
        $payload['scenario_1m'] = trim((string)($payload['scenario_1m'] ?? ''));
        $payload['scenario_3m'] = trim((string)($payload['scenario_3m'] ?? ''));
        $payload['scenario_6m'] = trim((string)($payload['scenario_6m'] ?? ''));
        $payload['conclusion'] = trim((string)($payload['conclusion'] ?? ''));
        $payload['diagnostic_table'] = array_values(array_filter($payload['diagnostic_table'] ?? [], fn($row) => is_array($row)));
        $payload['trigger_map'] = array_values(array_filter(array_map('trim', $payload['trigger_map'] ?? []), fn($v) => $v !== ''));
        $payload['sources'] = array_values(array_filter(array_map('trim', $payload['sources'] ?? []), fn($v) => $v !== ''));
        $payload['technical_levels'] = array_merge([
            'support' => '',
            'resistance' => '',
            'bull_confirmation' => '',
            'bear_confirmation' => '',
            'invalidation' => '',
        ], is_array($payload['technical_levels'] ?? null) ? $payload['technical_levels'] : []);
        $payload['created_at'] = trim((string)($payload['created_at'] ?? ''));
        $payload['updated_at'] = trim((string)($payload['updated_at'] ?? ''));
        return $payload;
    }

    private function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\-\s]+/i', '', $text) ?: 'report';
        $text = preg_replace('/[\s\-]+/', '-', $text) ?: 'report';
        return trim($text, '-') ?: 'report';
    }

    private function reportPath(string $slug): string
    {
        return REPORTS_DIR . '/' . $slug . '.json';
    }

    private function backupFile(string $slug, string $sourcePath, string $reason): void
    {
        if (!is_file($sourcePath)) return;
        $dir = BACKUPS_DIR . '/' . $slug;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        // Include microseconds so rapid successive saves never overwrite each
        // other's backup file (e.g. two saves within the same second).
        $stamp = date('Ymd-His') . '-' . substr((string) microtime(false), 2, 4);
        $target = $dir . '/' . $stamp . '-' . $reason . '.json';
        @copy($sourcePath, $target);
    }

    private function writeJsonAtomic(string $path, array $payload): void
    {
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($encoded === false || trim((string)$encoded) === '') {
            throw new \RuntimeException('فشل تحويل التقرير إلى JSON صالح قبل الحفظ.');
        }
        $temp = $path . '.tmp';
        $written = @file_put_contents($temp, $encoded, LOCK_EX);
        if ($written === false || $written < 10) {
            @unlink($temp);
            throw new \RuntimeException('تعذر كتابة الملف المؤقت. تأكد من صلاحيات الكتابة أو من صحة المحتوى.');
        }
        if (!@rename($temp, $path)) {
            @unlink($temp);
            throw new \RuntimeException('تعذر استبدال ملف التقرير بعد الحفظ.');
        }
    }

    private function clearStatCache(): void
    {
        $this->scanCache = [];
    }

    private function stripBom(string $text): string
    {
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            return substr($text, 3);
        }
        return $text;
    }

    private function textLength(string $text): int
    {
        if (function_exists('mb_strlen')) {
            return (int) mb_strlen($text);
        }
        return strlen($text);
    }

    private function backupCount(): int
    {
        $count = 0;
        foreach (glob(BACKUPS_DIR . '/*/*.json') ?: [] as $file) {
            $count++;
        }
        return $count;
    }
}
