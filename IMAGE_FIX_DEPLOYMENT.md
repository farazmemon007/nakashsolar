# Image Fix & Live Hosting Deployment Guide

## Issues Fixed ✅

### 1. **APP_URL Configuration**
- **Fixed in .env**: Changed `http:/nakshsolar.binsultansweet.com/` to `http://nakshsolar.binsultansweet.com/`
- Also changed `APP_DEBUG=true` to `APP_DEBUG=false` for production

### 2. **Image Path Issues**
The following files have been updated to use the Laravel `asset()` helper:

#### Changed Files:
- `resources/views/salesman_panel/dashboard.blade.php` 
  - `src="assets/img/icons/dash*.svg"` → `src="{{ asset('assets/img/icons/dash*.svg') }}"`

- `resources/views/distributor_panel/dashboard.blade.php`
  - `src="assets/img/icons/dash*.svg"` → `src="{{ asset('assets/img/icons/dash*.svg') }}"`

- `resources/views/welcome.blade.php`
  - `src="welcome-logo.png"` → `src="{{ asset('assets/img/welcome-logo.png') }}"`

---

## Live Hosting Deployment Steps 🚀

### Step 1: Run Storage Link (CRITICAL)
```bash
php artisan storage:link
```
This creates a symlink from `public/storage` to `storage/app/public`. **This is required for company logo images to display.**

### Step 2: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Step 3: Optimize for Production
```bash
php artisan optimize
```

### Step 4: Verify File Permissions
Make sure these directories are writable:
- `storage/` (755)
- `bootstrap/cache/` (755)
- `public/` (755)

---

## Environment Configuration

Verify `.env` is correctly set for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://nakshsolar.binsultansweet.com/
FILESYSTEM_DISK=local
```

---

## File Structure for Images

Images should be in:
- **Public assets**: `public/assets/img/icons/` (static images)
- **User uploads**: `storage/app/public/` (company logos, etc.)

The `asset()` helper automatically resolves:
- `asset('assets/img/icons/dash1.svg')` → `/public/assets/img/icons/dash1.svg`
- `asset('storage/company-logo.png')` → `/public/storage/company-logo.png` (via symlink)

---

## Troubleshooting

### Images still not showing?

1. **Check symlink exists**:
   ```bash
   ls -la public/storage
   ```
   Should show symlink to `../storage/app/public`

2. **Check APP_URL**:
   - Must match your domain exactly
   - Should NOT have `/admin` or other subdirectories

3. **Check file permissions**:
   ```bash
   chmod -R 755 storage bootstrap/cache public
   ```

4. **Clear browser cache**:
   - Hard refresh (Ctrl+Shift+R on Windows)

---

## Testing Locally

Run locally to verify everything works:
```bash
php artisan serve
```
Then visit `http://localhost:8000` and verify all images load.
