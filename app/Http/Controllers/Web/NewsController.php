<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with('author')
            ->where('is_published', true)
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(9);

        return view('news.index', compact('news'));
    }

    public function show(News $news)
    {
        if (!$news->is_published) {
            if (\Illuminate\Support\Facades\Auth::id() !== $news->user_id) {
                abort(404, 'Новину не знайдено');
            }
        }

        $news->load(['author', 'blocks' => function ($query) {
            $query->orderBy('order');
        }]);

        return view('news.show', compact('news'));
    }

    public function dashboard()
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();

        $news = $user->news()
            ->withCount('blocks')
            ->latest()
            ->paginate(10);

        return view('news.dashboard', compact('news'));
    }
}
