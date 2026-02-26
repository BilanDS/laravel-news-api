@extends('layouts.app')

@section('content')
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Останні новини</h1>
        </div>
        <div class="col-md-6">
            <form action="{{ route('home') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Пошук новин..."
                    value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary">Знайти</button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        @forelse($news as $item)
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    @if ($item->image)
                        <img src="{{ Str::startsWith($item->image, 'http') ? $item->image : asset('storage/' . $item->image) }}"
                            class="card-img-top news-image" alt="{{ $item->title }}">
                    @else
                        <div
                            class="card-img-top news-image bg-secondary d-flex align-items-center justify-content-center text-white">
                            Немає фото
                        </div>
                    @endif
                    <div class="card-body">
                        <h5 class="card-title">{{ $item->title }}</h5>
                        <p class="card-text text-muted">{{ Str::limit($item->short_description, 100) }}</p>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center">
                        <small class="text-muted">{{ $item->author->name }}</small>
                        <a href="{{ route('news.show', $item->id) }}" class="btn btn-sm btn-outline-primary">Читати</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <h3 class="text-muted">Новин не знайдено</h3>
            </div>
        @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $news->links('pagination::bootstrap-5') }}
    </div>
@endsection
