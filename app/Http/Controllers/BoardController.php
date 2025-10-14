<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use App\Models\Board;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    public function welcome(Request $request)
    {
        $user = $request->user();

        $boards = Board::where('user_id', $user->id)
            ->orderBy('position')
            ->get(['id', 'name', 'position', 'created_at']);

        $selectedId = $request->integer('selectedBoard');
        if (!$selectedId && $boards->isNotEmpty()) 
        {
            $selectedId = (int) $boards->first()->id;
        }

        $selectedBoard = null;
        if ($selectedId)
        {
            $selectedBoard = Board::where('user_id', $user->id)
                ->where('id', $selectedId)
                ->with(['columns' => fn($q) => $q->orderBy('position')])
                ->first();
        }

        return Inertia::render('Welcome', [
            'boards'            => $boards,
            'selectedBoard'     => $selectedBoard,
        ]);
    }

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
            'name'      =>  [
                'required', 'string', 'max:100',
                Rule::unique('boards', 'name')->where(fn ($q) => $q->where('user_id', $user->id)),
            ],
            'columns'   =>  ['nullable', 'array'],
            'columns.*' =>  ['nullable', 'string', 'max:100'],
        ]);

        return DB::transaction(function () use ($user, $validated)
        {
            $nextPosition = (int) (($user->boards()->max('position')) ?? 0) + 1;

            $board = Board::create([
                'user_id'   => $user->id,
                'name'      => $validated['name'],
                'position'  => $nextPosition,
            ]);

            $columns = collect($validated['columns'] ?? [])
                ->map(fn($column) => trim((string) $column))
                ->filter(fn($column) => $column !== '')
                ->values();

            foreach ($columns as $column => $columnName) 
            {
                $board->columns()->create([
                    'name'      => $columnName,
                    'position'  => $column + 1,   
                ]);
            }

            return redirect()->route('home', ['selectedBoard' => $board->id])->with('success', 'Board Created');
        });

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
