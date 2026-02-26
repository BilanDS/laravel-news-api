@extends('layouts.app')

@section('content')
    <div class="mb-3">
        <a href="{{ route('home') }}" class="text-decoration-none">&larr; Назад до списку</a>
    </div>

    <div class="card shadow-sm">
        @if ($news->image)
            <img src="{{ Str::startsWith($news->image, 'http') ? $news->image : asset('storage/' . $news->image) }}"
                class="card-img-top" alt="{{ $news->title }}" style="max-height: 400px; object-fit: cover;">
        @endif

        <div class="card-body p-5">
            <h1 class="mb-3">
                {{ $news->title }}
                @if (!$news->is_published)
                    <span class="badge bg-secondary fs-6 align-middle ms-2">Чернетка</span>
                @endif
            </h1>
            <div class="d-flex justify-content-between text-muted mb-4 border-bottom pb-3">
                <span>Автор: <strong>{{ $news->author->name }}</strong></span>
                <span>Опубліковано: {{ $news->created_at->format('d.m.Y H:i') }}</span>
            </div>

            <p class="lead mb-5">{{ $news->short_description }}</p>

            @foreach ($news->blocks as $block)
                <div class="content-block mb-4">

                    @if ($block->type === 'text')
                        <div class="text-content">
                            {!! nl2br(e($block->text_content)) !!}
                        </div>
                    @elseif($block->type === 'image')
                        <div class="text-center my-4">
                            <img src="{{ Str::startsWith($block->image_path, 'http') ? $block->image_path : asset('storage/' . $block->image_path) }}"
                                class="block-image shadow-sm" alt="Зображення блоку">
                        </div>
                    @elseif($block->type === 'text_image_right')
                        <div class="row align-items-center my-4">
                            <div class="col-md-7">
                                {!! nl2br(e($block->text_content)) !!}
                            </div>
                            <div class="col-md-5 text-center">
                                <img src="{{ Str::startsWith($block->image_path, 'http') ? $block->image_path : asset('storage/' . $block->image_path) }}"
                                    class="img-fluid rounded shadow-sm" alt="Зображення">
                            </div>
                        </div>
                    @elseif($block->type === 'text_image_left')
                        <div class="row align-items-center my-4">
                            <div class="col-md-5 text-center order-2 order-md-1">
                                <img src="{{ Str::startsWith($block->image_path, 'http') ? $block->image_path : asset('storage/' . $block->image_path) }}"
                                    class="img-fluid rounded shadow-sm" alt="Зображення">
                            </div>
                            <div class="col-md-7 order-1 order-md-2 mb-3 mb-md-0">
                                {!! nl2br(e($block->text_content)) !!}
                            </div>
                        </div>
                    @endif

                </div>
            @endforeach

        </div>
    </div>
@endsection
