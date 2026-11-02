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
  <img src="https://img.shields.io/badge/Cloud-Microsoft_Azure-0078D4?style=flat-square&logo=microsoftazure&logoColor=white">
  <img src="https://img.shields.io/badge/Region-Malaysia_West-008080?style=flat-square">
  <img src="https://img.shields.io/badge/Email-Resend_Verified-000000?style=flat-square&logo=resend&logoColor=white">
  <img src="https://img.shields.io/badge/Security-SSL_Let's_Encrypt-10B981?style=flat-square&logo=letsencrypt&logoColor=white">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

## 🌐 Live Cloud Deployment

* **Production URL:** [https://mysubshield.me](https://mysubshield.me)
* **Alternate URL:** [https://www.mysubshield.me](https://www.mysubshield.me)
* **Cloud Infrastructure:** Microsoft Azure Virtual Machine (Ubuntu 24.04 LTS, Nginx, PHP 8.3-FPM, MySQL 8)
* **Datacenter Region:** `(Asia Pacific) Malaysia West`
* **Custom Domain & DNS:** Registered via Namecheap, verified on GitHub Student Pack & Resend
* **SSL Certificate:** Automated TLS 1.3 encryption via Let's Encrypt (Certbot auto-renewing)

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
|                       https://mysubshield.me (SSL / HTTPS)                        |
+------------------------------------------+----------------------------------------+
                                           |
                                     (HTTPS Port 443)
                                           v
+-----------------------------------------------------------------------------------+
|                        Microsoft Azure Virtual Machine                            |
|                        Region: Malaysia West (Ubuntu 24.04)                       |
|                                                                                   |
|  [ Nginx Reverse Proxy ] ──> [ PHP 8.3-FPM FastCGI ] ──> [ Laravel 13 Core ]      |
|                                                                    |              |
|                                                             (Port 3306)           |
|                                                                    v              |
|                                                          [ MySQL 8 Database ]     |
|                                                                                   |
|  [ Linux Crontab ] ──(Every 1 min)──> `php artisan schedule:run`                  |
|     ├── Daily 08:00 AM: `subscriptions:remind --days=3`                           |
|     └── Every 6 Hours:  `fx:refresh`                                              |
|                                                                                   |
|  [ Systemd Daemon ] ──> `php artisan queue:work` (subshield-worker.service)       |
+------------------------------------------+----------------------------------------+
                                           |
                                     (SMTP Port 465)
                                           v
+-----------------------------------------------------------------------------------+
|                        Resend Cloud Email Infrastructure                          |
|         Verified Domain: mysubshield.me (DKIM, SPF, DMARC Cryptographic Auth)    |
|                      From: notifications@mysubshield.me                           |
+-----------------------------------------------------------------------------------+
```

---

## Automated Cloud Background Processing

SubShield runs autonomously in the cloud without terminal prompts:

1. **System Crontab (`/etc/cron.d/`):**
   ```bash
   * * * * * cd /var/www/subscription-tracker && php artisan schedule:run >> /dev/null 2>&1
   ```
2. **Scheduled Tasks (`routes/console.php`):**
   * **`fx:refresh`** — Refreshes FX exchange rates every 6 hours and re-normalizes all active subscription amounts.
   * **`subscriptions:remind --days=3`** — Fires daily at `08:00` (Asia/Kuala_Lumpur) to notify users of subscriptions renewing within 72 hours.
3. **Queue Daemon (`/etc/systemd/system/subshield-worker.service`):**
   * Manages background notification jobs, welcomes, and OTPs with automatic restarts and zero downtime.

---

## Tech Stack

| Component | Technology | Version / Specification |
|---|---|---|
| **Backend Framework** | Laravel | 13.x |
| **Language Runtime** | PHP | 8.3 (PHP-FPM) |
| **Database Engine** | MySQL | 8.0 / 8.4 InnoDB |
| **Web Server** | Nginx | 1.24+ |
| **Cloud Provider** | Microsoft Azure | Standard_B2als_v2 (2 vCPUs, 4 GB RAM) |
| **Cloud Datacenter** | Azure Malaysia West | Low-latency domestic hosting |
| **Frontend Styling** | Tailwind CSS / Bootstrap | 5.3 + Vite |
| **Data Visualization** | Chart.js | 4.x |
| **Email Infrastructure** | Resend | SMTP Relay (`smtp.resend.com:465`) |
| **SSL / TLS** | Let's Encrypt | Certbot TLS 1.3 Automated Renewal |
| **FX Rate Feed** | ExchangeRate-API | Real-time foreign exchange |

---

## Demo Credentials

You can test the live system directly at **[https://mysubshield.me](https://mysubshield.me)**:

### 1. Pre-Populated Evaluation Account
* **Login URL:** [https://mysubshield.me/login](https://mysubshield.me/login)
* **Email:** `demo@subtracker.test`
* **Password:** `password`
* *Contains 26 pre-loaded subscriptions across diverse international currencies (USD, EUR, SGD, GBP, JPY).*

### 2. Live Registration Walkthrough
1. Navigate to **[https://mysubshield.me/register](https://mysubshield.me/register)**.
2. Enter your real email address.
3. Receive the live **6-digit verification code** sent from `notifications@mysubshield.me`.
4. Enter the code on `/verify-email` to unlock the dashboard and receive your **Welcome Email**.

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
