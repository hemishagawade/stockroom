# Stockroom

A polished Laravel MVC inventory website for managing material categories, opening balances, and dated inward/outward stock movements. Data is persisted in MySQL.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8+ (or a compatible MariaDB server)

## Setup

1. Create a MySQL database:

   ```sql
   CREATE DATABASE stockroom CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. Copy `.env.example` to `.env` if needed. The included local settings use XAMPP MySQL at `127.0.0.1:3307` with the `root` account and a blank password; adjust `DB_HOST`, `DB_PORT`, `DB_USERNAME`, and `DB_PASSWORD` for your MySQL installation.
3. Install dependencies and initialize the database:

   ```sh
   composer install
   php artisan key:generate
   php artisan migrate --seed
   ```

4. Start the website:

   ```sh
   php artisan serve
   ```

   Open `http://127.0.0.1:8000`.

## Features

- Category create, display, update, and delete; categories in use are protected from deletion.
- Material create, display, update, and soft-delete/restore with generated auto-incrementing IDs.
- Dated inward and outward stock entries; positive and negative quantities are accepted.
- Stock movement history can be displayed, edited, and deleted. The current balance is calculated from opening stock and the signed movement ledger.
- Client-side and server-side validation cover required fields, valid category/material pairing, duplicate names, numeric ranges, material-name characters, and a maximum of two decimal places. Field-level messages are shown beside each input.

## MVC structure

- `app/Models` — Eloquent Category, Material, and StockMovement relationships.
- `app/Http/Controllers` — CRUD actions and validation.
- `resources/views` — server-rendered Blade pages.
- `routes/web.php` — web routes with Laravel CSRF protection.
- `database/migrations` and `database/seeders` — MySQL schema and default categories.

Run the automated feature tests with `php artisan test`. Tests use an in-memory SQLite database and do not require a running MySQL server.

**Time taken to complete:** approximately 30 minutes.
