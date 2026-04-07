<p align="center">
  <img src="public/assets/images/logo.svg" alt="Double-E" width="80" height="80">
</p>

<h1 align="center">Double-E</h1>

<p align="center">
  <strong>Production-grade double-entry accounting platform built on the LAMP stack.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/Apache-2.4-D22128?style=flat-square&logo=apache&logoColor=white" alt="Apache 2.4">
  <img src="https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap 5.3">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker">
  <img src="https://img.shields.io/badge/License-Apache_2.0-D22128?style=flat-square&logo=apache&logoColor=white" alt="License">
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Lines_of_Code-22%2C693-blue?style=flat-square" alt="Lines of Code">
  <img src="https://img.shields.io/badge/Database_Tables-30-blue?style=flat-square" alt="Tables">
  <img src="https://img.shields.io/badge/SQL_Migrations-17-blue?style=flat-square" alt="Migrations">
</p>

---

## Overview

Double-E is a full-featured double-entry accounting system built entirely on **vanilla PHP** with no framework. It demonstrates production-grade LAMP development: clean MVC architecture, atomic database transactions, role-based access control, and a professional Bootstrap UI.

Every journal entry enforces the fundamental accounting equation &mdash; **debits must equal credits** &mdash; within MySQL transactions using `bcmath` for arbitrary-precision arithmetic.

### Demo Credentials

| Role | Email | Password | Access |
|------|-------|----------|--------|
| **Admin** | admin@double-e.com | admin123 | Full access |
| **Accountant** | sarah@apex-consulting.com | demo123 | Accounting features, no user/settings management |
| **Viewer** | mike@apex-consulting.com | demo123 | Read-only access |

---

## Features

### Core Accounting
- **Double-Entry Journal Entries** &mdash; create, post, and void entries with enforced debit/credit balance
- **Chart of Accounts** &mdash; hierarchical account tree with 5 types (Asset, Liability, Equity, Revenue, Expense) and 13 subtypes
- **General Ledger** &mdash; per-account and full ledger views with running balances
- **Fiscal Year Management** &mdash; periods, locking, and year-end closing wizard that posts closing entries to Retained Earnings

### Financial Reports
- **Trial Balance** &mdash; verify debits equal credits across all accounts
- **Balance Sheet** &mdash; Assets = Liabilities + Equity at a point in time
- **Income Statement** &mdash; Revenue - Expenses = Net Income over a period
- **Cash Flow Statement** &mdash; operating, investing, and financing activities
- **AR/AP Aging Report** &mdash; receivables and payables by 30/60/90+ day buckets
- **PDF Export** &mdash; print-ready reports via HTML-to-PDF rendering
- **CSV Export** &mdash; downloadable data from all major list views

### Accounts Receivable & Payable
- **Invoicing** &mdash; create and post invoices with automatic journal entry generation
- **Bills** &mdash; vendor bills with the same workflow as invoices
- **Payments** &mdash; receive and make payments with multi-invoice allocation
- **Contact Management** &mdash; customers and vendors with addresses and payment terms

### Banking
- **Bank Accounts** &mdash; link GL accounts to bank accounts
- **CSV Import** &mdash; upload bank statements with column mapping
- **Transaction Matching** &mdash; auto-match imported transactions to journal entries
- **Bank Reconciliation** &mdash; interactive reconciliation with AJAX toggle and zero-difference enforcement

### Platform
- **Role-Based Access Control** &mdash; Admin, Accountant, Viewer roles with 31 granular permissions
- **Audit Trail** &mdash; filterable log of every action with before/after diffs
- **Global Search** &mdash; autocomplete search across accounts, contacts, invoices, journal entries, and payments
- **Recurring Transactions** &mdash; scheduled journal entries and invoices with cron processing
- **Tax Management** &mdash; tax rates, groups, and compound tax calculation
- **Dashboard** &mdash; KPI cards, Chart.js revenue/expense chart, and activity feed
- **Settings** &mdash; company information and accounting preferences

---

## Tech Stack

| Layer | Technology | Purpose |
|-------|-----------|---------|
| **OS** | Linux (Debian) | Server environment |
| **Web Server** | Apache 2.4 | mod_rewrite, .htaccess, front controller |
| **Database** | MySQL 8.0 | InnoDB, ACID transactions, prepared statements |
| **Language** | PHP 8.4 | Vanilla MVC, PSR-4 autoloading, strict types |
| **Frontend** | Bootstrap 5.3 | Responsive UI via CDN |
| **Charts** | Chart.js 4.x | Dashboard visualizations |
| **PDF** | Dompdf-ready | Report and invoice PDF generation |
| **Containers** | Docker Compose | One-command local development |

### Architecture

