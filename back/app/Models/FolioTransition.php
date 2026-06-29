<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FolioTransition extends Model
{
    use HasFactory;

    protected $table = 'folio_transitions';

    public function prevState() : BelongsTo
    {
        return $this->belongsTo(FolioState::class, 'prev_state_id');
    }

    public function nextState() : BelongsTo
    {
        return $this->belongsTo(FolioState::class, 'next_state_id');
    }

    public function folio() : BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
