@extends('layout')

@section('title', Lang::get('ui.shifts_arrangements_changes'))

@section('content')

<script src="{{ url(mix('js/list.min.js')) }}"></script>

<table id="changes" class="table is-striped is-hoverable">
    <thead>
        <tr>
            <th class="sort" data-sort="timestamp">@lang('ui.event_timestamp')</th>
            <th class="sort" data-sort="changer">@lang('ui.changer')</th>
            <th class="sort" data-sort="shift">@lang('ui.shift')</th>
            <th class="sort" data-sort="date">@lang('ui.date')</th>
            <th class="sort" data-sort="on_duty_staff">@lang('ui.on_duty_staff')</th>
            <th class="sort" data-sort="status">@lang('ui.status')</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <th class="sort" data-sort="timestamp">@lang('ui.event_timestamp')</th>
            <th class="sort" data-sort="changer">@lang('ui.changer')</th>
            <th class="sort" data-sort="shift">@lang('ui.shift')</th>
            <th class="sort" data-sort="date">@lang('ui.date')</th>
            <th class="sort" data-sort="on_duty_staff">@lang('ui.on_duty_staff')</th>
            <th class="sort" data-sort="status">@lang('ui.status')</th>
        </tr>
    </tfoot>
    <tbody class="list">
        @foreach ($changes as $change)

        <tr>
            <td class="timestamp">{{ $change->created_at }}</td>
            <td class="changer">{{ $change->changer->display_name }} ({{ $change->changer->username }})</td>
            <td class="shift">{{ $change->shift->shift_name }}</td>
            <td class="date">{{ $change->date->format('Y-m-d') }}</td>
            <td class="on_duty_staff">{{ $change->onDutyStaff->display_name }} ({{ $change->onDutyStaff->username }})</td>
            <td class="status">{{ $change->is_up ? Lang::get('ui.create') : Lang::get('ui.delete') }}</td>
        </tr>

        @endforeach
    </tbody>
</table>

<script>
    var list = new List('changes', {valueNames: ['timestamp', 'changer', 'shift', 'date', 'on_duty_staff', 'status']});
</script>

@endsection