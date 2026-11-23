# Waves POS

Laravel-based ERP/POS for Airwaves Wholesalers (Kenya).

## Setup
1. Ensure XAMPP (PHP 8.5, MySQL/MariaDB) is running.
2. Create database: `airwaves_erp`.
3. Copy `.env.example` to `.env` and configure DB credentials (done).
4. Run `php artisan key:generate`
5. Run `php artisan migrate --force`
6. Run `php artisan db:seed --force`
7. Run `npm install && npm run build` (optional, assets pre-built)
8. Serve: `php artisan serve` or point Apache to `public/`.

## Default admin

Set `SEED_ADMIN_EMAIL` and `SEED_ADMIN_PASSWORD` in `.env` before running `php artisan db:seed`. If `SEED_ADMIN_PASSWORD` is empty, the seeder generates a random password and prints it once during seeding.

- Email: value of `SEED_ADMIN_EMAIL` (defaults to `admin@airwaves.co.ke`)
- Password: value of `SEED_ADMIN_PASSWORD`

## Payments

Subscription payments are arranged over WhatsApp. A platform admin sets the number in **Platform Console → WhatsApp**. Use international format (e.g. `254712345678`) or a local number such as `0712345678`; local numbers are converted with the country code field (defaults to `254`). When no number is saved, WhatsApp buttons stay hidden.

## Modules included
Dashboard, Products, Categories, Brands, Units, Customers, Suppliers, POS, Sales, Purchases, Inventory, Expenses, Reports, Settings.