<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Task;

class Subtask extends Model
{
    protected $fillable = ['task_id', 'title', 'description', 'is_completed', 'position', 'due_date'];

    protected $casts = [
        'due_date' => 'date',
        'position' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
