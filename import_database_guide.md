# Database Import Guide - DigitalOcean to OVH VPS

## Step 1: Transfer the SQL file to your VPS

Open PowerShell on Windows and run:

```powershell
scp "C:\Users\Admin\Desktop\full_backup2_utf8.sql" ubuntu@51.75.205.36:/home/ubuntu/
```

When prompted, enter the password: `bureaumerciervps1748+*`

## Step 2: SSH into your VPS

```powershell
ssh ubuntu@51.75.205.36
```

Enter password when prompted: `bureaumerciervps1748+*`

## Step 3: On the VPS, check PostgreSQL status and get database credentials

First, verify PostgreSQL is installed and running:
```bash
sudo systemctl status postgresql
```

If PostgreSQL is not installed:
```bash
sudo apt update
sudo apt install postgresql postgresql-contrib -y
```

## Step 4: Your Database Credentials (from .env)

Based on your .env file:
- **Database Name**: `old_app_backup`
- **Username**: `postgres`
- **Password**: `0000`
- **Host**: `127.0.0.1`
- **Port**: `5432`

## Step 5: Import the database

**⚠️ IMPORTANT**: Your dump was created from a database named "db", but you're importing to "old_app_backup". The dump file may contain `\connect db` commands. Use one of these methods:

### Method 1: Recommended - Import using postgres user (no password needed)

```bash
# First, ensure the database exists (create it if it doesn't)
sudo -u postgres createdb old_app_backup 2>/dev/null || echo "Database may already exist"

# Import the SQL file (this will ignore \connect commands and use the -d parameter)
sudo -u postgres psql -d old_app_backup -f /home/ubuntu/full_backup2_utf8.sql
```

### Method 2: If Method 1 fails due to \connect commands in the dump

If the dump file contains `\connect db` commands that cause errors, you need to replace them:

```bash
# Create the database if it doesn't exist
sudo -u postgres createdb old_app_backup 2>/dev/null || echo "Database may already exist"

# Replace \connect db with \connect old_app_backup and import
sed 's/\\connect db/\\connect old_app_backup/g' /home/ubuntu/full_backup2_utf8.sql | sudo -u postgres psql -d old_app_backup
```

### Method 3: Using password authentication

```bash
# Create the database if it doesn't exist
sudo -u postgres createdb old_app_backup 2>/dev/null || echo "Database may already exist"

# Import with password
PGPASSWORD=0000 psql -U postgres -d old_app_backup -h localhost -f /home/ubuntu/full_backup2_utf8.sql
```

### Method 4: If you want to import to a different database name first

If you prefer to import to "db" and then rename, or if you want to match the original database name:

```bash
# Option: Create database 'db' to match the dump
sudo -u postgres createdb db 2>/dev/null || echo "Database may already exist"

# Import to 'db'
sudo -u postgres psql -d db -f /home/ubuntu/full_backup2_utf8.sql

# Then update your .env to use DB_DATABASE=db instead of old_app_backup
```

## Important Notes:

1. **Database Name**: The dump might reference a specific database name. If you need to import to a different database name, you can:
   ```bash
   # Use sed to replace database references
   sed 's/\\connect db/\\connect YOUR_NEW_DB_NAME/g' /home/ubuntu/full_backup2_utf8.sql | psql -U YOUR_DB_USER -d YOUR_NEW_DB_NAME -h localhost
   ```

2. **Large Files**: If the file is very large, you might want to use `pv` (pipe viewer) to monitor progress:
   ```bash
   sudo apt install pv
   pv /home/ubuntu/full_backup2_utf8.sql | sudo -u postgres psql -d YOUR_DB_NAME
   ```

3. **Permissions**: Make sure the ubuntu user has read access to the SQL file:
   ```bash
   ls -la /home/ubuntu/full_backup2_utf8.sql
   ```

4. **Connection Issues**: If you get connection errors, you might need to modify PostgreSQL's pg_hba.conf:
   ```bash
   sudo nano /etc/postgresql/*/main/pg_hba.conf
   # Make sure local connections are allowed
   # Then restart: sudo systemctl restart postgresql
   ```

## Step 6: Verify the import

After import, verify:
```bash
# List all tables
sudo -u postgres psql -d old_app_backup -c "\dt"

# Or connect interactively to explore
sudo -u postgres psql -d old_app_backup

# Once connected, you can run:
# \dt          - list tables
# \d table_name - describe a table
# SELECT COUNT(*) FROM your_table_name; - count rows
# \q           - quit
```

To check specific tables (examples):
```bash
# Count migrations table (should exist in Laravel apps)
sudo -u postgres psql -d old_app_backup -c "SELECT COUNT(*) FROM migrations;"

# List all table names
sudo -u postgres psql -d old_app_backup -c "SELECT tablename FROM pg_tables WHERE schemaname='public';"
```

## Troubleshooting:

- **Permission denied**: Use `sudo -u postgres` before psql commands
- **Database doesn't exist**: Create it first with `createdb`
- **Connection refused**: Check PostgreSQL is running: `sudo systemctl status postgresql`
- **Encoding issues**: The dump was created with UTF8, so it should be fine, but if you see encoding errors, you can set: `export PGCLIENTENCODING=UTF8`

