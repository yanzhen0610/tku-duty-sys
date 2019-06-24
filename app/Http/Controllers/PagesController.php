<?php

namespace App\Http\Controllers;

use \App\{Area, Shift, User, ShiftArrangement};
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
        $table_a_data = UsersController::getUsersData();
        $table_b_data = [];
        return view('pages.dual_edit_table', compact(['table_a_data', 'table_b_data']));
    }

    public function areas()
    {
        $table_a_data = AreasController::getAreasData();
        $table_b_data = [];
        return view('pages.dual_edit_table', compact(['table_a_data', 'table_b_data']));
    }

    public function shifts()
    {
        $table_a_data = ShiftsController::getShiftsData();
        $table_b_data = [];
        return view('pages.dual_edit_table', compact(['table_a_data', 'table_b_data']));
    }

    public function shiftsArrangementsTable()
    {
        $from_date = now()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $to_date = now()->addDays(30)->endOfWeek(Carbon::SATURDAY)->toDateString();

        $data = [
            'is_admin' => false,
            'current_user' => null,
            'areas' => Area::with('shifts')->get(),
            'duration' => [
                'from_date' => $from_date,
                'to_date' => $to_date,
            ],
            'shifts' => Shift::with('area')->get(),
            'staves' => User::all(),
            'shifts_arrangements' => ShiftArrangement::with(['shift', 'onDutyStaff'])
                ->whereBetween('date', [$from_date, $to_date])->get(),
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
        ];

        if (Auth::check())
        {
            $data['is_admin'] = Auth::user()->is_admin;
            $data['current_user'] = Auth::user();
        }

        return view('pages.shifts_arrangements_table', ['data' => $data]);
    }
}
