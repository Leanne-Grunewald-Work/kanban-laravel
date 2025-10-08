<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;

class SubtaskController extends Controller
{
    public function store(Request $request, Board $board, Column $column, Task $task)
    {
        // Make sure task belongs to column which belongs to the board
        if ($task->column_id !== $column->id || $column->board_id !== $board->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title'         => [
                'required', 'string', 'max:255',
            ],
            'description'   => [
                'nullable', 'string',
            ],
            'due_date'      => [
                'nullable', 'date',
            ],
        ]);

        $nextPosition = (int) ($task->subtasks()->max('position') ?? 0) + 1;

        $task->subtasks()->create([
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'due_date'      => $validated['due_date'] ?? null,
            'position'      => $nextPosition,
        ]);

        return back();


    }
}
