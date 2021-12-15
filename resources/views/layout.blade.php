<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="/css/app.css" rel="stylesheet">
    <title>{{ env('APP_NAME')  }}</title>
</head>
<body>
    <header class="flex-shrink-0">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark">
            <a class="navbar-brand" href="{{route('urls.main')}}">{{env('APP_NAME')}}</a>
            <button
                class="navbar-toggler"
                type="button"
                data-toggle="collapse"
                data-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a @class(["nav-link", 'active' => route('urls.main') === Request::url()]) href="{{route('urls.main')}}">Главная</a>
                    </li>
                    <li class="nav-item">
                        <a @class(["nav-link", 'active' => route('urls.index') === Request::url()]) href="{{route('urls.index')}}">Сайты</a>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="flex-grow-1">
        @if(session('success'))
            <div class="alert alert-info" role="alert">
                {{session('success')}}
            </div>
        @endif
        @if(session('errors'))
            <div class="alert alert-danger" role="alert">
                {{session('errors')->first()}}
            </div>
        @endif
        @if(session('status'))
            <div class="alert alert-info" role="alert">
                {{session('status')}}
            </div>
        @endif
        @yield('content')
    </main>
<script src="/js/app.js"></script>
</body>
