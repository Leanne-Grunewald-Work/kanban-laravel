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
        $user = $request->user();
        $selectedBoard = $request->integer('selectedBoard');

        $boards = $user
            ->boards()
            ->select('id', 'name', 'position', 'created_at')
            ->orderBy('position')
            ->get();

        return Inertia::render('Boards/Index', [
            'boards' => $boards,
            'selectedBoard' => fn() => $selectedBoard
                ? Board::where('user_id', $user->id)
                    ->where('id', $selectedBoard)
                    ->with([
                        'columns'                   => fn($q) => $q->orderBy('position'),
                        'columns.tasks'             => fn($q) => $q->orderBy('position'),
                        'columns.tasks.subtasks'    => fn($q) => $q->orderBy('position'),
                    ])
                    ->first()
                : null,
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

        $nextPosition = (int) (($user->boards()->max('position')) ?? 0) + 1;

        $board = Board::create([
            'user_id'   => $user->id,
            'name'      => $validated['name'],
            'position'  => $nextPosition,
        ]);

        return redirect()->route('boards.index', ['selectedBoard' => $board->id])->with('success', 'Board Created');

    }

    public function show(Board $board)
    {
        $board->load([
            'columns'                   => fn($q) => $q->orderBy('position'),
            'columns.tasks'             => fn($q) => $q->orderBy('position'),
            'columns.tasks.subtasks'    => fn($q) => $q->orderBy('position'),
        ]);

        return Inertia::render('Boards/Show', [
            'board' => $board,
        ]);
    }
}
