# Fix for 413 Request Entity Too Large Error

## Problem
When uploading files larger than 1MB (especially PDFs around 10MB), nginx returns a `413 Request Entity Too Large` error. This happens because nginx blocks the request before it reaches PHP.

## VPS Connection Information
- **IP:** 51.75.205.36
- **Port:** 22
- **Username:** ubuntu
- **Password:** bureaumerciervps1748+*
- **Web Directory:** /var/www/entrack

## Solution

You need to increase the `client_max_body_size` directive in your nginx configuration.

### Steps to Fix:

#### Step 1: Connect to your VPS via SSH

**On Windows (using PowerShell or Command Prompt):**
```bash
ssh ubuntu@51.75.205.36 -p 22
```
When prompted, enter the password: `bureaumerciervps1748+*`

**On Linux/Mac:**
```bash
ssh ubuntu@51.75.205.36 -p 22
```

#### Step 2: Find your nginx site configuration file

First, let's find which nginx config file is being used for your site:

```bash
sudo ls -la /etc/nginx/sites-available/
```

Look for a file that might be named `entrack`, `default`, or similar. Also check:

```bash
sudo ls -la /etc/nginx/sites-enabled/
```

#### Step 3: Edit the nginx configuration

Once you identify the config file (let's assume it's `entrack` or `default`), edit it:

```bash
sudo nano /etc/nginx/sites-available/entrack
```

Or if it's named differently:
```bash
sudo nano /etc/nginx/sites-available/default
```

**Add this line inside the `server` block** (look for the `server {` section):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    
    # Increase upload size limit to 25MB (adjust as needed)
    client_max_body_size 25M;
    
    # ... rest of your configuration
    root /var/www/entrack/public;
    # ... other directives
}
```

**Important:** Add `client_max_body_size 25M;` right after the `server {` line or before the `root` directive.

#### Step 4: Test the nginx configuration

```bash
sudo nginx -t
```

You should see: `nginx: configuration file /etc/nginx/nginx.conf test is successful`

#### Step 5: Reload nginx

```bash
sudo systemctl reload nginx
```

### Additional PHP Configuration

#### Step 6: Check PHP version

```bash
php -v
```

This will show your PHP version (e.g., 8.1, 8.2, etc.)

#### Step 7: Edit PHP configuration

Based on your PHP version, edit the php.ini file. For example, if you have PHP 8.1:

```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

Or for PHP 8.2:
```bash
sudo nano /etc/php/8.2/fpm/php.ini
```

**Find and modify these lines** (use Ctrl+W to search in nano):
```ini
upload_max_filesize = 25M
post_max_size = 25M
memory_limit = 256M
```

**Save the file:** Press `Ctrl+O`, then `Enter`, then `Ctrl+X` to exit.

#### Step 8: Restart PHP-FPM

For PHP 8.1:
```bash
sudo systemctl restart php8.1-fpm
```

For PHP 8.2:
```bash
sudo systemctl restart php8.2-fpm
```

Or check which version is running:
```bash
sudo systemctl list-units | grep php
```

Then restart the appropriate service.

### Verification

After making these changes, try uploading your 10MB PDF again. The error should be resolved.

## Quick Command Reference (Copy-Paste Ready)

Here's a quick sequence of commands you can copy and paste:

```bash
# 1. Connect to VPS
ssh ubuntu@51.75.205.36 -p 22

# 2. Find nginx config file
sudo ls -la /etc/nginx/sites-available/
sudo ls -la /etc/nginx/sites-enabled/

# 3. Edit nginx config (replace 'entrack' with your actual config filename)
sudo nano /etc/nginx/sites-available/entrack
# Add: client_max_body_size 25M; inside the server block

# 4. Test and reload nginx
sudo nginx -t
sudo systemctl reload nginx

# 5. Check PHP version
php -v

# 6. Edit PHP config (replace 8.1 with your PHP version)
sudo nano /etc/php/8.1/fpm/php.ini
# Modify: upload_max_filesize = 25M, post_max_size = 25M

# 7. Restart PHP-FPM (replace 8.1 with your PHP version)
sudo systemctl restart php8.1-fpm
```

## Notes

- The Laravel validation rule in `employeesController.php` already allows up to 20MB (`max:20480` = 20MB)
- Setting nginx to 25M provides a comfortable buffer
- Adjust the size limits based on your server's resources and requirements
- Your web directory is located at: `/var/www/entrack`

