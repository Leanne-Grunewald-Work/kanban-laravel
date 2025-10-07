<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Board;

class ColumnController extends Controller
{
    public function store(Request $request, Board $board)
    {
        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('columns', 'name')->where(fn ($q) => $q->where('board_id', $board->id)),
            ],
        ]);

        $nextPosition = (int) ($board->columns()->max('position') ?? 0) + 1;

        $board->columns()->create([
            'name'      => $validated['name'],
            'position'  => $nextPosition,
        ]);

        return back();


    }
}
