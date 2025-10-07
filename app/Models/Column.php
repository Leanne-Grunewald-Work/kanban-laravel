<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Column extends Model
{
    protected $fillable = ['board_id', 'name', 'position'];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }
}
