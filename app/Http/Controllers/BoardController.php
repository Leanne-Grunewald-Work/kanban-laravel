<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Board;
use Illuminate\Validation\Rule;

class BoardController extends Controller
{
    public function index(Request $request): Response
    {
        $boards = $request->user()
            ->boards()
            ->select('id', 'name', 'position', 'created_at')
            ->orderBy('position')
            ->get();

        return Inertia::render('Boards/Index', [
            'boards' => $boards,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('boards', 'name')->where(fn ($q) => $q->where('user_id', $user->id)),
            ],
        ]);

        $nextPosition = (int) Board::where('user_id', $user->id)->max('position') + 1;

        Board::create([
            'user_id'   => $user->id,
            'name'      => $validated['name'],
            'position'  => $nextPosition,
        ]);

        return redirect()->route('boards.index')->with('success', 'Board Created');


    }
}
