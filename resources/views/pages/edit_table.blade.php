@extends('layout')

@php

$table_data['ui_i18n'] = __('ui');

@endphp

@section('content')

<div id="edit-table"></div>

<script src="{{ mix('js/edit-table.js') }}" type="text/javascript"></script>
<script>
    var table_data = @json($table_data);
    var edit_table = make_edit_table('#edit-table', table_data);
</script>

@endsection