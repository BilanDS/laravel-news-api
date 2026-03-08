@extends('layouts.app')

@section('content')
    <div class="container mb-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <nav class="mb-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-link ps-0 text-decoration-none text-secondary">
                        Назад до панелі керування
                    </a>
                </nav>

                <header class="mb-5">
                    <h1 class="display-4 fw-bold mb-3">{{ $news->title }}</h1>
                    <p class="lead text-muted mb-4">{{ $news->short_description }}</p>

                    <div class="d-flex align-items-center text-muted small">
                        <span class="me-3">{{ $news->created_at->format('d.m.Y H:i') }}</span>
                        <span>Автор: {{ $news->author->name ?? 'Анонім' }}</span>
                        @if (!$news->is_published)
                            <span class="badge bg-warning text-dark ms-3">Чернетка</span>
                        @endif
                    </div>
                </header>

                @if ($news->image)
                    <div class="mb-5 text-center">
                        <img src="{{ asset('storage/' . $news->image) }}" class="img-fluid rounded-4 shadow-sm"
                            style="max-height: 500px; width: 100%; object-fit: cover;" alt="{{ $news->title }}">
                    </div>
                @endif

                <hr class="my-5 opacity-25">

                <article class="news-content">
                    @forelse ($news->blocks as $block)
                        <div class="mb-5">
                            @switch($block->type->value)
                                @case('text')
                                    <div class="fs-5 lh-lg">
                                        {!! nl2br(e($block->text_content)) !!}
                                    </div>
                                @break

                                @case('image')
                                    @if ($block->image_path)
                                        <div class="text-center">
                                            <img src="{{ asset('storage/' . $block->image_path) }}"
                                                class="img-fluid rounded-3 shadow-sm" alt="Зображення">
                                        </div>
                                    @endif
                                @break

                                @case('text_image_right')
                                    <div class="row align-items-center g-4">
                                        <div class="col-md-7 fs-5 lh-lg">
                                            {!! nl2br(e($block->text_content)) !!}
                                        </div>
                                        <div class="col-md-5">
                                            @if ($block->image_path)
                                                <img src="{{ asset('storage/' . $block->image_path) }}"
                                                    class="img-fluid rounded-3 shadow-sm" alt="Зображення">
                                            @endif
                                        </div>
                                    </div>
                                @break

                                @case('text_image_left')
                                    <div class="row align-items-center g-4">
                                        <div class="col-md-5 order-2 order-md-1">
                                            @if ($block->image_path)
                                                <img src="{{ asset('storage/' . $block->image_path) }}"
                                                    class="img-fluid rounded-3 shadow-sm" alt="Зображення">
                                            @endif
                                        </div>
                                        <div class="col-md-7 order-1 order-md-2 fs-5 lh-lg">
                                            {!! nl2br(e($block->text_content)) !!}
                                        </div>
                                    </div>
                                @break

                                @default
                                    <div class="alert alert-light border small text-muted">
                                        Блок типу "{{ $block->type->value ?? 'unknown' }}" не підтримується.
                                    </div>
                            @endswitch
                        </div>
                        @empty
                            <div class="text-center py-5">
                                <p class="text-muted">Зміст новини відсутній.</p>
                            </div>
                        @endforelse
                    </article>
                </div>
            </div>
        </div>
    @endsection
