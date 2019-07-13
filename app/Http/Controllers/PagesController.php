<?php

namespace App\Http\Controllers;

use \App\{Area, Shift, User, ShiftArrangement, ShiftArrangementLock};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PagesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'], ['except' => 'shiftsArrangementsTable']);
    }

    public function users()
    {
        $table_data = UsersController::getUsersData();
        return view('pages.edit_table', compact(['table_data']));
    }

    public function areas()
    {
        $table_data = AreasController::getAreasData();
        return view('pages.edit_table', compact(['table_data']));
    }

    public function shifts()
    {
        $table_data = ShiftsController::getShiftsData();
        return view('pages.edit_table', compact(['table_data']));
    }

    public function shiftsArrangementsTable()
    {
        $from_date = now()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $to_date = now()->addDays(30)->endOfWeek(Carbon::SATURDAY)->toDateString();

        $areas = Area::with('shifts')->get();
        $is_admin = Auth::check() && (Auth::user()->is_admin || $areas->contains(function ($value, $key) {
            return $value->responsible_person_id == Auth::user()->id;
        }));

        $data = [
            'is_admin' => $is_admin,
            'read_only' => !Auth::check(),
            'current_user' => Auth::check() ? Auth::user() : null,
            'areas' => $areas,
            'duration' => [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ],
            'shifts' => Shift::with('area')->get(),
            'staves' => User::all(),
            'shifts_arrangements' => ShiftArrangement::with(['shift', 'onDutyStaff'])
                ->whereBetween('date', [$from_date, $to_date])->get(),
            'locks' => ShiftArrangementLocksController::getIsLocked($from_date, $to_date),
            'crud' => [
                'create' => [
                    'url' => route('shifts_arrangements.store'),
                    'method' => 'POST',
                ],
                'read' => [
                    'url' => route('shifts_arrangements.index'),
                    'method' => 'GET',
                ],
                'delete' => [
                    'url' => route('shifts_arrangements.destroy', '') . '/',
                    'method' => 'DELETE',
                ],
            ],
            'locks_crud' => [
                'read' => [
                    'url' => route('shift_arrangement_locks.index'),
                    'method' => 'GET',
                ],
                'update' => [
                    'url' => route('shift_arrangement_locks.update'),
                    'method' => 'POST',
                ],
            ],
        ];

        return view('pages.shifts_arrangements_table', ['data' => $data]);
    }
}
