# Hospital Queue Management System (QMS) - Development Guide

This guide documents the step-by-step process used to build this application. Follow these steps to recreate the system or understand its architecture for future updates.

---

## Phase 1: Project Setup

### 1. Initialize Laravel Project
```bash
composer create-project laravel/laravel QMS
cd QMS
```

### 2. Install Dependencies
We used several packages to speed up development:
```bash
# Authentication Scaffolding
composer require laravel/ui
php artisan ui bootstrap --auth

# Excel Export (for reports)
composer require maatwebsite/excel

# PDF Generation (for reports)
composer require barryvdh/laravel-dompdf

# Install Frontend Assets
npm install
npm install flatpickr choices.js datatables.net-bs5
npm run build
```

### 3. Database Configuration (`.env`)
We used SQLite for simplicity, but you can use MySQL.
```env
DB_CONNECTION=sqlite
# DB_HOST, DB_PORT, etc. are not needed for SQLite
```
*Note: Created an empty `database/database.sqlite` file.*

---

## Phase 2: Database Design & Migrations

We created the following tables using `php artisan make:migration`:

1.  **Users**: Enhanced default table with `role` (admin, reception, counter) and `status`.
2.  **Departments**: `name`, `code` (e.g., 'PH' for Pharmacy), `letter` (for token prefix).
3.  **Counters**: `name`, `department_id`, `is_active`.
4.  **Tokens**: The core table.
    - `token_number`: (e.g., A001)
    - `status`: `waiting`, `called`, `serving`, `completed`, `cancelled`
    - `issued_at`, `called_at`, `served_at`, `completed_at`
5.  **TokenHistory**: Audit trail for status changes.

### Key Models (`app/Models`)
- **User**: Added `isAdmin()`, `isReception()`, etc. helper methods.
- **Token**: Added `logHistory()` to track changes automatically.
- **Department**: Added `getNextTokenNumber()` logic to generate sequential numbers (A001, A002...).

---

## Phase 3: Authentication & Roles

### 1. Middleware (`app/Http/Middleware`)
- **RoleMiddleware**: Checks if user has the required role (e.g., `admin`).
- **CheckAccountStatus**: Ensures user account is active before logging in.

### 2. Routes (`routes/web.php`)
We grouped routes by role:
```php
Route::middleware(['auth', 'role:admin'])->group(function() {
    // User & Department Management
});

Route::middleware(['auth', 'role:reception'])->group(function() {
    // Token Issuance
});
```

---

## Phase 4: Core Logic (The "Brain")

### 1. Token Controller (`TokenController.php`)
- **Issue**: Creates a new token with status `waiting`.
- **Call**: Finds the next `waiting` token for the counter's department, changes status to `called`.
- **Complete/Cancel**: Updates status to `completed` or `cancelled`.

### 2. Dashboard Controller (`DashboardController.php`)
- **Admin**: Shows overall stats.
- **Reception**: Shows issuance form.
- **Counter**: Shows "Call Next" button and current token.
- **Display**: Shows public view.

---

## Phase 5: The Display Board (TV Screen)

### 1. Public View (`resources/views/display/index.blade.php`)
- Designed with a **Dark Theme** for high visibility.
- Uses **AJAX Polling** (fetches data every 3 seconds) instead of page reloads to keep the screen smooth.

### 2. Text-to-Speech (TTS)
- Uses the browser's `speechSynthesis` API.
- **Logic**:
    1. Check if `mainToken` status is `called`.
    2. Check if this token was already announced (using `localStorage`).
    3. If new, speak in English then Arabic.
    4. "Enable Audio" button added to bypass browser autoplay restrictions.

---

## Phase 6: Reporting

### 1. Report Controller
- Filters tokens by Date Range, Department, and Status.
- Calculates **Average Wait Time** (Served Time - Issued Time).

### 3. Counter Queue Logic (Auto-Assignment)
- **Problem**: If a counter calls next but no tokens are waiting, they should "wait" for the next one.
- **Solution**:
    1.  Added `waiting_for_token_at` timestamp to `counters` table.
    2.  **TokenController::call**: If no token found, set counter status to "Waiting".
    3.  **TokenController::issue**: When a new token is created, check if any counters are waiting.
    4.  **Auto-Assign**: Assign the new token to the *longest-waiting* counter automatically.
    5.  **Frontend**: Counter dashboard polls for updates and shows "Waiting for Token..." status.

### 4. How to Maintain & Update

### Adding a New Role
1. Add role to `User` model constants.
2. Update `RoleMiddleware`.
3. Create a new Dashboard view.

### Changing Token Logic
- Edit `app/Models/Department.php` -> `getNextTokenNumber()` if you want to change the numbering format (e.g., from A001 to 1001).

### Customizing the Display
- Edit `resources/views/display/index.blade.php`.
- You can change colors, font sizes, or the TTS voice speed here.

### Backup
- Simply copy your `database/database.sqlite` file to back up all data.

---

## Troubleshooting Common Issues

1.  **"View not found"**: You probably forgot to create the blade file in `resources/views`.
2.  **"Route not defined"**: Check `routes/web.php` and run `php artisan route:list`.
3.  **Audio not playing**: Click the "Enable Audio" button. Browsers block auto-audio.
4.  **Database errors**: Run `php artisan migrate:fresh --seed` to reset the database (WARNING: Deletes all data).
