<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $news = $user->news()
            ->with(['author'])
            ->withCount('blocks')
            ->latest()
            ->paginate(10);

        return view('news.dashboard', compact('news'));
    }
}
