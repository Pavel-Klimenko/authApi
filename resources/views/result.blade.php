@extends('layouts.app')

@section('title')
    Страница результата
@endsection

@section('content')
      @if ($result['status'] == 'existing')
          <div>{{$result['message']}}</div>
      @elseif($result['status'] == 'new')
          <div>{{$result['message']}}</div>
          <div>Email: {{$result['email']}}</div>
          <div>Имя: {{$result['name']}}</div>
      @endif
@endsection
