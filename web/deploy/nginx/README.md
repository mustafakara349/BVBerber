# Nginx Production Deployment & Storage Security Guide

## Overview

This directory provides the production Nginx server block configuration for the B&V Barber management panel and API.

In accordance with **SEC-BE-2026-016**, the configuration strictly prevents executable scripts from being run inside the public storage directory (`/storage/` pointing to `storage/app/public/`).

---

## Security Architecture & Location Precedence

### The Vulnerability Pattern
In standard Nginx setups:
```nginx
location / { try_files $uri $uri/ /index.php?$query_string; }
location ~ \.php$ { fastcgi_pass unix:/var/run/php/php8.4-fpm.sock; ... }
```
Because regular expression matches (`~`) take precedence over standard prefix matches, any uploaded file with a `.php`, `.phar`, or `.phtml` extension inside `/storage/` matches the regex block and gets executed by PHP-FPM.

### The Remediation Directive
In `bvberber.conf`, we use the **`^~` (non-regex prefix)** modifier:
```nginx
location ^~ /storage/ {
    location ~* \.(php|phtml|phar|pl|py|cgi|sh|bash|exe|bat|cmd)$ {
        deny all;
        return 404;
    }

    try_files $uri =404;
    expires 30d;
    add_header Cache-Control "public, no-transform";
    add_header X-Content-Type-Options "nosniff" always;
    access_log off;
}
```
1. When a client requests any resource beginning with `/storage/`, the `^~` modifier signals to Nginx that **all outer regular expressions (specifically `location ~ \.php$`) are completely skipped**.
2. Within `/storage/`, requests for executable script extensions are caught by the nested regex and rejected immediately with `404 Not Found` / `deny all`.
3. Valid static assets (JPEG, PNG, WebP) are served directly by Nginx without ever touching the FastCGI/PHP subsystem.

---

## Production Deployment Steps

Follow these steps on the production Linux server (Ubuntu/Debian):

### 1. Copy Configuration
```bash
sudo cp /var/www/bvberber/deploy/nginx/bvberber.conf /etc/nginx/sites-available/bvberber.conf
```

### 2. Adjust Environment Paths
Edit `/etc/nginx/sites-available/bvberber.conf` to adjust:
- `server_name` (e.g., `panel.yourdomain.com`)
- `root` (ensure it points to `/var/www/bvberber/public`)
- SSL certificate paths (`ssl_certificate` and `ssl_certificate_key`)
- PHP-FPM socket path (verify PHP version: `/var/run/php/php8.4-fpm.sock`)

### 3. Enable Site
```bash
sudo ln -sf /etc/nginx/sites-available/bvberber.conf /etc/nginx/sites-enabled/bvberber.conf
```

### 4. Ensure Storage Symlink Exists
```bash
cd /var/www/bvberber
php artisan storage:link
```

### 5. Validate Syntax
> [!IMPORTANT]
> Always run `nginx -t` before reloading. Live Nginx validation remains outstanding until executed on the target host.
```bash
sudo nginx -t
```
Output should confirm:
```
nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
nginx: configuration file /etc/nginx/nginx.conf test is successful
```

### 6. Reload Nginx
```bash
sudo systemctl reload nginx
```

---

## Live Verification Procedure

Once deployed, verify both allowed media delivery and blocked script execution:

### Step 1: Verify Legitimate Media Delivery
Test that a valid public image returns `200 OK` with static caching headers:
```bash
curl -I https://panel.yourdomain.com/storage/services/example.webp
```
**Expected Result:**
```http
HTTP/2 200
content-type: image/webp
x-content-type-options: nosniff
cache-control: public, no-transform
```

### Step 2: Verify Script Execution Prevention
Place a test PHP script in the public storage directory:
```bash
echo "<?php echo 'UNSAFE_EXECUTION_TRIGGERED'; ?>" > /var/www/bvberber/storage/app/public/test_sec_check.php
```

Attempt to request the script over HTTP:
```bash
curl -i https://panel.yourdomain.com/storage/test_sec_check.php
```

**Expected Result:**
```http
HTTP/2 404 Not Found
# OR HTTP/2 403 Forbidden
```
**Critical Verification:** The string `UNSAFE_EXECUTION_TRIGGERED` must **NEVER** appear in the output. The file must never be executed by PHP-FPM.

### Step 3: Clean Up
Remove the test verification file:
```bash
rm -f /var/www/bvberber/storage/app/public/test_sec_check.php
```

---

## Environment Validation Status

- **Repository Template**: Created and reviewed (`web/deploy/nginx/bvberber.conf`).
- **Local Nginx Syntax Validation**: `nginx -t` was **not executed locally** because the local development workstation is Windows without the `nginx` CLI (`where.exe nginx` returned code 1).
- **Outstanding Step**: Run `sudo nginx -t` on the production Linux server during deployment prior to `systemctl reload nginx`.
