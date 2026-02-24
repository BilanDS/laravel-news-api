<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Http\Resources\NewsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class PublicNewsController extends Controller
{
    #[OA\Get(
        path: '/api/news',
        summary: 'Отримати список усіх опублікованих новин',
        description: 'Дозволяє шукати новини за текстом та фільтрувати за автором.',
        tags: ['Public News']
    )]
    #[OA\Parameter(
        name: 'search',
        in: 'query',
        description: 'Пошук за заголовком або коротким описом',
        required: false,
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'author_id',
        in: 'query',
        description: 'ID автора (користувача)',
        required: false,
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 200,
        description: 'Успішна відповідь із пагінацією'
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $news = News::query()
            ->where('is_published', true)
            ->with(['author', 'blocks'])

            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                });
            })

            ->when($request->author_id, function ($query, $authorId) {
                $query->where('user_id', $authorId);
            })

            ->latest()
            ->paginate(10);

        return NewsResource::collection($news);
    }

    #[OA\Get(
        path: '/api/news/{id}',
        summary: 'Переглянути конкретну публічну новину',
        tags: ['Public News']
    )]
    #[OA\Parameter(
        name: 'id',
        in: 'path',
        required: true,
        description: 'ID новини',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(response: 200, description: 'Успішно')]
    #[OA\Response(response: 404, description: 'Новину не знайдено або вона ще не опублікована')]
    public function show(News $news): NewsResource
    {
        if (!$news->is_published) {
            abort(404, 'Новину не знайдено або вона прихована.');
        }

        $news->load(['author', 'blocks']);

        return new NewsResource($news);
    }
}
