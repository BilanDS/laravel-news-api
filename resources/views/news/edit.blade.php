@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3">Редагування новини</h1>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Назад до списку</a>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger shadow-sm">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('dashboard.news.update', $news) }}" method="POST" enctype="multipart/form-data"
                    id="news-form">
                    @csrf
                    @method('PUT')

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0 fw-bold">Основна інформація</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label fw-semibold">Заголовок</label>
                                <input type="text" name="title" id="title"
                                    class="form-control @error('title') is-invalid @enderror"
                                    value="{{ old('title', $news->title) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="short_description" class="form-label fw-semibold">Короткий опис</label>
                                <textarea name="short_description" id="short_description" rows="3"
                                    class="form-control @error('short_description') is-invalid @enderror" required>{{ old('short_description', $news->short_description) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label fw-semibold">Головне зображення</label>
                                @if ($news->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $news->image) }}" class="img-thumbnail"
                                            style="height: 120px; object-fit: cover;">
                                        <div class="form-text">Поточне зображення</div>
                                    </div>
                                @endif
                                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                            </div>

                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="is_published" value="0">
                                <input class="form-check-input" type="checkbox" name="is_published" id="is_published"
                                    value="1" {{ old('is_published', $news->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Опублікувати новину</label>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <h5 class="mb-0 fw-bold">Контентні блоки</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBlock('text')">+
                                    Текст</button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBlock('image')">+
                                    Фото</button>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="addBlock('text_image_right')">+ Текст|Фото</button>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick="addBlock('text_image_left')">+ Фото|Текст</button>
                            </div>
                        </div>

                        <div id="blocks-container" class="list-group list-group-flush">
                            @foreach ($news->blocks as $index => $block)
                                @php
                                    $blockType = is_object($block->type) ? $block->type->value : $block->type;
                                @endphp
                                <div class="list-group-item block-item p-4" data-index="{{ $index }}">
                                    <input type="hidden" name="blocks[{{ $index }}][id]"
                                        value="{{ $block->id }}">
                                    <input type="hidden" name="blocks[{{ $index }}][type]"
                                        value="{{ $blockType }}">

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="badge bg-light text-dark border px-3 py-2">
                                            Блок #{{ $index + 1 }}:
                                            @switch($blockType)
                                                @case('text')
                                                    Текст
                                                @break

                                                @case('image')
                                                    Зображення
                                                @break

                                                @case('text_image_right')
                                                    Текст + Фото (праворуч)
                                                @break

                                                @case('text_image_left')
                                                    Текст + Фото (ліворуч)
                                                @break

                                                @default
                                                    {{ $blockType }}
                                            @endswitch
                                        </span>
                                        <button type="button" class="btn-close shadow-none"
                                            onclick="removeExistingBlock(this, {{ $block->id }})"></button>
                                    </div>

                                    <div class="row">
                                        @if (in_array($blockType, ['text', 'text_image_right', 'text_image_left']))
                                            <div class="{{ $blockType === 'text' ? 'col-12' : 'col-md-7' }} mb-3">
                                                <textarea name="blocks[{{ $index }}][text_content]" class="form-control" rows="4"
                                                    placeholder="Текст блоку...">{{ old("blocks.$index.text_content", $block->text_content) }}</textarea>
                                            </div>
                                        @endif

                                        @if (in_array($blockType, ['image', 'text_image_right', 'text_image_left']))
                                            <div class="{{ $blockType === 'image' ? 'col-12' : 'col-md-5' }} mb-3">
                                                @if ($block->image_path)
                                                    <div class="mb-2">
                                                        <img src="{{ asset('storage/' . $block->image_path) }}"
                                                            class="img-thumbnail" style="max-height: 100px;">
                                                    </div>
                                                @endif
                                                <input type="file" name="blocks[{{ $index }}][image]"
                                                    class="form-control form-control-sm" accept="image/*">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="deleted-blocks-inputs"></div>
                    </div>

                    <div class="d-grid gap-2 mb-5">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">Зберегти всі зміни</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let blockCount = {{ $news->blocks->count() }};

        function addBlock(type) {
            const container = document.getElementById('blocks-container');
            const index = blockCount++;

            let label = 'Новий блок: ';
            switch (type) {
                case 'text':
                    label += 'Текст';
                    break;
                case 'image':
                    label += 'Зображення';
                    break;
                case 'text_image_right':
                    label += 'Текст + Фото';
                    break;
                case 'text_image_left':
                    label += 'Фото + Текст';
                    break;
            }

            const hasText = ['text', 'text_image_right', 'text_image_left'].includes(type);
            const hasImage = ['image', 'text_image_right', 'text_image_left'].includes(type);

            const html = `
            <div class="list-group-item block-item p-4 border-start border-primary border-4 bg-light" data-index="${index}">
                <input type="hidden" name="blocks[${index}][type]" value="${type}">
                <div class="d-flex justify-content-between mb-3">
                    <span class="badge bg-primary px-3 py-2">${label}</span>
                    <button type="button" class="btn-close" onclick="this.closest('.block-item').remove()"></button>
                </div>
                <div class="row">
                    ${hasText ? `<div class="${type === 'text' ? 'col-12' : 'col-md-7'} mb-3">
                            <textarea name="blocks[${index}][text_content]" class="form-control" rows="4" placeholder="Введіть текст..."></textarea>
                        </div>` : ''}
                    ${hasImage ? `<div class="${type === 'image' ? 'col-12' : 'col-md-5'} mb-3">
                            <input type="file" name="blocks[${index}][image]" class="form-control" accept="image/*" required>
                        </div>` : ''}
                </div>
            </div>
        `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function removeExistingBlock(button, id) {
            if (confirm('Видалити цей блок із бази даних?')) {
                const container = document.getElementById('deleted-blocks-inputs');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'deleted_blocks[]';
                input.value = id;
                container.appendChild(input);
                button.closest('.block-item').remove();
            }
        }
    </script>
@endsection
