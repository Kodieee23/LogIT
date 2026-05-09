# LogIT - IT Support Management System

LogIT is a streamlined, aesthetically modern IT support logging and tracking system built on Laravel 11. It is designed to provide IT staff and administrators with a fast, responsive interface for logging tasks, tracking unresolved issues, managing categories, and exporting system data.

## Features

### 🌟 Modern, Glassmorphism UI
- Fully responsive, mobile-first design tailored for quick interaction.
- Sleek glassmorphism visual identity with a cohesive light-mode aesthetic.
- Custom dropdowns, dynamic modals, and compact icon-based action buttons.

### 👥 Role-Based Access Control
- **Staff Level:** Can log new tasks, update their unresolved tasks, add comments, and view historical tasks.
- **Admin Level:** Has full access to user management, category management, data exports, and high-level system analytics.

### 📋 Task Logging & Management
- **Quick Logging:** Staff can rapidly log issues with details such as Department, Staff Helped, Description, Category, and Priority Status (Resolved, Medium, Urgent).
- **Task Feeds:** The dashboard separates "Tasks Requiring Attention" from standard "Recent Tasks" to keep critical issues highly visible.
- **Tasks Page (`/tasks`):** A dedicated, filterable repository of all historical tasks. Users can search and filter by Date, Status, and Staff Member.
- **Commenting System:** Staff can leave follow-up comments on active tasks to track progress.

### 📊 Admin Analytics & Control
- **Admin Dashboard:** Features an interactive Chart.js Pie Chart visualizing "Tasks by Category".
- **Admin Panel:** Complete CRUD interface for managing system users (Edit, Delete, Reset Password) and dynamic Category tags.
- **Export Capabilities:** Admins can export the entire task database into CSV or PDF formats for offline reporting.

## Tech Stack
- **Backend:** Laravel 11 (PHP)
- **Database:** SQLite (default for development/portability)
- **Frontend:** Vanilla HTML/CSS with JavaScript (No heavy CSS frameworks; relies on custom `styles.css` for the glassmorphism theme).
- **Data Visualization:** Chart.js
- **PDF Generation:** barryvdh/laravel-dompdf

## Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd LogIT
   ```

2. **Install Dependencies:**
   ```bash
   composer install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Configuration:**
   Ensure your `.env` is configured for your preferred database (SQLite is pre-configured by default).
   ```bash
   php artisan migrate --seed
   ```
   *(Note: The seeder creates an initial admin user `admin` with password `password123`)*

5. **Run the Application:**
   ```bash
   php artisan serve
   ```
   The system will be accessible at `http://127.0.0.1:8000`.

## Deployment (Railway/Production)
When deploying to a production environment like Railway:
1. Ensure the `APP_ENV` is set to `production`.
2. Ensure `APP_DEBUG` is `false`.
3. Set the `APP_KEY` environment variable.
4. If using SQLite, ensure the persistent volume is correctly mapped to the database path. For PostgreSQL/MySQL, update the `DB_CONNECTION` variables accordingly.

## System Workflow
1. **Authentication:** Users log in. The system redirects them to `/dashboard`.
2. **Logging:** A staff member resolves an issue for the Finance department and logs it via the Dashboard form.
3. **Tracking:** If the issue is marked as "Medium" or "Urgent", it appears in the "Tasks Requiring Attention" feed until it is marked as "Resolved".
4. **Analysis:** Admins review the Admin Dashboard to see which categories (e.g., Network, Hardware) generate the most support tickets.

## License
Proprietary software. All rights reserved.
