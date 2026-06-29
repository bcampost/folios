<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FolioState extends Model
{
    use HasFactory, ForwardsCalls;

    // public function __call($name, $arguments)
    // {
    //     return $this->forwardCallTo($this->state, $name, $arguments);
    // }
}
