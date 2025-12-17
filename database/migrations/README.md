# Clean Migration Files - QMS

This folder contains **consolidated, organized migration files** for the Queue Management System.

## Migration Files (In Order)

1. **2024_01_01_000001_create_users_table.php**
   - Users table with all fields (locale, is_active, login tracking, etc.)
   - Password reset tokens
   - Sessions table

2. **2024_01_01_000002_create_departments_table.php**
   - Departments table

3. **2024_01_01_000003_create_services_table.php**
   - Services table with token ranges (normal + VIP)

4. **2024_01_01_000004_create_counters_table.php**
   - Counters table with waiting_for_token_at field

5. **2024_01_01_000005_create_tokens_table.php**
   - Tokens table with all fields (service_id, is_vip, priority, etc.)

6. **2024_01_01_000006_create_token_history_table.php**
   - Token history tracking table

7. **2024_01_01_000007_create_pivot_tables.php**
   - user_departments (pivot)
   - counter_service (pivot with priority)

8. **2024_01_01_000008_create_system_tables.php**
   - Cache, jobs, failed_jobs tables

9. **2024_01_01_000009_create_permission_tables.php**
   - Spatie permission package tables

## How to Use These Clean Migrations

### Option 1: Fresh Installation (Recommended for new projects)
```bash
# 1. Backup your current database
php artisan db:backup  # or manually export

# 2. Delete old migrations folder
rm -rf database/migrations

# 3. Rename this folder
mv database/migrations_new database/migrations

# 4. Fresh migrate
php artisan migrate:fresh --seed
```

### Option 2: Keep Current Data (For existing projects)
```bash
# 1. Export your current data
php artisan db:seed --class=DataExportSeeder  # Create this seeder first

# 2. Drop all tables
php artisan db:wipe

# 3. Move new migrations
rm -rf database/migrations
mv database/migrations_new database/migrations

# 4. Run new migrations
php artisan migrate

# 5. Import your data back
php artisan db:seed --class=DataImportSeeder
```

### Option 3: Manual Approach (Safest)
1. Keep both folders
2. Use migrations_new for NEW installations only
3. Keep old migrations for existing installations

## Benefits of These Clean Migrations

✅ **Organized by table** - One migration per table/concept
✅ **All fields included** - No need for multiple "add_column" migrations
✅ **Proper foreign keys** - All relationships defined upfront
✅ **Clean timestamps** - Easy to understand order (2024_01_01_000001, 000002, etc.)
✅ **Production ready** - Can be used for fresh installations

## Notes

- These migrations include ALL features from your 22 original migrations
- Foreign keys are properly set with cascade/set null
- Soft deletes are included where needed
- All indexes and unique constraints are in place
