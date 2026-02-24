<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\UpdateNewsRequest;
use OpenApi\Attributes as OA;

class UserNewsController extends Controller
{
    #[OA\Get(
        path: '/api/my-news',
        summary: 'Отримати список власних новин',
        description: 'Повертає пагінований список усіх новин (включаючи чернетки), створених поточним користувачем.',
        security: [['bearerAuth' => []]],
        tags: ['My News']
    )]
    #[OA\Response(response: 200, description: 'Успішно')]
    #[OA\Response(response: 401, description: 'Неавторизовано')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $news = $request->user()->news()
            ->with(['author', 'blocks'])
            ->latest()
            ->paginate(10);

        return NewsResource::collection($news);
    }

    #[OA\Post(
        path: '/api/my-news',
        summary: 'Створити нову новину',
        description: 'Створення новини з мультимедійними блоками через multipart/form-data.',
        security: [['bearerAuth' => []]],
        tags: ['My News']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['title', 'short_description', 'is_published'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Нова новина'),
                    new OA\Property(property: 'short_description', type: 'string', example: 'Короткий опис'),
                    new OA\Property(property: 'is_published', type: 'boolean', example: 1),
                    new OA\Property(property: 'image', type: 'string', format: 'binary'),
                    new OA\Property(property: 'blocks[0][type]', type: 'string', example: 'text'),
                    new OA\Property(property: 'blocks[0][text_content]', type: 'string', example: 'Зміст блоку'),
                    new OA\Property(property: 'blocks[1][type]', type: 'string', example: 'image'),
                    new OA\Property(property: 'blocks[1][image]', type: 'string', format: 'binary'),
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Створено')]
    public function store(StoreNewsRequest $request): NewsResource
    {
        $validated = $request->validated();
        $user = $request->user();

        $news = DB::transaction(function () use ($validated, $user, $request) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('news', 'public');
            }

            $news = $user->news()->create([
                'title' => $validated['title'],
                'short_description' => $validated['short_description'],
                'image' => $imagePath,
                'is_published' => $validated['is_published'] ?? false,
            ]);

            if (!empty($validated['blocks'])) {
                foreach ($validated['blocks'] as $index => $blockData) {
                    $blockImagePath = null;
                    if (isset($blockData['image']) && $request->hasFile("blocks.{$index}.image")) {
                        $blockImagePath = $request->file("blocks.{$index}.image")->store('news_blocks', 'public');
                    }

                    $news->blocks()->create([
                        'type' => $blockData['type'],
                        'text_content' => $blockData['text_content'] ?? null,
                        'image_path' => $blockImagePath,
                        'order' => $index,
                    ]);
                }
            }
            return $news;
        });

        $news->load(['author', 'blocks']);
        return new NewsResource($news);
    }

    #[OA\Get(
        path: '/api/my-news/{id}',
        summary: 'Переглянути свою новину',
        security: [['bearerAuth' => []]],
        tags: ['My News']
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Успішно')]
    public function show(News $my_news): NewsResource
    {
        Gate::authorize('view', $my_news);
        $my_news->load(['author', 'blocks']);
        return new NewsResource($my_news);
    }

    #[OA\Post(
        path: '/api/my-news/{id}',
        summary: 'Оновити існуючу новину',
        description: 'Для оновлення з файлами використовуйте POST та поле _method=PUT.',
        security: [['bearerAuth' => []]],
        tags: ['My News']
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: '_method', type: 'string', example: 'PUT'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'blocks[0][type]', type: 'string', example: 'text'),
                    new OA\Property(property: 'blocks[0][text_content]', type: 'string'),
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Оновлено')]
    public function update(UpdateNewsRequest $request, News $my_news): NewsResource
    {
        Gate::authorize('update', $my_news);
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $my_news) {
            if ($request->hasFile('image')) {
                if ($my_news->image) {
                    Storage::disk('public')->delete($my_news->image);
                }
                $my_news->image = $request->file('image')->store('news', 'public');
            }

            $my_news->update([
                'title' => $validated['title'] ?? $my_news->title,
                'short_description' => $validated['short_description'] ?? $my_news->short_description,
                'is_published' => $validated['is_published'] ?? $my_news->is_published,
            ]);

            if ($request->has('blocks')) {
                foreach ($my_news->blocks as $block) {
                    if ($block->image_path) {
                        Storage::disk('public')->delete($block->image_path);
                    }
                }
                $my_news->blocks()->delete();

                foreach ($validated['blocks'] as $index => $blockData) {
                    $blockImagePath = null;
                    if (isset($blockData['image']) && $request->hasFile("blocks.{$index}.image")) {
                        $blockImagePath = $request->file("blocks.{$index}.image")->store('news_blocks', 'public');
                    }

                    $my_news->blocks()->create([
                        'type' => $blockData['type'],
                        'text_content' => $blockData['text_content'] ?? null,
                        'image_path' => $blockImagePath,
                        'order' => $index,
                    ]);
                }
            }
        });

        $my_news->load(['author', 'blocks']);
        return new NewsResource($my_news);
    }

    #[OA\Delete(
        path: '/api/my-news/{id}',
        summary: 'Видалити новину',
        security: [['bearerAuth' => []]],
        tags: ['My News']
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Видалено')]
    public function destroy(News $my_news)
    {
        Gate::authorize('delete', $my_news);

        if ($my_news->image) {
            Storage::disk('public')->delete($my_news->image);
        }

        foreach ($my_news->blocks as $block) {
            if ($block->image_path) {
                Storage::disk('public')->delete($block->image_path);
            }
        }

        $my_news->delete();
        return response()->json(['message' => 'Новину успішно видалено']);
    }
}
