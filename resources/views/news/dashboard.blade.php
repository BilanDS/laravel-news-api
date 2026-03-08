@extends('layouts.app')

@section('content')
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-0">Панель керування: Мої новини</h1>
            <p class="text-muted">Перегляд статусу ваших публікацій</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('dashboard.news.create') }}" class="btn btn-primary shadow-sm me-2">
                + Створити новину
            </a>
            <a href="/api/documentation" target="_blank" class="btn btn-warning shadow-sm">
                Керувати API
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Назва новини</th>
                            <th>Статус</th>
                            <th>Кількість блоків</th>
                            <th>Дата створення</th>
                            <th class="text-end pe-4">Дія</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($news as $item)
                            <tr>
                                <td class="ps-4">
                                    <a href="{{ route('news.show', $item) }}" class="text-decoration-none text-dark">
                                        <strong>{{ $item->title }}</strong>
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($item->short_description, 50) }}</small>
                                </td>
                                <td>
                                    @if ($item->is_published)
                                        <span class="badge bg-success">Опубліковано</span>
                                    @else
                                        <span class="badge bg-secondary">Чернетка</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $item->blocks_count ?? $item->blocks->count() }} блоків
                                    </span>
                                </td>
                                <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                                <td class="text-end pe-4">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('news.show', $item) }}" class="btn btn-sm btn-outline-info">
                                            Переглянути
                                        </a>

                                        <a href="{{ route('dashboard.news.edit', $item) }}"
                                            class="btn btn-sm btn-outline-primary" title="Редагувати">
                                            Редагувати
                                        </a>

                                        <form action="{{ route('dashboard.news.destroy', $item) }}" method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Ви впевнені, що хочете видалити цю новину?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Видалити">
                                                Видалити
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Ви ще не створили жодної новини.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $news->links('pagination::bootstrap-5') }}
    </div>
@endsection
