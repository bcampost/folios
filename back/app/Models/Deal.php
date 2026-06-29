<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Quote;

class Deal extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct();

        $this->table = config('database.connections.crm.database') . '.deals';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function dealable()
    {
        return $this->customer ?? $this->lead;
    }

    public function getDealableType(): ?string
    {
        if ($this->customer_id) {
            return 'customer';
        }

        if ($this->lead_id) {
            return 'lead';
        }

        return null;
    }

    public function resolveSelectedOrLastQuote(): ?Quote
    {
        if ($this->quote_id) {
            $selectedQuote = Quote::query()
                ->where('deal_id', $this->id)
                ->find($this->quote_id);

            if ($selectedQuote) {
                return $selectedQuote;
            }
        }

        return Quote::query()
            ->where('deal_id', $this->id)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->first();
    }

    public function getProjectValueAttribute()
    {
        return $this->resolveSelectedOrLastQuote()?->subtotal ?? $this->value;
    }

}
