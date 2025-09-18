# Youth Ministry Management App

This repository contains a PHP-based youth ministry portal with administrator and youth-facing dashboards. It now ships with automatic database provisioning, reusable layout components, and server-side features that make it ready to run as a full-stack application.

## Features
- Admin dashboard for managing youth profiles, pledges, encouragement posts, and downloadable resources.
- Youth portal for viewing resources, posting encouragements, creating pledges, and maintaining personal profiles.
- Automatic database schema creation (users, resources, encouragements, and youth_pledges tables) with a seeded administrator account.
- Responsive layout with a shared navigation bar, collapsible sidebars, and reusable Bootstrap styling.

## Getting started
1. **Install dependencies** – PHP 8.1+, MySQL 8+, and Composer (optional if you want to add packages).
2. **Clone the repository** and install the database schema. By default the application connects to `127.0.0.1:3306` using the `root` user with no password and creates a database called `youth_ministry_db` if it does not exist.
   ```bash
   mysql -u root -p < database/schema.sql
   ```
3. **Configure database credentials** using environment variables if you need custom values:
   ```bash
   export DB_HOST=127.0.0.1
   export DB_PORT=3306
   export DB_USER=my_user
   export DB_PASSWORD=my_password
   export DB_NAME=youth_ministry_db
   ```
4. **Serve the application** from the repository root:
   ```bash
   php -S 0.0.0.0:8000
   ```
   or deploy with the provided Dockerfile.

The default administrator account is seeded automatically (or through the schema SQL file):
- Email: `admin@example.com`
- Password: `admin1@2024`

## Directory structure
- `admin/` – administrator dashboards, resource management, pledge reports, and moderation tools.
- `youth/` – youth-facing dashboard, resource listings, encouragement sharing, pledge management, and profile settings.
- `includes/` – shared configuration, password utilities, and layout components (header, footer, sidebars).
- `assets/` – shared Bootstrap styles, custom CSS/JS, and uploaded files.
- `database/schema.sql` – SQL script that provisions all required tables and the seed admin account.

## Development tips
- The layout system expects pages that include `includes/header.php` to set `$pageTitle`, `$bodyClass`, and `$showSidebarToggle` when sidebars are needed.
- Uploaded files are stored inside `assets/images/profiles/` and `assets/resources/`. Make sure these directories are writable by the PHP process in your environment.
- Global JavaScript helpers live in `assets/js/scripts.js` and handle sidebar toggling and password visibility controls.

Feel free to extend the schema or add API endpoints depending on your deployment needs.
