@extends('layouts.app')

@section('content')
    <div class="container mb-5">
        <div class="row align-items-center mb-4">
            <div class="col-md-8">
                <h1 class="h3 mb-0">Створення новини</h1>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Скасувати</a>
            </div>
        </div>

        <form id="create-news-form" enctype="multipart/form-data">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Основна інформація</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Назва новини <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" required>
                        <div class="invalid-feedback" id="error-title"></div>
                    </div>

                    <div class="mb-3">
                        <label for="short_description" class="form-label">Короткий опис <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="short_description" name="short_description" rows="2" required></textarea>
                        <div class="invalid-feedback" id="error-short_description"></div>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Головне зображення (опціонально)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <div class="invalid-feedback" id="error-image"></div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published"
                            value="1">
                        <label class="form-check-label" for="is_published">Опублікувати відразу</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Блоки контенту</h4>
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        + Додати блок
                    </button>
                    <ul class="dropdown-menu">
                        <li><button type="button" class="dropdown-item" onclick="addBlock('text')">Лише текст</button></li>
                        <li><button type="button" class="dropdown-item" onclick="addBlock('image')">Лише картинка</button>
                        </li>
                        <li><button type="button" class="dropdown-item" onclick="addBlock('text_image_right')">Текст +
                                картинка праворуч</button></li>
                        <li><button type="button" class="dropdown-item" onclick="addBlock('text_image_left')">Текст +
                                картинка ліворуч</button></li>
                    </ul>
                </div>
            </div>

            <div id="blocks-container"></div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button type="submit" class="btn btn-success btn-lg px-5" id="submit-btn">
                    <span class="spinner-border spinner-border-sm d-none" id="submit-spinner" role="status"
                        aria-hidden="true"></span>
                    Зберегти новину
                </button>
            </div>
        </form>
    </div>

    <script>
        let blockIndex = 0;

        function addBlock(type) {
            const container = document.getElementById('blocks-container');

            const typeNames = {
                'text': 'Лише текст',
                'image': 'Лише картинка',
                'text_image_right': 'Текст + картинка (праворуч)',
                'text_image_left': 'Текст + картинка (ліворуч)'
            };

            let html = `
            <div class="card mb-3 border-info content-block" data-index="${blockIndex}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Блок: ${typeNames[type]}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('.content-block').remove()">Видалити блок</button>
                </div>
                <div class="card-body">
                    <input type="hidden" name="blocks[${blockIndex}][type]" value="${type}">
        `;

            if (type.includes('text')) {
                html += `
                <div class="mb-3">
                    <label class="form-label">Зміст тексту</label>
                    <textarea class="form-control" name="blocks[${blockIndex}][text_content]" rows="3" required></textarea>
                </div>
            `;
            }

            if (type.includes('image')) {
                html += `
                <div class="mb-3">
                    <label class="form-label">Зображення для блоку</label>
                    <input type="file" class="form-control" name="blocks[${blockIndex}][image]" accept="image/*" required>
                </div>
            `;
            }

            html += `
                </div>
            </div>
        `;

            container.insertAdjacentHTML('beforeend', html);
            blockIndex++;
        }

        document.getElementById('create-news-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = document.getElementById('submit-btn');
            const spinner = document.getElementById('submit-spinner');

            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            submitBtn.disabled = true;
            spinner.classList.remove('d-none');

            const formData = new FormData(form);

            if (!form.querySelector('#is_published').checked) {
                formData.set('is_published', 0);
            }

            try {
                const response = await fetch('/api/dashboard/news', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    window.location.href = '{{ route('dashboard') }}';
                } else if (response.status === 422) {
                    for (const [field, messages] of Object.entries(result.errors)) {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const errorDiv = document.getElementById(`error-${field}`);
                            if (errorDiv) errorDiv.textContent = messages[0];
                        } else {
                            alert('Помилка валідації: ' + messages[0]);
                        }
                    }
                } else {
                    alert('Помилка сервера: ' + (result.message || 'Невідома помилка'));
                }
            } catch (error) {
                console.error('Помилка:', error);
                alert('Сталася помилка при відправці запиту.');
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
            }
        });
    </script>
@endsection
