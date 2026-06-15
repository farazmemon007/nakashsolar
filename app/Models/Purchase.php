<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'item' => 'array',
        'rate' => 'array',
        'product_mode' => 'array',
        'pcs' => 'array',
        'discount' => 'array',
        'amount' => 'array',
        'pcs_carton' => 'array',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function vendorLedger()
    {
        return $this->hasOne(VendorLedger::class, 'vendor_id', 'vendor_id')->orderBy('id', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_or_user_id', 'id');
    }

    /**
     * Generate unique invoice number for purchase
     * Handles both active and soft-deleted records
     */
    public static function generateInvoiceNo()
    {
        $prefix = 'INVPURC-';
        
        // Get all invoice numbers (including soft-deleted) to find the highest
        $allInvoices = self::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->pluck('invoice_number')
            ->map(function ($invoice) use ($prefix) {
                return (int) substr($invoice, strlen($prefix));
            });
        
        // Get the highest number from all invoices
        $lastNumber = $allInvoices->isNotEmpty() ? $allInvoices->max() : 0;
        
        // Generate new number, ensuring it's always incrementing
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
    }

    /**
     * Get decoded items
     */
    public function getItemsArray()
    {
        return json_decode($this->item, true) ?? [];
    }

    /**
     * Get decoded rates
     */
    public function getRatesArray()
    {
        return json_decode($this->rate, true) ?? [];
    }

    /**
     * Get decoded amounts
     */
    public function getAmountsArray()
    {
        return json_decode($this->amount, true) ?? [];
    }

    /**
     * Get decoded discounts
     */
    public function getDiscountsArray()
    {
        return json_decode($this->discount, true) ?? [];
    }

    /**
     * Get total gross amount
     */
    public function getGrossTotal()
    {
        return array_sum($this->getAmountsArray());
    }

    /**
     * Get total discount
     */
    public function getTotalDiscount()
    {
        return array_sum($this->getDiscountsArray());
    }

    /**
     * Get net total after discount
     */
    public function getNetTotal()
    {
        return $this->getGrossTotal() - $this->getTotalDiscount();
    }
}
