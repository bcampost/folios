<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct();

        $this->table = config('database.connections.crm.database') . '.customers';
    }
}
