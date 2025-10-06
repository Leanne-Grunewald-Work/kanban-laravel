<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

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
}
