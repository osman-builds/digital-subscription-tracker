# SubShield — FYP Demo Commands Cheat Sheet

Quick-reference commands and credentials for demonstrating **SubShield** during your Final Year Project (FYP) presentation and viva.

---

## 1. Quick Demo Account Credentials

Use these pre-configured credentials to demonstrate the full dashboard immediately without needing email verification:

* **URL**: [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)
* **Email**: `demo@subtracker.test`
* **Password**: `password`
* **Status**: Pre-verified (`email_verified_at` set)
* **Pre-loaded Data**: 26 active subscriptions across 8 categories with USD, EUR, GBP, and SGD multi-currency FX normalisation.

---

## 2. Launching the App Locally

If the server is not already running in Laragon:

```bash
# Start the local PHP development server
php artisan serve

# In a separate terminal, compile frontend assets (if needed)
npm run dev
```

---

## 3. Live Feature Demonstration Commands

### A. Renewal Alert Emails (Proactive Notification Engine)
Demonstrate how SubShield detects upcoming deadlines and sends grouped reminder emails:

```bash
# Check upcoming renewals within the next 3 days (default) and dispatch email
php artisan subscriptions:remind

# Check upcoming renewals within the next 7 days
php artisan subscriptions:remind --days=7
```
> **What to show examiners:** Point to the terminal output table showing the subscription names, renewal dates, and MYR amounts, then show the email received in your Brevo inbox.

---

### B. Foreign Exchange (FX) Normalisation Engine
Demonstrate live rate updates and automatic recalculation of subscription amounts in MYR:

```bash
# Refresh exchange rates and update all subscription MYR amounts
php artisan fx:refresh

# Force a fresh API call (bypasses the 6-hour cache)
php artisan fx:refresh --force
```
> **What to show examiners:** Explain how the 6-hour cache protects the 1,500 free-tier API limit (~120 calls/month), while `--force` allows immediate manual synchronization.

---

### C. Automated Task Scheduler (Cron)
Demonstrate that background tasks are configured to run automatically without manual terminal input:

```bash
# Display the active schedule table and countdowns to next execution
php artisan schedule:list

# Manually trigger a 1-minute schedule cycle (simulates server cron)
php artisan schedule:run

# Run the scheduler continuously in terminal (for local development)
php artisan schedule:work
```

---

### D. Background Queue Worker (Instant Page Loads)
Demonstrate background email dispatching via database queues:

```bash
# Start processing queued email notifications in the background
php artisan queue:work --tries=3
```

---

## 4. Demonstrating Software Quality & Automated Tests

Examiners highly value automated testing and code quality. Run these to show that every critical feature has regression test coverage:

```bash
# Run the entire test suite (100% green — 40 passing tests)
php artisan test --compact

# Run Category Isolation tests (proves system categories are locked and users are isolated)
php artisan test --compact tests/Feature/CategoryTest.php

# Run 6-Digit Email OTP verification tests
php artisan test --compact tests/Feature/Auth/EmailVerificationTest.php

# Run API & Auth Rate Limiting tests (proves bot protection for Brevo quota)
php artisan test --compact tests/Feature/AuthRateLimitTest.php

# Run Settings & preferences tests
php artisan test --compact tests/Feature/SettingsTest.php
```

---

## 5. Database Diagnostics

Show examiners the underlying schema and database structure:

```bash
# View the migration status and batch execution history
php artisan migrate:status

# Inspect environment and driver configuration
php artisan about
```

---

## 6. Fresh User Walkthrough (For Live Sign-up Demo)

To demonstrate the full user onboarding experience from scratch:

1. Open [http://127.0.0.1:8000/register](http://127.0.0.1:8000/register).
2. Enter your real email address and choose a password.
3. Check your inbox for the **6-digit verification code** sent via Resend (`notifications@mysubshield.me`).
4. Enter the 6 digits on the `/verify-email` screen.
5. Watch the account verify and receive the automated **Welcome Email**.