```
Double-E/
├── public/              # Apache DocumentRoot, front controller
├── app/
│   ├── Controllers/     # 18 controllers
│   ├── Models/          # 20 models (thin data access, no Active Record)
│   ├── Services/        # 12 services (business logic layer)
│   ├── Middleware/       # Auth and role middleware
│   ├── Validators/      # Input validation
│   ├── Exceptions/      # Custom exceptions
│   └── Helpers/         # Pagination, currency formatting
├── core/                # Framework: Router, Database, Auth, Session, CSRF, View
├── config/              # App, database, and route configuration
├── views/               # PHP templates with layouts and partials
├── database/
│   ├── migrations/      # 17 SQL migrations
│   └── seeds/           # Account types, chart of accounts, demo data
├── storage/             # Logs, uploads, exports
└── bin/                 # CLI tools (cron processor)
```

**No framework.** The entire MVC framework is hand-written in `core/` &mdash; routing, database abstraction with nested transaction support (savepoints), template rendering, session management, and CSRF protection.

---

## Quick Start

### Prerequisites

- [Docker](https://docs.docker.com/get-docker/) and Docker Compose

### Setup

```bash
# Clone the repository
git clone https://github.com/pradhankukiran/double-entry.git
cd double-entry

# Copy environment file
cp .env.example .env

# Update database host for Docker
# Set DB_HOST=db and DB_PASSWORD=double_e_pass in .env

# Start the stack
docker compose up -d

# Run migrations
for f in database/migrations/*.sql; do
  docker compose exec -T db mysql -uroot -pdouble_e_pass double_e < "$f"
done

# Run seeds (in order)
docker compose exec -T db mysql -uroot -pdouble_e_pass double_e < database/seeds/default_roles.sql
docker compose exec -T db mysql -uroot -pdouble_e_pass double_e < database/seeds/seed_account_types.sql
docker compose exec -T db mysql -uroot -pdouble_e_pass double_e < database/seeds/seed_default_chart_of_accounts.sql
docker compose exec -T db mysql -uroot -pdouble_e_pass double_e < database/seeds/default_admin.sql
docker compose exec -T db mysql -uroot -pdouble_e_pass double_e < database/seeds/demo_data.sql
```

### Access

Open **http://localhost:8080** and log in with any of the demo credentials above.

### Stop

```bash
docker compose down        # Stop containers (data persists)
docker compose down -v     # Stop and remove data volume
```

---

## Database Schema

30 tables across 15 logical layers, all InnoDB with foreign keys and indexes:

| Layer | Tables | Purpose |
|-------|--------|---------|
| Users & RBAC | `users`, `roles`, `permissions`, `role_permissions`, `user_roles` | Authentication and authorization |
| Chart of Accounts | `account_types`, `account_subtypes`, `accounts` | Hierarchical account structure |
| Fiscal | `fiscal_years`, `fiscal_periods` | Period management and locking |
| Journal | `journal_entries`, `journal_entry_lines` | Core double-entry transactions |
| Contacts | `contacts`, `contact_addresses` | Customers and vendors |
| Invoicing | `invoices`, `invoice_lines` | AR and AP documents |
| Payments | `payments`, `payment_allocations` | Payment recording and allocation |
| Banking | `bank_accounts`, `bank_transactions`, `bank_import_batches` | Bank integration |
| Reconciliation | `bank_reconciliations`, `bank_reconciliation_lines` | Statement reconciliation |
| Tax | `tax_rates`, `tax_groups`, `tax_group_rates` | Tax calculation |
| Recurring | `recurring_templates`, `recurring_template_lines` | Scheduled transactions |
| Audit | `audit_log` | Complete audit trail |
| Settings | `settings` | Application configuration |

### Key Constraints

- **Double-entry integrity** enforced in application layer within MySQL transactions
- **Soft deletes only** &mdash; accounting records are voided/reversed, never deleted
- **DECIMAL(15,2)** for all monetary columns
- **bcmath** for all arithmetic (no floating-point errors)
- **UTC timestamps** everywhere

---

## Security

- PDO prepared statements on every query (no raw SQL interpolation)
- Bcrypt password hashing via `password_hash()` / `password_verify()`
- CSRF tokens on all forms with timing-safe comparison (`hash_equals`)
- Session fixation prevention with `session_regenerate_id()`
- HTTP-only, SameSite cookies
- Account lockout after 5 failed login attempts
- XSS prevention via `htmlspecialchars()` output escaping on all user data
- `.htaccess` denies access to hidden files and non-public directories

---

## Project Structure

| Directory | Files | Description |
|-----------|-------|-------------|
| `app/Controllers/` | 18 | Request handling, RBAC enforcement |
| `app/Models/` | 20 | Database queries, data access |
| `app/Services/` | 12 | Business logic (accounting rules, PDF, CSV, search) |
| `app/Middleware/` | 2 | Auth and role gate |
| `app/Validators/` | 1 | Journal entry balance validation |
| `app/Helpers/` | 1 | Pagination |
| `core/` | 10 | Framework (Router, Database, Auth, Session, CSRF, View, etc.) |
| `views/` | 70+ | PHP templates with layouts, partials, and page views |
| `database/migrations/` | 17 | Schema DDL |
| `database/seeds/` | 5 | Default data and demo dataset |

---

## License

Licensed under the [Apache License 2.0](LICENSE).
