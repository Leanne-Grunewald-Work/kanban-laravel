<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;
use App\Models\Column;

class TaskController extends Controller
{
    public function store(Request $request, Board $board, Column $column)
    {
        // Make sure column belongs to the board
        if ($column->board_id !== $board->id) {
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

        $nextPosition = (int) ($column->tasks()->max('position') ?? 0) + 1;

        $board->tasks()->create([
            'column_id'     => $column->id,
            'title'         => $validated['title'],
            'description'   => $validated['description'] ?? null,
            'due_date'      => $validated['due_date'] ?? null,
            'position'      => $nextPosition,
        ]);

        return back();


    }
}
