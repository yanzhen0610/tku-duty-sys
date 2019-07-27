@extends('layout')

@php

$data['i18n'] = __('ui');

@endphp

@section('content')

<div id="shifts-arrangements-table"></div>

<script src="{{ url(mix('js/shifts-arrangements.js')) }}" type="text/javascript"></script>
<script>
    var data = @json($data);
    var shifts_arrangements_table = make_shifts_arrangements_table('#shifts-arrangements-table', data);
</script>

@endsection