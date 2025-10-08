<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Board;
use App\Models\Column;
use App\Models\Subtask;

class Task extends Model
{
    protected $fillable = ['board_id', 'column_id', 'title', 'description', 'position', 'due_date'];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(Subtask::class)->orderBy('position');
    }
}
