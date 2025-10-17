# Finance Planner

Finance Planner is a single-user demo application that helps track wallets, categories, budgets, and financial transactions. The project is built with Laravel 12, PHP 8.2, MySQL, Blade templates, TailwindCSS (via CDN), and Chart.js.

## Features

- Dashboard with monthly income, expense, net balance, wallet balances, and quick charts.
- Transaction management with filters, CSV export, and CRUD operations.
- Category and wallet management with color coding.
- Budget planning with month-normalised targets and spending insights.
- Reports with weekly summaries, category and wallet breakdowns.
- IDR currency formatting helper (@idr directive).
- Seeders with realistic demo data.

## Getting started

1. Install PHP 8.2, Composer, MySQL, and Node (optional for tooling).
2. Install dependencies and set up the environment:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

3. Update the `.env` file with your database credentials and then run migrations and seeders:

```bash
php artisan migrate --seed
```

4. Start the development server:

```bash
php artisan serve
```

5. Visit [http://localhost:8000](http://localhost:8000) to explore the dashboard.

## Testing

Run the automated test suite (if desired) with:

```bash
php artisan test
```

## Screenshots

Add your screenshots in this section to showcase the dashboard and reports UI.

---

Finance Planner is provided as a demo. Feel free to extend it with authentication, multi-user support, or API endpoints.
