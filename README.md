<p align="center">
  <img src="public/favicon.svg" width="90" height="90" alt="SubShield Logo">
</p>

<h1 align="center">SubShield</h1>

<p align="center">
  <strong>Privacy-First Digital Subscription Tracker with Real-Time Multi-Currency Normalisation</strong><br>
  An enterprise-grade, zero-bank-exposure personal subscription intelligence platform featuring automated foreign exchange conversion, proactive renewal notifications, and strict data sovereignty.
</p>

<p align="center">
  <a href="https://mysubshield.me"><img src="https://img.shields.io/badge/Production-mysubshield.me-success?style=for-the-badge&logo=cloudflare&logoColor=white" alt="Live Production"></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/Security-OWASP_Top_10_Hardened-10B981?style=flat-square&logo=shield&logoColor=white" alt="Security Hardened">
  <img src="https://img.shields.io/badge/CI%2FCD-GitHub_Actions-2088FF?style=flat-square&logo=githubactions&logoColor=white" alt="CI/CD">
  <img src="https://img.shields.io/badge/Tests-57%20Passed%20(227%20assertions)-success?style=flat-square" alt="Test Suite">
  <img src="https://img.shields.io/badge/Lighthouse-100%2F100-success?style=flat-square&logo=googlechrome&logoColor=white" alt="Lighthouse 100/100">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=flat-square" alt="License MIT">
</p>

---

## Architecture Overview

```mermaid
flowchart TD
    Client["Client Browser (Desktop / Mobile)"]
    CF["Cloudflare Edge Network\n(TLS 1.3 · Anycast DNS · Edge WAF · Bot Mitigation)"]
    Nginx["Nginx Reverse Proxy\n(HTTP/2 · Security Headers · Gzip Compression)"]
    App["Laravel 13 Application Tier\n(PHP 8.3-FPM · Business Logic · Auth Guard)"]
    DB[("MySQL 8.0 Database\n(InnoDB · 3NF Normalized · Foreign Key Constraints)")]
    FX["ExchangeRate-API\n(6-Hour Cached Resilient Feed)"]
    Email["Resend Email Relay\n(DKIM · SPF · DMARC Cryptographic Delivery)"]

    Client -->|HTTPS / TLS 1.3| CF
    CF -->|Reverse Proxy| Nginx
    Nginx -->|FastCGI| App
    App -->|PDO Prepared Statements| DB
    App -.->|Scheduled FX Sync| FX
    App -.->|Transactional Alerts & OTP| Email
```

---

## Core Features

* **Zero Bank Credential Exposure:** Eliminates the privacy and security vulnerabilities of third-party screen-scraping aggregators (Plaid/Yodlee). Users maintain 100% control over their financial records.
* **Dynamic Multi-Currency Normalisation:** Converts foreign recurring billing (USD, EUR, GBP, SGD, JPY) into the user's preferred local currency (KES, MYR, USD, etc.) in real time with automated 6-hour rate caching.
* **Smart Brand Correction & Asset Sync:** Client-side typo detection automatically corrects misspelled subscription names (e.g., `netfix` $\to$ `Netflix`) and synchronizes official brand favicons.
* **Autonomous Renewal Reminders:** Daily background scheduler detects upcoming renewal deadlines and dispatches consolidated, localized email alerts based on custom lead-time preferences (3-day / 7-day).
* **Data Sovereignty & Injection Neutralization:** One-click CSV export secured against **CWE-1236 (CSV / Formula Injection)**, alongside permanent self-serve account deletion complying with GDPR Article 17 ("Right to be Forgotten").
* **Interactive Expenditure Analytics:** Monthly spending breakdown, category distributions, and yearly budget forecasts powered by Chart.js.

---

## Security Architecture & OWASP Alignment

| Security Pillar | Technical Implementation |
| :--- | :--- |
| **Access Control (A01)** | Anti-IDOR checks enforced across all controller endpoints (`abort_if($sub->user_id !== auth()->id(), 403)`). |
| **Cryptographic Hygiene (A02)** | Passwords hashed using Bcrypt with work factor 12. Session cookies enforce `Secure`, `HttpOnly`, and `SameSite=Lax`. |
| **Injection Defense (A03)** | 100% PDO prepared statements. CSV exports sanitize leading formula trigger characters (`=`, `+`, `-`, `@`, `\t`, `\r`) with single-quote escaping. |
| **Security Misconfiguration (A05)** | Nginx server tokens suppressed; HTTP response headers enforce strict CSP, HSTS, `X-Frame-Options: SAMEORIGIN`, and `X-Content-Type-Options: nosniff`. |
| **Rate Limiting & Anti-Brute-Force** | Auth endpoints protected with granular throttles (`throttle:5,10` on registration/password reset, `throttle:6,1` on OTP verification). |

---

## Tech Stack

| Component | Technology | Role |
| :--- | :--- | :--- |
| **Backend** | Laravel 13 / PHP 8.3-FPM | Enterprise MVC application framework |
| **Database** | MySQL 8.0 (InnoDB) | Relational persistence with cascade constraints |
| **Frontend** | Blade, Bootstrap 5.3, Chart.js | WCAG AA compliant responsive UI |
| **Edge & CDN** | Cloudflare | Anycast routing, DDoS absorption, SSL termination |
| **Web Server** | Nginx | Reverse proxy, static asset compression, FastCGI cache |
| **Mail Infrastructure** | Resend | Authenticated transactional delivery |

---

## Live Access

The production application is accessible at:

👉 **[https://mysubshield.me](https://mysubshield.me)**

Users can sign up for a free account, complete email verification via interactive 6-digit OTP, and immediately start organizing their digital subscriptions.

---

## Local Development Setup

### Prerequisites

* PHP 8.3 or higher
* Composer
* Node.js (v18+) & NPM
* MySQL 8.0 or MariaDB

### Quickstart

```bash
# 1. Clone the repository
git clone https://github.com/osman-builds/digital-subscription-tracker.git
cd digital-subscription-tracker

# 2. Install PHP and JavaScript dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Run database migrations and seeders
php artisan migrate --seed

# 5. Compile frontend assets
npm run build

# 6. Start the local server
php artisan serve
```

---

## Automated Background Commands

```bash
# Evaluate upcoming renewals and dispatch alerts
php artisan subscriptions:remind --days=3

# Force-refresh foreign exchange market rates
php artisan fx:refresh --force

# Create timestamped JSON database snapshot
php artisan app:backup

# Run test suite
php artisan test --compact
```

---

## Quality & Testing Metrics

* **Automated Unit & Feature Tests:** 57 passed (227 assertions, 0 failures).
* **Code Style:** Formatted and validated against PSR-12 via Laravel Pint.
* **Chrome DevTools Lighthouse Audit:**
  - Accessibility: **100 / 100**
  - Best Practices: **100 / 100**
  - SEO: **100 / 100**
  - Cumulative Layout Shift (CLS): **0.00**

---

## Author

* **Osman Abdullahi Ahmed**  
* Bachelor of Software Engineering (Honours), Universiti Tenaga Nasional (UNITEN)  
* Repository: [osman-builds/digital-subscription-tracker](https://github.com/osman-builds/digital-subscription-tracker)

---

## License

This project is licensed under the [MIT License](LICENSE).
