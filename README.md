# Airwaves Wholesalers ERP & POS

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
- Email: admin@airwaves.co.ke
- Password: password

## Modules included
Dashboard, Products, Categories, Brands, Units, Customers, Suppliers, POS, Sales, Purchases, Inventory, Expenses, Reports, Settings.