{{-- resources/views/errors/500.blade.php --}}
@extends('layouts.app') {{-- или твой основной layout --}}

@section('title', 'Ошибка сервера')

@section('content')
    <div class="container py-5 text-center">
        <h1 class="display-4 text-danger">Ошибка 500</h1>
        <p class="lead">Произошла непредвиденная ошибка на сервере.</p>

        @if (config('app.debug'))
            <div class="alert alert-warning mt-4 text-start">
                <strong>Сообщение:</strong> {{ $exception->getMessage() }} <br>
                <strong>Файл:</strong> {{ $exception->getFile() }} : {{ $exception->getLine() }}
            </div>
        @else
            <p>Попробуйте обновить страницу или вернуться позже.</p>
        @endif

        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary mt-4">⬅ Назад</a>
    </div>
@endsection
