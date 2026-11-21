# FreshProvision — Wholesale & Retail E-Commerce (Pure PHP + SQLite)

A mobile-first provision store e-commerce web app built with **pure PHP** and **SQLite**.  
Supports both **Retail** and **Wholesale** pricing, cart, checkout, orders, and a simple admin panel.

## Features

- 📱 Mobile-app style UI (bottom navigation, sticky header, PWA-ready)
- 🛒 Product catalog with categories (grains, oils, canned goods, etc.)
- 📦 **Retail** vs **Wholesale** pricing (wholesale applies when qty ≥ minimum)
- 🛍️ Session-based cart
- 📋 Checkout with Cash on Delivery / Bank Transfer / POS
- 👤 User registration & login (customer type: retail or wholesale)
- 📦 Order history & status tracking
- ⚙️ Admin panel: manage products, stock, orders & statuses
- 🇳🇬 Naira (₦) currency, Nigerian provision-store product set

## Requirements

- PHP 8.0+ with PDO SQLite extension
- Web server (Apache, Nginx, or PHP built-in server)

## Quick Start (Local)

```bash
cd freshprovision-store
php -S localhost:8080
```

Then open: **http://localhost:8080**

The SQLite database (`data/store.db`) is created automatically on first run and seeded with demo products and users.

## Demo Accounts

| Role     | Email                     | Password    |
|----------|---------------------------|-------------|
| Admin    | admin@freshprovision.ng   | admin123    |
| Customer | customer@example.com      | customer123 |

## Important: Hosting Notes

**Vercel is NOT recommended** for this project.

Vercel is designed for Node.js / Next.js / static sites and serverless functions.  
A traditional multi-page PHP application that uses:
- PHP sessions
- File-based SQLite
- Multiple `.php` pages

…does not work reliably on Vercel.

### Better free hosting options for this PHP app:

1. **Render.com** (recommended) – supports PHP
2. **Railway.app**
3. **Fly.io**
4. **InfinityFree** / **000webhost** (classic free PHP hosting)
5. Any shared hosting with PHP + SQLite support

### Deploy on Render (example)

1. Create a new **Web Service** on Render
2. Connect this GitHub repository
3. Runtime: **PHP**
4. Build command: leave empty or `composer install` (not needed)
5. Start command: `php -S 0.0.0.0:$PORT`
6. Make sure the `data/` folder is writable

## Project Structure

```
freshprovision-store/
├── index.php
├── products.php
├── cart.php / cart_action.php
├── checkout.php
├── orders.php
├── login.php / register.php / account.php
├── admin/
├── includes/
├── assets/
└── data/                  # SQLite DB (auto-created)
```

## How Wholesale Pricing Works

1. User registers or switches to **Wholesale** account type.
2. When quantity ≥ product’s `wholesale_min_qty`, the wholesale price is used.
3. Cart and checkout automatically apply the correct price based on account type + quantity.
