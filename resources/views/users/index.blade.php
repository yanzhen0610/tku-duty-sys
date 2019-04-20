@extends('layout')

@php

$users_data['ui_i18n'] = __('ui');
$groups_data['ui_i18n'] = __('ui');

@endphp

@section('content')

<div class="columns is-multiline is-variable is-8">
    <div class="column is-narrow">
        <div id="users-view"></div>
    </div>
    <div class="column is-narrow">
        <div id="groups-view"></div>
    </div>
</div>

<script src="{{ mix('js/edit-table.js') }}" type="text/javascript"></script>
<script>
    const users_data = @json($users_data);
    const groups_data = @json($groups_data);
    const users_view = make_edit_table('#users-view', users_data);
    const groups_view = make_edit_table('#groups-view', groups_data);
</script>

@endsection