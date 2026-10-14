# FoodSave: Online Food Waste Management System

foodsave-final is a food donation application that connects available food with recipients.

![PHP](https://img.shields.io/badge/PHP-2563EB?style=flat-square) ![MySQL](https://img.shields.io/badge/MySQL-0F172A?style=flat-square)

## Overview

The data model covers `admins`, `donation_requests`, `feedback`, `food_donations`, `food_safety_guidelines`, `pickups`, `users`.

## Application areas

| Area | Pages in the repository |
| --- | --- |
| Admin | [`admin/add_user.php`](admin/add_user.php), [`admin/admin_add_guideline.php`](admin/admin_add_guideline.php), [`admin/admin_home.php`](admin/admin_home.php), [`admin/admin_login.php`](admin/admin_login.php), [`admin/admin_navbar.php`](admin/admin_navbar.php) |
| Donor | [`donor/add_donation.php`](donor/add_donation.php), [`donor/donor_dashboard.php`](donor/donor_dashboard.php), [`donor/donor_requests.php`](donor/donor_requests.php), [`donor/edit_donation.php`](donor/edit_donation.php), [`donor/generate_receipt.php`](donor/generate_receipt.php) |
| Root Pages | [`login.php`](login.php), [`register.php`](register.php), [`view_guidelines_user.php`](view_guidelines_user.php) |
| Ngo | [`ngo/feedback.php`](ngo/feedback.php), [`ngo/generate_receipt.php`](ngo/generate_receipt.php), [`ngo/manage_pickup.php`](ngo/manage_pickup.php), [`ngo/ngo_dashboard.php`](ngo/ngo_dashboard.php), [`ngo/ngo_requests.php`](ngo/ngo_requests.php) |

## Getting started

### 1. Get the project

```bash
git clone https://github.com/nasirabbas827/foodsave-final.git
cd foodsave-final
```

### 2. Configure PHP and the database

1. Use a local PHP server with MySQL or MariaDB, such as XAMPP.
2. Import [`Database/foodsave_db.sql`](Database/foodsave_db.sql) into your local database server.
3. Set your local database connection details in [`admin/config.php`](admin/config.php), [`config.php`](config.php), [`donor/config.php`](donor/config.php), [`ngo/config.php`](ngo/config.php).
4. Keep the existing asset and upload directory paths intact.

### 3. Start the application

With the database running, start a local development server from the repository root:

```bash
php -S 127.0.0.1:8000
```

Open http://127.0.0.1:8000/index.php. If using Apache/XAMPP instead, place the project in its document root and open the matching localhost folder.

## Repository guide

| Path | Purpose |
| --- | --- |
| [`Database`](Database) | Database scripts |
| [`config.php`](config.php) | Application/database configuration |
| [`css`](css) | Stylesheets |
| [`images`](images) | Image assets |
| [`index.php`](index.php) | Web entry page |

## Development notes

These setup instructions are based on the repository source. The application has not been executed as part of this documentation update; verify dependencies and configuration in your local environment.
