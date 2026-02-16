# Fix Vite Permission Denied Error on VPS

## Problem
When running `npm run build` on VPS, you get one of these errors:
```
sh: 1: vite: Permission denied
```
or
```
Error: The service was stopped: spawn /var/www/entrack/node_modules/@esbuild/linux-x64/bin/esbuild EACCES
```

This happens because executables in `node_modules` don't have execute permissions. This can affect:
- `node_modules/.bin/vite`
- `node_modules/@esbuild/linux-x64/bin/esbuild` (and other platform-specific binaries)
- Other native module binaries

## Solution Options

### Option 1: Fix Permissions on ALL node_modules Binaries (Recommended)

Run these commands on your VPS:

```bash
# Navigate to your project directory
cd /var/www/entrack

# Fix ownership first (important!)
sudo chown -R ubuntu:ubuntu node_modules

# Make all executables in node_modules/.bin executable
sudo chmod +x node_modules/.bin/*

# Fix permissions on esbuild and other native binaries
# Find and fix all binary files in node_modules
sudo find node_modules -type f -name "esbuild" -exec chmod +x {} \;
sudo find node_modules -type f -path "*/bin/*" -exec chmod +x {} \;
sudo find node_modules -type f -path "*/.bin/*" -exec chmod +x {} \;

# Or more comprehensively - fix all directories and files
sudo chmod -R 755 node_modules
```

### Option 2: Reinstall node_modules (If Option 1 doesn't work)

**IMPORTANT: If you get `EACCES: permission denied, mkdir '/var/www/entrack/node_modules'`**, you need to fix directory ownership first:

```bash
cd /var/www/entrack

# Fix ownership of the directory so ubuntu user can write
sudo chown -R ubuntu:ubuntu /var/www/entrack

# Remove node_modules and package-lock.json
sudo rm -rf node_modules package-lock.json

# Reinstall as the ubuntu user (not sudo)
npm install

# Fix permissions on binaries after install
sudo chmod -R 755 node_modules

# Now try building
npm run build
```

### Option 3: Use npx instead (Quick Fix)

Modify your `package.json` to use `npx vite` instead of just `vite`:

```json
{
  "scripts": {
    "dev": "npx vite",
    "build": "npx vite build"
  }
}
```

### Option 4: Fix Ownership and Permissions (Complete Fix)

```bash
cd /var/www/entrack

# Set ownership to ubuntu user (or your user)
sudo chown -R ubuntu:ubuntu /var/www/entrack

# Fix node_modules permissions
sudo chmod -R 755 node_modules

# Specifically fix .bin directory
sudo chmod -R 755 node_modules/.bin

# Now try building
npm run build
```

## Recommended Complete Setup

If you're setting up from scratch or want to ensure everything works:

```bash
cd /var/www/entrack

# 1. Set ownership (use your actual user, not www-data for node_modules)
sudo chown -R ubuntu:ubuntu /var/www/entrack

# 2. Remove old node_modules if exists
sudo rm -rf node_modules package-lock.json

# 3. Install dependencies as ubuntu user (NOT with sudo)
npm install

# 4. Fix permissions on executables
chmod +x node_modules/.bin/*

# 5. Now build should work
npm run build

# 6. After build, set proper permissions for Laravel files
# (Keep node_modules owned by ubuntu, but Laravel files by www-data)
sudo chown -R www-data:www-data /var/www/entrack/storage
sudo chown -R www-data:www-data /var/www/entrack/bootstrap/cache
sudo chmod -R 775 /var/www/entrack/storage
sudo chmod -R 775 /var/www/entrack/bootstrap/cache
```

## Quick One-Liner Fix

If you just want a quick fix right now:

```bash
cd /var/www/entrack && sudo chown -R ubuntu:ubuntu node_modules && sudo find node_modules -type f -path "*/bin/*" -exec chmod +x {} \; && sudo chmod +x node_modules/.bin/* && npm run build
```

Or the comprehensive fix:

```bash
cd /var/www/entrack && sudo chown -R ubuntu:ubuntu node_modules && sudo chmod -R 755 node_modules && npm run build
```

## Verify It Works

After applying the fix, verify:

```bash
# Check if vite is executable
ls -la node_modules/.bin/vite

# Should show: -rwxr-xr-x (the x means executable)

# Check if esbuild is executable
ls -la node_modules/@esbuild/linux-x64/bin/esbuild

# Should also show: -rwxr-xr-x (the x means executable)

# Try building
npm run build
```

## Common Errors and Solutions

### Error: `EACCES: permission denied, mkdir '/var/www/entrack/node_modules'`

This happens when the directory is owned by `www-data:www-data` (common Laravel setup). You have two options:

#### Option A: Add ubuntu to www-data group (Recommended for Production)

This keeps www-data ownership but allows ubuntu to write:

```bash
# Add ubuntu user to www-data group
sudo usermod -a -G www-data ubuntu

# Log out and log back in (or use newgrp) for group changes to take effect
newgrp www-data

# Set group permissions so www-data group can write
cd /var/www/entrack
sudo chmod -R g+w /var/www/entrack

# Now npm install will work
npm install

# Fix permissions on binaries
sudo chmod -R 755 node_modules

# Build
npm run build
```

#### Option B: Temporarily change ownership (Quick Fix)

```bash
cd /var/www/entrack

# Temporarily change ownership to ubuntu for npm operations
sudo chown -R ubuntu:ubuntu /var/www/entrack

# Install and build
npm install
sudo chmod -R 755 node_modules
npm run build

# Set Laravel directories back to www-data (for web server)
sudo chown -R www-data:www-data /var/www/entrack/storage
sudo chown -R www-data:www-data /var/www/entrack/bootstrap/cache
sudo chmod -R 775 /var/www/entrack/storage
sudo chmod -R 775 /var/www/entrack/bootstrap/cache

# Set rest of directory back to www-data
sudo chown -R www-data:www-data /var/www/entrack

# But keep node_modules owned by ubuntu (for future npm commands)
sudo chown -R ubuntu:ubuntu /var/www/entrack/node_modules
```

#### Option C: Mixed Ownership (Best for Ongoing Development)

Keep most files as www-data, but allow ubuntu to manage node_modules:

```bash
cd /var/www/entrack

# Set base ownership to www-data
sudo chown -R www-data:www-data /var/www/entrack

# Add ubuntu to www-data group
sudo usermod -a -G www-data ubuntu
newgrp www-data

# Set permissions so group can write
sudo chmod -R g+w /var/www/entrack

# Install npm packages (ubuntu user, but in www-data group)
npm install

# Fix binary permissions
sudo chmod -R 755 node_modules

# Build
npm run build

# Ensure storage/cache are writable by web server
sudo chmod -R 775 /var/www/entrack/storage
sudo chmod -R 775 /var/www/entrack/bootstrap/cache
```

## Notes

- **Never run `npm install` with `sudo`** - this causes permission issues
- If you get permission errors, fix ownership FIRST with `sudo chown -R ubuntu:ubuntu /var/www/entrack`
- Keep `node_modules` owned by your user (ubuntu), not www-data
- Only Laravel storage/cache directories need www-data ownership
- If you must use sudo for npm, use `sudo -u ubuntu npm install` instead

