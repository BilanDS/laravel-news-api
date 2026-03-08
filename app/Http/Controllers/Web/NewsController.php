<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsController extends Controller
{
    /**
     * Display a listing of the published news.
     */
    public function index(Request $request)
    {
        $news = News::with(['author'])
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

    /**
     * Display the specified news item.
     */
    public function show(News $news)
    {
        if (! $news->is_published && Auth::id() !== $news->user_id) {
            abort(404, __('api.news_not_found'));
        }

        $news->loadMissing([
            'author',
            'blocks' => fn ($query) => $query->orderBy('order'),
        ]);

        return view('news.show', compact('news'));
    }
}
