<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VendorLedger extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];
    protected $casts = [
        'opening_balance' => 'float',
        'previous_balance' => 'float',
        'closing_balance' => 'float',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'admin_or_user_id', 'id');
    }

    /**
     * Get the balance due (money owed by vendor)
     */
    public function getBalanceDue()
    {
        return $this->closing_balance;
    }

    /**
     * Get the payment made
     */
    public function getPaymentMade()
    {
        return max(0, $this->opening_balance - $this->closing_balance);
    }
}
