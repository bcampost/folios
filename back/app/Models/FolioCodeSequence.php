<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FolioCodeSequence extends Model
{
    protected $fillable = [
        'type',
        'last_number',
    ];

    public static function getNextNumber(string $type): int
    {
        return DB::transaction(function () use ($type) {
            $sequence = static::lockForUpdate()
                ->where('type', $type)
                ->firstOrFail();

            $sequence->increment('last_number');
            $sequence->refresh();

            return $sequence->last_number;
        });
    }

    public static function peekNextNumber(string $type): int
    {
        $sequence = static::where('type', $type)->firstOrFail();

        return $sequence->last_number + 1;
    }
}
