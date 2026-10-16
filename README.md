<p align="center">
  <img src="public/favicon.svg" width="80" height="80" alt="SubShield Logo">
</p>

<h1 align="center">SubShield</h1>
<p align="center">
  <strong>Digital Subscription Tracker — Built with Laravel 13 &amp; MySQL</strong><br>
  Track every subscription, see your spend in MYR, and never get surprised by a renewal again.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel&logoColor=white">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=flat-square&logo=php&logoColor=white">
  <img src="https://img.shields.io/badge/MySQL-8.4-4479A1?style=flat-square&logo=mysql&logoColor=white">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square">
</p>

---

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Screenshots](#screenshots)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [Configuration](#configuration)
- [FX Currency Setup](#fx-currency-setup)
- [Running the App](#running-the-app)
- [Scheduled Tasks (Cron)](#scheduled-tasks-cron)
- [Artisan Commands](#artisan-commands)
- [Database Schema](#database-schema)
- [Project Structure](#project-structure)
- [Demo Account](#demo-account)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

**SubShield** is a self-hosted digital subscription tracker that helps you manage all your recurring payments in one place. Enter subscriptions in any currency — SubShield automatically converts everything to **Malaysian Ringgit (MYR)** using live FX rates, so you always know your true local spend.

Built with **Laravel 13**, **MySQL**, and **Bootstrap 5**, it runs beautifully on a local **Laragon** stack or any standard LAMP/LEMP server.

---

## Features

### 💰 Spending Intelligence
- **Real-time MYR normalization** — enter USD, EUR, SGD, or 15+ currencies; SubShield converts to MYR automatically
- **Monthly & yearly cost breakdown** — see exactly what each subscription costs per month and per year
- **Cost equivalents** — weekly, monthly, quarterly, and yearly views for every subscription

### 📋 Subscription Management
- Full **CRUD** — add, view, edit, delete subscriptions
- Billing cycles: **Weekly, Monthly, Quarterly, Semi-Annual, Annual**
- Status tracking: **Active, Inactive, Cancelled**
- Category tagging with custom **colour-coded** labels

### 📊 Expenditure Tracker
- **Monthly bar chart** — visualise spending across all 12 months
- **Category doughnut chart** — see where your money goes by category
- **Per-subscription yearly table** — ranked highest to lowest cost
- Year selector to compare across different years

### 🔔 Renewal Alerts
- Dashboard **"Due This Week"** panel — renewals in the next 7 days
- Colour-coded urgency badges (Today / Tomorrow / N days)
- Scheduled background command to log and optionally email reminders

### 🔐 Authentication
- Full **registration & login** with **bcrypt**-hashed passwords (Laravel's `hashed` cast)
- Per-user data isolation — each user only sees their own subscriptions
- Forgot password & password reset flows (via Breeze)
- Remember Me, session management

### 🌍 Live FX Rates
- Fetches rates from **ExchangeRate-API** (free tier: 1,500 requests/month)
- Rates **cached for 6 hours** to stay within free limits
- Automatic **fallback rates** if API key is not set or the request fails
- FX refresh timestamp shown on dashboard

### 🎨 UI & UX
- Clean **sidebar layout** with Bootstrap 5
- Fully **responsive** — works on mobile, tablet, and desktop
- SVG favicon/icon — the SubShield shield emblem
- Flash messages for all CRUD operations
- Search & filter on the subscriptions list

---

## Tech Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.3 |
| Database | MySQL 8.4 |
| Auth | Laravel Breeze (bcrypt) |
| Frontend | Bootstrap 5.3, Bootstrap Icons, Chart.js 4 |
| FX Rates | ExchangeRate-API (free tier) |
| Local Server | Laragon (Windows) |
| Build Tool | Vite |

---

## Screenshots

> The app has the following main screens:

| Screen | Description |
|---|---|
| **Login / Register** | Dark gradient auth pages with SubShield branding |
| **Dashboard** | Stats cards, category doughnut chart, due-soon panel, active subs table |
| **Subscriptions** | Paginated list with search, status & category filters |
| **Add / Edit** | Clean form with currency selector and MYR conversion note |
| **Expenditure** | Monthly bar chart, category doughnut, yearly breakdown table |
| **Categories** | Colour-coded cards with edit/delete modals |

---

## Prerequisites

- [Laragon](https://laragon.org/download/) (includes PHP 8.3, MySQL 8.4, Composer, Node.js)
- Git

> **Note:** SubShield works on any platform with PHP 8.2+, MySQL 8+, Composer, and Node.js. Laragon is recommended for Windows.

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/digital-subscription-tracker.git
cd digital-subscription-tracker

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies and build assets
npm install && npm run build

# 4. Copy environment file
cp .env.example .env

# 5. Generate application key
php artisan key:generate
```

---

## Configuration

Edit `.env` and update these values:

```env
APP_NAME="SubShield"
APP_URL=http://subscription-tracker.test

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subscription_tracker
DB_USERNAME=root
DB_PASSWORD=

# FX API (see section below)
EXCHANGERATE_API_KEY=your_key_here
```

Then create the database and run migrations:

```bash
# Create the MySQL database
mysql -u root -e "CREATE DATABASE subscription_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed with sample data (12 subscriptions + 8 categories + demo user)
php artisan db:seed
```

---

## FX Currency Setup

SubShield uses the **ExchangeRate-API** free tier to fetch live currency rates.

1. Sign up at [https://app.exchangerate-api.com/sign-up](https://app.exchangerate-api.com/sign-up) (free, no credit card)
2. Copy your API key from the dashboard
3. Add it to `.env`:

```env
EXCHANGERATE_API_KEY=your_api_key_here
```

4. Force a fresh fetch:

```bash
php artisan fx:refresh --force
```

> **Without an API key**, SubShield uses hardcoded fallback rates (approximate Oct 2026 values). The app works fully — rates just won't be live.

**Supported currencies:** MYR, USD, EUR, GBP, SGD, AUD, JPY, CNY, HKD, INR, CAD, CHF, KRW, TWD, THB, IDR, PHP

---

## Running the App

**With Laragon** (recommended):
1. Start Laragon → Start All
2. Visit `http://subscription-tracker.test`

**With artisan serve:**
```bash
php artisan serve
# Visit http://localhost:8000
```

---

## Scheduled Tasks (Cron)

SubShield has two background scheduled tasks:

| Command | Schedule | Purpose |
|---|---|---|
| `fx:refresh` | Every 6 hours | Fetches latest FX rates, updates `amount_myr` on all subscriptions |
| `subscriptions:remind --days=3` | Daily at 8:00 AM | Logs renewals due within 3 days |

### Enabling the scheduler

**On Windows (Task Scheduler):**

1. Open **Task Scheduler** → Create Basic Task
2. Set trigger: **Every 1 minute**
3. Action → Start a program:
   - Program: `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe`
   - Arguments: `C:\laragon\www\subscription-tracker\artisan schedule:run`

**On Linux/Mac (crontab):**

```bash
* * * * * cd /path/to/subscription-tracker && php artisan schedule:run >> /dev/null 2>&1
```

Verify the schedule is registered:

```bash
php artisan schedule:list
```

---

## Artisan Commands

```bash
# Refresh FX rates from API and update all subscription MYR amounts
php artisan fx:refresh

# Force-bypass cache and fetch fresh rates
php artisan fx:refresh --force

# Check renewals due in the next 3 days (default)
php artisan subscriptions:remind

# Check renewals due in the next 7 days
php artisan subscriptions:remind --days=7

# Run all scheduled tasks (called by cron every minute)
php artisan schedule:run

# View all registered scheduled tasks
php artisan schedule:list
```

---

## Database Schema

```
users
  id, name, email, password (bcrypt), remember_token, timestamps

categories
  id, name, color (#hex), timestamps

subscriptions
  id
  user_id          → users.id (cascade delete)
  category_id      → categories.id (set null on delete)
  name
  description
  amount           decimal(10,2)  — original price
  currency         char(3)        — e.g. USD, MYR
  amount_myr       decimal(10,2)  — auto-converted MYR equivalent
  billing_cycle    enum: weekly | monthly | quarterly | semi_annual | annual
  start_date       date
  next_billing_date date
  status           enum: active | inactive | cancelled
  website_url
  logo_url
  notes
  timestamps
```

---

## Project Structure

```
app/
  Console/Commands/
    RefreshFxRates.php          ← Artisan: fetch FX + update amount_myr
    SendRenewalReminders.php    ← Artisan: log upcoming renewals
  Http/Controllers/
    DashboardController.php
    SubscriptionController.php
    CategoryController.php
    ExpenditureController.php
  Models/
    User.php
    Subscription.php            ← monthlyAmountMyr(), yearlyAmountMyr(), scopeDueSoon()
    Category.php
  Services/
    FxRateService.php           ← Fetch/cache FX rates, toMyr() conversion

resources/views/
  layouts/app.blade.php         ← Sidebar layout with nav + logout
  auth/login.blade.php          ← Bootstrap 5 login page
  auth/register.blade.php       ← Bootstrap 5 register page
  dashboard.blade.php           ← Stats + charts + due soon
  expenditure/index.blade.php   ← Monthly/yearly tracker with Chart.js
  subscriptions/
    index.blade.php             ← Paginated list with filters
    create.blade.php
    edit.blade.php
    show.blade.php
    _form.blade.php             ← Shared form partial
  categories/index.blade.php    ← Cards with edit/delete modals

database/
  migrations/                   ← All DB migrations
  seeders/
    CategorySeeder.php          ← 8 default categories
    SubscriptionSeeder.php      ← 12 sample subscriptions
    DatabaseSeeder.php          ← Demo user + seeds

routes/
  web.php                       ← Protected + public routes
  auth.php                      ← Breeze auth routes
  console.php                   ← Schedule definitions

public/
  favicon.svg                   ← SubShield SVG icon
```

---

## Demo Account

After seeding (`php artisan db:seed`), a demo account is created:

| Field | Value |
|---|---|
| Email | `demo@subtracker.test` |
| Password | `password` |

The demo account has **12 pre-loaded subscriptions** (Netflix, Spotify, Adobe Creative Cloud, Microsoft 365, Google One, Xbox Game Pass, YouTube Premium, Notion, NY Times, Calm, GitHub Copilot, Disney+) across **8 categories**.

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Commit your changes: `git commit -m "Add: my feature"`
4. Push to your branch: `git push origin feature/my-feature`
5. Open a Pull Request

---

## License

This project is open-source under the [MIT License](LICENSE).

---

<p align="center">Built with ❤️ using Laravel 13 · Bootstrap 5 · MySQL</p>
