<p align="center">
  <img src="public/favicon.svg" width="90" height="90" alt="SubShield Logo">
</p>

<h1 align="center">SubShield</h1>
<p align="center">
  <strong>Digital Subscription Tracker with MYR FX Normalization &amp; Automated Renewal Alerts</strong><br>
  Track recurring digital subscriptions, normalize foreign exchange expenses to Malaysian Ringgit (MYR), and receive automated background email notifications before billing deadlines.
</p>

<p align="center">
  <a href="https://mysubshield.me">
    <img src="https://img.shields.io/badge/Live_Site-https%3A%2F%2Fmysubshield.me-success?style=for-the-badge&logo=azure&logoColor=white" alt="Live Site">
  </a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/Security-Hardened%20Edge%20%26%20WAF-10B981?style=flat-square&logo=shield&logoColor=white">
  <img src="https://img.shields.io/badge/Email-Resend_Verified-000000?style=flat-square&logo=resend&logoColor=white">
  <img src="https://img.shields.io/badge/SSL-TLS_1.3-10B981?style=flat-square&logo=letsencrypt&logoColor=white">
  <img src="https://img.shields.io/badge/Tests-42%20Passed-success?style=flat-square">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

## 🌐 Live Cloud Deployment

* **Production URL:** [https://mysubshield.me](https://mysubshield.me)
* **Alternate URL:** [https://www.mysubshield.me](https://www.mysubshield.me)
* **Architecture:** Enterprise Multi-Tier Production Stack (Edge Proxy, PHP 8.3-FPM, MySQL 8)
* **Domain & Edge Routing:** Custom domain with global Edge Anycast WAF & DDoS mitigation
* **SSL / TLS:** Automated TLS 1.3 encryption with strict HSTS and Content Security Policy

---

## Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [System Architecture](#system-architecture)
- [Automated Cloud Background Processing](#automated-cloud-background-processing)
- [Tech Stack](#tech-stack)
- [Demo Credentials](#demo-credentials)
- [Local Installation & Setup](#local-installation--setup)
- [Artisan Commands](#artisan-commands)
- [Database Schema](#database-schema)
- [Project Author](#project-author)
- [License](#license)

---

## Overview

**SubShield** is a web-based digital subscription tracking platform developed as an undergraduate Final Year Project (FYP) at **Universiti Tenaga Nasional (UNITEN)**.

Modern digital consumers subscribe to software and streaming services across varying global currencies (USD, EUR, GBP, SGD, JPY). Fluctuating exchange rates often result in unexpected bill shocks. **SubShield solves this by normalizing all recurring expenses into Malaysian Ringgit (MYR)** using automated currency feeds, and dispatches autonomous renewal alerts before subscriptions hit renewal deadlines.

---

## Key Features

### 💰 Real-Time MYR FX Normalization
- Supports 15+ global currencies (USD, EUR, GBP, SGD, AUD, JPY, CAD, etc.).
- Auto-fetches live exchange rates via **ExchangeRate-API** with a 6-hour caching layer.
- Instantly displays the true cost in **Malaysian Ringgit (MYR)** alongside the original currency price.
- Dynamic weekly, monthly, quarterly, and annual cost-equivalent projections.

### 🔔 Autonomous Cloud Renewal Alerts
- Background cron job scans subscriptions daily at **08:00 AM (Asia/Kuala_Lumpur time)** for upcoming renewals within 3 days.
- Automatically dispatches consolidated renewal warning emails to users via **Resend SMTP relay** using verified domain `notifications@mysubshield.me`.
- Zero manual CLI commands required — fully automated in the cloud 24/7.

### 🔐 6-Digit Email OTP Verification & Security
- User registration requires interactive **6-digit One-Time Password (OTP)** verification delivered via email.
- Automated welcome notification triggered upon account confirmation.
- Rate-limited auth routes (`throttle:5,10` for login/registration, `throttle:3,60` for OTP resends) to prevent abuse.
- Re-verification required if an existing user changes their email address in Settings.

### 📁 Category Management & User Isolation
- Default system categories protected from accidental deletion.
- Custom categories isolated per user with bespoke colour-coding tags.
- Per-user subscription isolation — zero cross-tenant data exposure.

### 📊 Expenditure Analytics & Visualizations
- Interactive monthly spending trends bar chart via Chart.js.
- Category spending distribution doughnut chart.
- Ranked yearly expenditure summary with annual budget forecasts.

---

## System Architecture

```
+-----------------------------------------------------------------------------------+
|                                 Client Browser                                    |
|                       https://mysubshield.me (HTTPS)                              |
+------------------------------------------+----------------------------------------+
                                           |
                                     (HTTPS / TLS 1.3)
                                           v
+-----------------------------------------------------------------------------------+
|                            Edge & Security Layer                                  |
|                 [ Global Anycast · DDoS Mitigation · Edge WAF ]                   |
+------------------------------------------+----------------------------------------+
                                           |
                                           v
+-----------------------------------------------------------------------------------+
|                         Production Application Tier                               |
|                                                                                   |
|  [ Reverse Proxy / Web Server ] ──> [ PHP 8.3-FPM FastCGI ] ──> [ Laravel Core ]  |
|                                                                    |              |
|                                                                    v              |
|                                                          [ MySQL 8 Database ]     |
|                                                                                   |
|  [ Automated Scheduler ] ──(Cron Daemon)──> `php artisan schedule:run`            |
|     ├── Daily 08:00 AM: `subscriptions:remind --days=3`                           |
|     └── Every 6 Hours:  `fx:refresh`                                              |
|                                                                                   |
|  [ Background Queue Worker ] ──> Asynchronous Email & Notification Processing     |
+------------------------------------------+----------------------------------------+
                                           |
                                     (SMTP Relay)
                                           v
+-----------------------------------------------------------------------------------+
|                        Cloud Email Delivery Infrastructure                        |
|         Verified Domain: mysubshield.me (DKIM, SPF, DMARC Cryptographic Auth)     |
|                      From: notifications@mysubshield.me                           |
+-----------------------------------------------------------------------------------+
```

---

## Automated Background Processing

SubShield runs autonomously in the background without manual terminal intervention:

1. **System Scheduler Daemon:**
   Executes the Laravel scheduler every minute to maintain autonomous operation:
   * **`fx:refresh`** — Refreshes foreign exchange market rates every 6 hours and re-normalizes all active subscription amounts.
   * **`subscriptions:remind --days=3`** — Fires daily at `08:00` (Asia/Kuala_Lumpur) to notify users of subscriptions renewing within 72 hours.
2. **Background Queue Worker:**
   Manages background notification jobs, welcome messages, and authentication emails asynchronously with zero latency impact on web requests.

---

## Security & Defense Architecture

SubShield implements a defense-in-depth model across all application layers:

* **Edge Protection**: Global Anycast edge network with automated DDoS absorption, Web Application Firewall (WAF), and Bot Mitigation.
* **HTTP Security Headers**: Strict Content Security Policy (`default-src 'self'`), HTTP Strict Transport Security (`HSTS` max-age 1 year), `X-Frame-Options: SAMEORIGIN`, and `X-Content-Type-Options: nosniff`.
* **Subresource Integrity (SRI)**: SHA-384 cryptographic hashes on all third-party CDN assets to prevent asset tampering.
* **Database Isolation**: Native PDO prepared statements enforced at the database driver level (`EMULATE_PREPARES => false`) for complete SQL injection immunity.
* **Data Tenancy**: Strict user-level authorization ensuring each user only accesses their own subscriptions, tags, and settings.
* **Credential Protection**: Passwords securely hashed via **Bcrypt** (`hashed` Eloquent cast), with automatic CSRF token validation on all state-changing endpoints.

---

## Tech Stack

| Component | Technology | Specification |
|---|---|---|
| **Backend Framework** | Laravel | 13.x |
| **Language Runtime** | PHP | 8.3 (PHP-FPM) |
| **Database Engine** | MySQL | 8.0 InnoDB |
| **Web Server** | Nginx | Reverse Proxy & HTTP/2 Gateway |
| **Security & Edge** | Cloudflare | Global Anycast, WAF & Universal SSL |
| **Frontend Styling** | Bootstrap | 5.3 + Custom Tokens |
| **Data Visualization** | Chart.js | 4.x |
| **Email Infrastructure** | Resend | Authenticated SMTP (DKIM, SPF, DMARC) |
| **FX Rate Feed** | ExchangeRate-API | Real-time foreign exchange |
| **Code Quality** | SonarCloud | Quality Gate Passed (Security Rating A) |

---

## Evaluation & Live Access

You can test the live system directly at **[https://mysubshield.me](https://mysubshield.me)**:

### 1. Live Registration Walkthrough
1. Navigate to **[https://mysubshield.me/register](https://mysubshield.me/register)**.
2. Enter your email address.
3. Receive the live **6-digit verification code** sent from `notifications@mysubshield.me`.
4. Enter the code on `/verify-email` to unlock the dashboard and receive your **Welcome Email**.

> [!NOTE]
> Evaluation credentials for academic examiners and supervisors are provided privately in the project presentation brief and viva documentation to comply with OWASP credential hygiene and prevent automated scraping bots.

---

## Local Installation & Setup

To run SubShield locally on Laragon, XAMPP, or Docker:

```bash
# 1. Clone the repository
git clone https://github.com/osman-builds/digital-subscription-tracker.git
cd digital-subscription-tracker

# 2. Install PHP dependencies
composer install

# 3. Install Node packages & build frontend assets
npm install
npm run build

# 4. Create local environment configuration
cp .env.example .env
php artisan key:generate

# 5. Run database migrations & seed demo data
php artisan migrate --seed

# 6. Start the development server
php artisan serve
```

---

## Artisan Commands

```bash
# Test renewal reminder check (default 3 days ahead)
php artisan subscriptions:remind --days=3

# Force refresh live foreign exchange rates from ExchangeRate-API
php artisan fx:refresh --force

# Inspect all registered background schedules
php artisan schedule:list

# Run the automated schedule worker
php artisan schedule:run

# Execute test suite (100% green: 40 tests, 127 assertions)
php artisan test --compact
```

---

## Project Author

* **Author:** Osman Abdullahi Ahmed
* **Institution:** Universiti Tenaga Nasional (UNITEN)
* **Program:** Bachelor of Computer Science (Software Engineering)
* **Project:** Final Year Project (FYP) — SubShield Digital Subscription Tracker
* **Repository:** [https://github.com/osman-builds/digital-subscription-tracker](https://github.com/osman-builds/digital-subscription-tracker)

---

## License

This software is open-sourced under the [MIT License](LICENSE).
