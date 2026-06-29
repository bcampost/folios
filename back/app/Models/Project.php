<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $casts = [
        'payment_by_customer_platform' => 'boolean'
    ];

    public function owner() : BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function customer() : BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function deal() : BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function branch() : BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function folios() : HasMany
    {
        return $this->hasMany(Folio::class);
    }

    public function paymentTerm() : BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term_id');
    }
}
