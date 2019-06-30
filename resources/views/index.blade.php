@extends('layout')

@section('content')

<h1 class="title">@lang('ui.home')</h1>

@includeFirst(['index.notes-'.Config::get('app.locale'), 'index.notes-zh'])

@endsection