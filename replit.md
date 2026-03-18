# Hub Trend Gold v3.3

An Arabic-language professional gold market reports platform (منصة عربية لتقارير الذهب).

## Project Overview

A PHP 8.2 web application for publishing and managing gold market analysis reports. Features include:
- JSON-based report management with validation
- Admin panel for report import/edit/template generation
- Member-gated premium content
- Market data integration (Yahoo Finance API)
- Draft/publish workflow with backups

## Architecture

- **Language**: PHP 8.2
- **Server**: PHP built-in server on port 5000
- **Storage**: File-based (JSON reports in `storage/reports/`)
- **Auth**: Session-based (admin + member roles)
- **No database** — all data stored as JSON files

## Key Paths

- `/admin/index.php` — Admin dashboard
- `/admin/report-edit.php` — Edit/create reports
- `/admin/report-import.php` — Import JSON reports
- `/admin/report-template.php` — Generate report templates
- `/admin/report-history.php` — Report version history
- `/api/health.php` — Health check endpoint
- `/api/reports.php` — Reports API

## Configuration

Main config: `src/Support/config.php`

Default credentials (change immediately):
- Admin: `admin / ChangeThisAdminPass!`
- Member: `member@example.com / ChangeThisMemberPass!`

## Storage Structure

```
storage/
  reports/    # Published JSON report files
  cache/      # Price cache (prices.json)
  backups/    # Auto-backups per report slug
```

## Workflow

- **Start application**: `php -S 0.0.0.0:5000 -t /home/runner/workspace`
- Port: 5000 (webview)
