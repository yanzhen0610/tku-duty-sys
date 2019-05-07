@extends('layout')

@php

$table_a_data['ui_i18n'] = __('ui');
$table_b_data['ui_i18n'] = __('ui');

@endphp

@section('content')

<div class="columns is-multiline is-variable is-8">
    <div class="column is-narrow">
        <div id="edit-table-a"></div>
    </div>
    <div class="column is-narrow">
        <div id="edit-table-b"></div>
    </div>
</div>

<script src="{{ mix('js/edit-table.js') }}" type="text/javascript"></script>
<script>
    const table_a_data = @json($table_a_data);
    const table_b_data = @json($table_b_data);
    const edit_table_a = make_edit_table('#edit-table-a', table_a_data);
    const edit_table_b = make_edit_table('#edit-table-b', table_b_data);
</script>

@endsection