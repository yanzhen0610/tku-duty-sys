@extends('layout')

@section('title', Lang::get('ui.home'))

@section('content')

<h1 class="title">@lang('ui.home')</h1>

@includeFirst(['index.notes-'.Config::get('app.locale'), 'index.notes-zh'])

@endsection