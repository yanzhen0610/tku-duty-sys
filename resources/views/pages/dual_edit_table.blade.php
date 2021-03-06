@extends('layout')

@php

$table_a_data['ui_i18n'] = __('ui');
$table_b_data['ui_i18n'] = __('ui');

@endphp

@section('content')

<div class="columns is-multiline is-variable">
    <div class="column is-narrow">
        <div id="edit-table-a"></div>
    </div>
    <div class="column is-narrow">
        <div id="edit-table-b"></div>
    </div>
</div>

<script src="{{ url(mix('js/edit-table.js')) }}" type="text/javascript"></script>
<script>
    var table_a_data = @json($table_a_data);
    var table_b_data = @json($table_b_data);
    var edit_table_a = make_edit_table('#edit-table-a', table_a_data);
    var edit_table_b = make_edit_table('#edit-table-b', table_b_data);
</script>

@endsection