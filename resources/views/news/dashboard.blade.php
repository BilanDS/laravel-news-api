@extends('layouts.app')

@section('content')
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Панель керування: Мої новини</h1>
            <p class="text-muted">Перегляд статусу ваших публікацій</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="/api/documentation" target="_blank" class="btn btn-warning shadow-sm">
                Керувати контентом через API
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
                                    <strong>{{ $item->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit($item->short_description, 50) }}</small>
                                </td>
                                <td>
                                    @if ($item->is_published)
                                        <span class="badge bg-success">Опубліковано</span>
                                    @else
                                        <span class="badge bg-secondary">Приховано (Чернетка)</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $item->blocks_count }} блоків</span>
                                </td>
                                <td>{{ $item->created_at->format('d.m.Y H:i') }}</td>
                                <td class="text-end pe-4">
                                    <a href="{{ route('news.show', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                        Переглянути
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Ви ще не створили жодної новини. Зробіть це через API!
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
