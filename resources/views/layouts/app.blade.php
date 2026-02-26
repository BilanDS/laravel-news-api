<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel News API - Публічна стрічка</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .news-image {
            height: 200px;
            object-fit: cover;
        }

        .block-image {
            max-height: 400px;
            object-fit: contain;
            width: 100%;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">NewsPortal</a>

            <div class="d-flex align-items-center">
                <a href="/api/documentation" class="btn btn-outline-light btn-sm me-3" target="_blank">Swagger API</a>

                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm me-2">Моя панель</a>
                    <form method="POST" action="{{ route('logout.web') }}" class="d-inline mb-0">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm">Вийти</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-light btn-sm">Увійти</a>
                @endauth
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
