<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\News\StoreRequest;
use App\Http\Requests\News\UpdateRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class NewsController extends Controller
{
    #[OA\Get(
        path: '/api/dashboard/news',
        summary: 'Отримати список власних новин',
        description: 'Повертає пагінований список усіх новин (включаючи чернетки), створених поточним користувачем.',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard News']
    )]
    #[OA\Response(response: 200, description: 'Успішно')]
    #[OA\Response(response: 401, description: 'Неавторизовано')]
    public function index(Request $request): AnonymousResourceCollection
    {
        $news = $request->user()->news()
            ->with(['author', 'blocks' => fn($q) => $q->orderBy('order')])
            ->latest()
            ->paginate(10);

        return NewsResource::collection($news);
    }

    #[OA\Post(
        path: '/api/dashboard/news',
        summary: 'Створити нову новину',
        description: 'Створення новини з мультимедійними блоками через multipart/form-data.',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard News']
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
    public function store(StoreRequest $request): NewsResource
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

            if (! empty($validated['blocks'])) {
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

        $news->load(['author', 'blocks' => fn($q) => $q->orderBy('order')]);

        return new NewsResource($news);
    }

    #[OA\Get(
        path: '/api/dashboard/news/{id}',
        summary: 'Переглянути свою новину',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard News']
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Успішно')]
    public function show(News $news): NewsResource
    {
        Gate::authorize('view', $news);
        $news->load(['author', 'blocks' => fn($q) => $q->orderBy('order')]);

        return new NewsResource($news);
    }

    #[OA\Post(
        path: '/api/dashboard/news/{id}',
        summary: 'Оновити існуючу новину',
        description: 'Для оновлення з файлами використовуйте POST та поле _method=PUT. Можна передати deleted_blocks[] для видалення конкретних блоків.',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard News']
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
                    new OA\Property(property: 'deleted_blocks[0]', type: 'integer', description: 'ID блоку для видалення'),
                    new OA\Property(property: 'blocks[0][id]', type: 'integer', description: 'ID існуючого блоку (для оновлення)'),
                    new OA\Property(property: 'blocks[0][type]', type: 'string', example: 'text'),
                    new OA\Property(property: 'blocks[0][text_content]', type: 'string'),
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Оновлено')]
    public function update(UpdateRequest $request, News $news): NewsResource
    {
        Gate::authorize('update', $news);
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $news) {

            if ($request->hasFile('image')) {
                if ($news->image) {
                    Storage::disk('public')->delete($news->image);
                }
                $news->image = $request->file('image')->store('news', 'public');
            }

            $news->update([
                'title' => $validated['title'] ?? $news->title,
                'short_description' => $validated['short_description'] ?? $news->short_description,
                'is_published' => $validated['is_published'] ?? $news->is_published,
            ]);

            if (!empty($validated['deleted_blocks'])) {
                $blocksToDelete = $news->blocks()->whereIn('id', $validated['deleted_blocks'])->get();

                foreach ($blocksToDelete as $block) {
                    if ($block->image_path) {
                        Storage::disk('public')->delete($block->image_path);
                    }
                    $block->delete();
                }
            }

            if (isset($validated['blocks'])) {
                foreach ($validated['blocks'] as $index => $blockData) {
                    $blockId = $blockData['id'] ?? null;

                    if ($blockId) {
                        $block = $news->blocks()->find($blockId);

                        if ($block) {
                            $updateData = [
                                'type' => $blockData['type'],
                                'text_content' => $blockData['text_content'] ?? null,
                                'order' => $index,
                            ];

                            if ($request->hasFile("blocks.{$index}.image")) {
                                if ($block->image_path) {
                                    Storage::disk('public')->delete($block->image_path);
                                }
                                $updateData['image_path'] = $request->file("blocks.{$index}.image")->store('news_blocks', 'public');
                            }

                            $block->update($updateData);
                        }
                    } else {
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
            }
        });

        $news->load(['author', 'blocks' => fn($q) => $q->orderBy('order')]);

        return new NewsResource($news);
    }

    #[OA\Delete(
        path: '/api/dashboard/news/{id}',
        summary: 'Видалити новину',
        security: [['bearerAuth' => []]],
        tags: ['Dashboard News']
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Response(response: 200, description: 'Видалено')]
    public function destroy(News $news)
    {
        Gate::authorize('delete', $news);

        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        foreach ($news->blocks as $block) {
            if ($block->image_path) {
                Storage::disk('public')->delete($block->image_path);
            }
        }

        $news->delete();

        return response()->json(['message' => __('api.news_deleted')]);
    }
}
