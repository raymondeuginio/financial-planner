# Finance Planner

Finance Planner is a lightweight Laravel 12 demo application for tracking income, expenses, budgets, categories, and wallets. It showcases a TailwindCSS (CDN) interface, Blade views, and Chart.js-powered dashboards without authentication.

## Features

- Dashboard summaries for the current month with income, expense, net balance, and per-wallet balances
- Filterable transactions with CSV export, inline color badges, and friendly empty states
- Budget planning per expense category with spent vs remaining insights
- Category and wallet management forms with colour pickers
- Reports view with multi-period charts, weekly breakdowns, and wallet/category tables
- IDR currency formatting helper (`@idr`) available across all templates

## Tech Stack

- Laravel 12 (PHP 8.2)
- MySQL (or compatible SQL database)
- Blade templates with TailwindCSS CDN and Chart.js CDN

## Getting Started

```bash
# Install dependencies
composer install

# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database connection in .env, then run migrations & seeders
php artisan migrate --seed

# Serve the application
php artisan serve
```

The database seeder will populate demo wallets, categories, budgets, and roughly 80 historical transactions so charts and summaries are immediately useful.

## Screenshots

_Add UI screenshots of the dashboard, transactions, and reports views here._

## License

This project is provided for demo purposes under the MIT license.
