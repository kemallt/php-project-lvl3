@extends('layout')
@section('content')
    @if($errors->any())
        <div class="alert alert-danger" role="alert">
            Некорректный URL
        </div>
    @endif
    <main class="flex-grow-1">
        <div class="jumbotron jubmotron-fluid bg-dark">
            <div class="container-lg">
                <div class="row">
                    <div class="col-12 col-md-10 col-lg-8 mx-auto text-white">
                        <h1 class="display-3">{{ env('APP_NAME') }}</h1>
                        <p class="lead">Бесплатно проверяйте сайты на SEO пригодность</p>
                        <form action="{{ route('urls.store') }}" method="post" class="d-flex justify-content-center">
                            @csrf
                            <input type="text" name="url[name]" class="form-control form-control-lg" placeholder="https://www.example.com">
                            <button type="submit" class="btn btn-lg btn-primary ml-3 px-5 text-uppercase">Проверить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
