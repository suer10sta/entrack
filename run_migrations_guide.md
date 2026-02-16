# Running Laravel Migrations After Database Import

After importing your database, you need to run any migrations that weren't in the imported database. Laravel tracks which migrations have been run in the `migrations` table.

## Step 1: SSH into your VPS

```bash
ssh ubuntu@51.75.205.36
```

Password: `bureaumerciervps1748+*`

## Step 2: Navigate to your Laravel application directory

```bash
# Find your Laravel app directory (common locations)
cd /var/www/html
# OR
cd /var/www/your-app-name
# OR wherever your Laravel app is located

# You can check by looking for artisan file
ls -la artisan
```

## Step 3: Check which migrations have already been run

Before running migrations, you can check what's already in the migrations table:

```bash
# Connect to the database
sudo -u postgres psql -d old_app_backup

# Inside psql, check the migrations table
SELECT * FROM migrations ORDER BY id DESC LIMIT 20;

# Or see all migration names
SELECT migration FROM migrations ORDER BY migration;

# Exit psql
\q
```

## Step 4: Run pending migrations

Laravel will automatically detect which migrations haven't been run yet:

```bash
# Make sure you're in your Laravel app directory
cd /path/to/your/laravel/app

# Run migrations (only new ones will execute)
php artisan migrate

# If you need to see what will run without executing:
php artisan migrate --pretend

# For verbose output to see each migration:
php artisan migrate -v
```

## Step 5: Verify the migrations ran successfully

```bash
# Check the migrations table again to see newly added entries
sudo -u postgres psql -d old_app_backup -c "SELECT migration FROM migrations ORDER BY migration DESC LIMIT 10;"

# Or check specific tables/columns that should have been added
# For example, check if new columns exist:
sudo -u postgres psql -d old_app_backup -c "\d devis_history" | grep -i billing
sudo -u postgres psql -d old_app_backup -c "\d entreprises" | grep -i type_compt
```

## Important Notes:

1. **Safe Migrations**: Your migrations already have safety checks (like `Schema::hasColumn()`), so they won't fail if columns already exist.

2. **Database Connection**: Make sure your `.env` file on the VPS has the correct database credentials:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=old_app_backup
   DB_USERNAME=postgres
   DB_PASSWORD=0000
   ```

3. **Permissions**: Make sure the web server user (usually `www-data` or `nginx`) has the correct permissions, but for running migrations, your current user should be fine.

4. **Backup First** (optional but recommended):
   ```bash
   # Create a backup before running migrations
   sudo -u postgres pg_dump old_app_backup > /home/ubuntu/backup_before_migrations_$(date +%Y%m%d_%H%M%S).sql
   ```

## Troubleshooting:

### If migrations fail with "column already exists":
- This shouldn't happen with your migrations since they check first, but if it does:
  ```bash
  php artisan migrate:status  # Check migration status
  ```

### If you see "migration already exists" errors:
- This means the migration is already in the migrations table but wasn't actually run. You can:
  ```bash
  # Roll back the last batch
  php artisan migrate:rollback --step=1
  
  # Then run again
  php artisan migrate
  ```

### If you need to run a specific migration:
```bash
# Run migrations up to a specific migration
php artisan migrate --path=/database/migrations/2025_12_01_120500_add_billing_fields_to_devis_history_table.php
```

### Check Laravel can connect to the database:
```bash
php artisan db:show  # Shows database connection info
```

## Latest Migrations in Your Codebase:

Based on your migrations folder, the latest migrations that might need to be run are:
- `2025_12_03_000000_add_account_status_to_entreprises_table.php`
- `2025_12_01_120500_add_billing_fields_to_devis_history_table.php`
- `2025_11_19_142449_update_subscriptions_table_for_entreprise.php`
- `2025_11_19_133452_add_cashier_columns_to_entreprises_table.php`
- `2025_11_19_091736_fix_user_id_type_in_employees.php`
- `2025_11_14_151040_add_payment_date_to_invoices_history_table.php`
- `2025_11_14_000001_create_customer_columns.php`
- `2025_11_10_143645_make_start_column_nullable_in_tasks.php`
- `2025_11_10_091759_add_trial_ends_at_to_entreprises_table.php`
- `2025_11_10_082143_add_archived_to_employees_table.php`
- `2025_11_07_132202_modify_plans_for_entreprises.php`

These are all from 2025, so if your imported database is older, these will need to be run.

