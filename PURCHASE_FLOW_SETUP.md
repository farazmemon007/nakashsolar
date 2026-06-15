# Purchase Flow - Complete Setup & Deployment Guide

## 🔧 Changes Implemented

### 1. **PurchaseController.php** - Enhanced Error Handling
- Added comprehensive try-catch blocks for database errors
- Proper vendor validation using `Vendor::findOrFail()`
- Fixed VendorLedger queries to use vendor_id instead of party_name
- Added detailed error logging to `storage/logs/laravel.log`
- Better AJAX responses with debug information in development

### 2. **Purchase Model** - Better Structure
```php
// Relationships
- vendor() -> belongsTo(Vendor::class, 'vendor_id', 'id')
- vendorLedger() -> hasOne(VendorLedger::class, 'vendor_id', 'vendor_id')

// Helper Methods
- getItemsArray() - Returns decoded items
- getRatesArray() - Returns decoded rates
- getAmountsArray() - Returns decoded amounts
- getDiscountsArray() - Returns decoded discounts
- getGrossTotal() - Sum of amounts
- getTotalDiscount() - Sum of discounts
- getNetTotal() - Gross - Discount
```

### 3. **VendorLedger Model** - Complete Relationships
```php
- vendor() -> belongsTo(Vendor::class, 'vendor_id', 'id')
- user() -> belongsTo(User::class, 'admin_or_user_id', 'id')
- getBalanceDue() - Money owed by vendor
- getPaymentMade() - Payment received from vendor
```

### 4. **New Migration** - Database Integrity
- File: `2026_06_15_000001_add_vendor_foreign_key_to_purchases_table.php`
- Adds foreign key: `purchases.vendor_id -> vendors.id`
- Handles existing constraints gracefully
- Enables cascading deletes for data consistency

## 📋 Pre-Deployment Checklist

### Local Testing ✓
- [x] Purchase created successfully
- [x] Vendor linked properly
- [x] VendorLedger updated
- [x] Stock updated
- [x] Invoice displays correctly

### Database Verification
- [ ] MySQL/MariaDB running and accessible
- [ ] All migrations applied
- [ ] Foreign keys created
- [ ] User permissions configured

## 🚀 Deployment Steps

### Step 1: Upload Files
```bash
# Files Modified:
- app/Http/Controllers/PurchaseController.php
- app/Models/Purchase.php
- app/Models/VendorLedger.php

# New Migration:
- database/migrations/2026_06_15_000001_add_vendor_foreign_key_to_purchases_table.php
```

### Step 2: Run Migrations
```bash
php artisan migrate
```
**Expected Output:** Migration creating foreign key constraint

### Step 3: Test Purchase Flow
1. Navigate to Purchase module
2. Select a vendor
3. Add items with quantities and rates
4. Submit purchase
5. Verify:
   - Invoice displays with correct totals
   - VendorLedger created/updated
   - Stock incremented
   - No errors in logs

### Step 4: Monitor Logs
```bash
tail -f storage/logs/laravel.log
```

## 🐛 Troubleshooting

### "Something went wrong" Error

**Check Logs First:**
```bash
cat storage/logs/laravel.log | grep -i purchase
```

**Common Issues & Solutions:**

1. **Foreign Key Constraint Error**
   - **Cause:** Vendor not found
   - **Fix:** Verify vendor exists before purchase submission
   - **Log Entry:** `Illuminate\Database\QueryException`

2. **Vendor Not Selected**
   - **Cause:** Invalid vendor ID sent
   - **Fix:** Form validation catches this now
   - **Response:** 422 Validation Error with vendor error message

3. **Database Connection Error**
   - **Cause:** Hosting DB credentials incorrect
   - **Fix:** Check `.env` file DB_* variables
   - **Test:** `php artisan tinker` -> `DB::connection()->getPdo()`

4. **JSON Encoding Issues**
   - **Cause:** Non-UTF8 characters in item names
   - **Fix:** Ensure database collation is `utf8mb4_unicode_ci`
   - **Check:** `SELECT * FROM information_schema.TABLES WHERE TABLE_NAME = 'purchases';`

5. **Ledger Not Updating**
   - **Cause:** Old party_name-based query logic
   - **Fix:** Already fixed in new controller
   - **Verify:** VendorLedger has vendor_id foreign key

## 📊 Database Schema Verification

### Purchase Table
```sql
ALTER TABLE purchases ADD FOREIGN KEY (vendor_id) 
REFERENCES vendors(id) ON DELETE CASCADE;
```

### VendorLedger Columns
```
- id (Primary Key)
- vendor_id (Foreign Key -> vendors.id)
- opening_balance (Decimal 15,2)
- previous_balance (Decimal 15,2)
- closing_balance (Decimal 15,2)
- admin_or_user_id (Foreign Key -> users.id)
- created_at, updated_at, deleted_at
```

## 📈 ERP Compliance Checklist

✅ **Purchase Module:**
- [x] Unique invoice numbers (INVPURC-001, INVPURC-002...)
- [x] Vendor linked with foreign key
- [x] Ledger maintained for each vendor
- [x] Stock updated on purchase
- [x] JSON data for flexible item storage
- [x] Soft deletes for audit trail
- [x] Error handling and logging

✅ **Ledger System:**
- [x] Opening balance tracked
- [x] Previous balance maintained
- [x] Closing balance calculated
- [x] User/Admin associated with ledger
- [x] Audit timestamps

✅ **Data Integrity:**
- [x] Foreign key constraints
- [x] Cascading deletes
- [x] Type casting for numeric fields
- [x] Exception handling

## 🔍 Testing Purchase Flow

### Test Case 1: Create Purchase
```
Input: 
- Vendor: XYZ Corp
- Items: 
  - Product A: Qty 10, Rate 100 = 1000
  - Product B: Qty 5, Rate 50 = 250
- Grand Total: 1250

Expected Output:
- Purchase created with ID
- Invoice generated (INVPURC-XXX)
- VendorLedger updated with +1250 closing balance
- Stock increased for both products
```

### Test Case 2: View Invoice
```
Input: Click on created purchase invoice

Expected Output:
- Invoice displays with all items
- Correct gross total (1250)
- Correct net total (after discount if any)
- Vendor ledger information
```

### Test Case 3: Multiple Purchases
```
Input: Create 3 purchases for same vendor

Expected Output:
- Each has unique invoice number
- VendorLedger updated correctly each time
- Closing balance accumulates: 1250 -> 2500 -> 3750
```

## 📞 Support & Rollback

### If Issues Occur:
1. Check `storage/logs/laravel.log` for detailed errors
2. Verify database connection in `.env`
3. Ensure migrations ran successfully: `php artisan migrate:status`

### Rollback (if needed):
```bash
php artisan migrate:rollback
# Then fix issues and retry
php artisan migrate
```

## ✨ Performance Notes

- JSON fields used for item storage (no separate items table)
- Indexes on `vendor_id`, `party_code`, `invoice_number`
- Soft deletes for data preservation
- Ledger queries optimized with `.latest()` for most recent balance

---

**Last Updated:** June 15, 2026  
**Version:** 1.0 - Production Ready
