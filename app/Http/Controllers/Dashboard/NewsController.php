<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Requests\News\UpdateRequest;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    use AuthorizesRequests;

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

    public function create()
    {
        $this->authorize('create', News::class);
        return view('news.create');
    }

    public function edit(News $news)
    {
        $this->authorize('update', $news);

        $news->load(['blocks' => fn($q) => $q->orderBy('order')]);

        return view('news.edit', compact('news'));
    }

    public function update(UpdateRequest $request, News $news)
    {
        $this->authorize('update', $news);
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
                        if ($request->hasFile("blocks.{$index}.image")) {
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

        return redirect()->route('dashboard')
            ->with('success', 'Новину успішно оновлено!');
    }

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

        return redirect()->route('dashboard')
            ->with('success', 'Новину успішно видалено разом із усіма файлами.');
    }
}
