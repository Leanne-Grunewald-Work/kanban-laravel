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

        abort_if($board->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('columns', 'name')->where(fn ($q) => $q->where('board_id', $board->id)),
            ],
            'colour' => [
                'nullable', 'regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/'
            ],
        ]);

        $nextPosition = (int) ($board->columns()->max('position') ?? 0) + 1;

        $board->columns()->create([
            'name'      => $validated['name'],
            'position'  => $nextPosition,
            'colour'    => $validated['colour'] ?? null,
        ]);

        return back()->with(['success' => 'Column created']);


    }
}
