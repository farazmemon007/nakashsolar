# Quick Deployment Guide

## 📋 Pre-Deployment Checklist
- [ ] All PHP files committed to git/uploaded to hosting
- [ ] Database credentials correct in `.env`
- [ ] MySQL/MariaDB running on hosting
- [ ] SSH/FTP access available

## ⚡ Quick Commands for Hosting

### 1. Connect to Hosting (via SSH)
```bash
ssh your_username@your_hosting_domain.com
cd /path/to/green_vision
```

### 2. Pull Latest Code
```bash
git pull origin main
# OR if using FTP, upload all modified files
```

### 3. Run Migrations
```bash
php artisan migrate
```

**Expected Output:**
```
Migration completed successfully
```

### 4. Verify Database
```bash
php artisan tinker
>>> DB::connection()->getPdo()
```

**Expected Output:** Should not show connection error

### 5. Test Purchase Creation
- Go to: `https://your_domain.com/Purchase`
- Create a test purchase
- Verify it saves and displays invoice

### 6. Check Logs
```bash
tail -20 storage/logs/laravel.log
```

**Expected:** No errors, clean log

## 🔧 If Migration Fails

### Check Status
```bash
php artisan migrate:status
```

### Rollback if Needed
```bash
php artisan migrate:rollback
```

### Rerun Migration
```bash
php artisan migrate
```

## 🧪 Quick Test Script

Copy to `storage/test_purchase.php`:
```php
<?php
$purchase = App\Models\Purchase::latest()->first();
if ($purchase) {
    echo "Latest Purchase ID: " . $purchase->id . "\n";
    echo "Vendor ID: " . $purchase->vendor_id . "\n";
    echo "Grand Total: " . $purchase->grand_total . "\n";
    
    $ledger = App\Models\VendorLedger::where('vendor_id', $purchase->vendor_id)->latest()->first();
    if ($ledger) {
        echo "Ledger Balance: " . $ledger->closing_balance . "\n";
        echo "Status: ✓ WORKING\n";
    } else {
        echo "Status: ✗ LEDGER MISSING\n";
    }
}
?>
```

Run with:
```bash
php artisan tinker
>>> include 'storage/test_purchase.php'
```

## 📱 Common Issues & Quick Fixes

### Issue: Migration fails with "Foreign key constraint"
```bash
# Solution: Check if table already has vendor_id
php artisan tinker
>>> Schema::hasColumn('purchases', 'vendor_id')
# If true, migration should work on retry
```

### Issue: Purchase saves but ledger doesn't update
```bash
# Check logs
tail -50 storage/logs/laravel.log | grep -i ledger

# Solution: Ensure migration ran
php artisan migrate:status | grep vendor_foreign_key
```

### Issue: "Something went wrong" error still appears
```bash
# Enable debug mode temporarily
# In .env: APP_DEBUG=true
# Try again, check logs
tail -100 storage/logs/laravel.log

# Disable debug after testing
# In .env: APP_DEBUG=false
```

## ✅ Verification Checklist

- [ ] Migration completed: `php artisan migrate:status`
- [ ] No errors in logs: `tail storage/logs/laravel.log`
- [ ] Create test purchase successfully
- [ ] Invoice displays with correct totals
- [ ] Vendor ledger updated in database
- [ ] Stock incremented for items
- [ ] Delete purchase reverses ledger
- [ ] Edit purchase recalculates ledger

## 📞 Debugging Commands

```bash
# View all recent migrations
php artisan migrate:status

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Test vendor existence
>>> App\Models\Vendor::count()

# View latest purchase
>>> App\Models\Purchase::latest()->first()

# Check foreign key
>>> Schema::getConnection()->getDoctrineSchemaManager()->introspectTable('purchases')->getForeignKeys()
```

## 🎉 Success Indicators

✅ All of these should be true:
- Migration ran without errors
- No errors in `storage/logs/laravel.log`
- Test purchase creates successfully
- Invoice loads with correct data
- Ledger balance updates correctly
- Vendor relationship works (purchase->vendor)

---

**Quick Reference:**
- Migration file: `2026_06_15_000001_add_vendor_foreign_key_to_purchases_table.php`
- Key changes: PurchaseController.php, Purchase.php, VendorLedger.php
- Deployment time: ~5 minutes
- Testing time: ~10 minutes
