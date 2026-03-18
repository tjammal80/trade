<?php
declare(strict_types=1);

const SITE_NAME = 'Hub Trend Gold';
const SITE_TAGLINE = 'تقارير الذهب الاحترافية';
const SITE_URL = '';
const SITE_DESCRIPTION = 'منصة عربية لتقارير الذهب: ملخص تنفيذي، تشخيص سوق، سيناريوهات، ومستويات فنية حاسمة.';

const APP_ENV = 'production';
const TIMEZONE = 'Asia/Riyadh';

const ADMIN_USERNAME = 'admin';
const ADMIN_PASSWORD = 'ChangeThisAdminPass!';
const MEMBER_EMAIL = 'member@example.com';
const MEMBER_PASSWORD = 'ChangeThisMemberPass!';
const MEMBER_PLAN = 'premium';

const STORAGE_DIR = __DIR__ . '/../../storage';
const REPORTS_DIR = STORAGE_DIR . '/reports';
const CACHE_DIR = STORAGE_DIR . '/cache';
const BACKUPS_DIR = STORAGE_DIR . '/backups';
const PRICE_CACHE_FILE = CACHE_DIR . '/prices.json';
const PRICE_CACHE_TTL = 300;

const YAHOO_BASE_URL = 'https://query1.finance.yahoo.com/v8/finance/chart/';

const MARKET_SYMBOLS = [
    'xauusd' => ['symbol' => 'GC=F', 'label' => 'COMEX Gold Futures', 'name' => 'XAU/USD / عقود الذهب', 'unit' => '$'],
    'dxy'    => ['symbol' => 'DX-Y.NYB', 'label' => 'US Dollar Index', 'name' => 'مؤشر الدولار', 'unit' => ''],
    'brent'  => ['symbol' => 'BZ=F', 'label' => 'Brent Crude', 'name' => 'نفط برنت', 'unit' => '$'],
    'spx'    => ['symbol' => '^GSPC', 'label' => 'S&P 500', 'name' => 'مؤشر S&P 500', 'unit' => ''],
    'silver' => ['symbol' => 'SI=F', 'label' => 'Silver Futures', 'name' => 'الفضة', 'unit' => '$'],
    'btc'    => ['symbol' => 'BTC-USD', 'label' => 'Bitcoin', 'name' => 'البيتكوين', 'unit' => '$'],
];
