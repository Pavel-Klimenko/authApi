@extends('layouts.app')

@section('title')
    Авторизация через Yandex и Google
@endsection

@section('content')
        <a class="btn btn-warning" href="{{ route('auth-yandex') }}">Войти через yandex</a>
        <br /><br />
        <a class="btn btn-success" href="{{ route('auth-google') }}">Войти через google</a>
@endsection
