<?php

namespace App\Http\Controllers;

use \App\{Area, Shift, User, ShiftArrangement, ShiftArrangementLock, ShiftArrangementChange};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'], ['except' => 'shiftsArrangementsTable']);
    }

    public function users()
    {
        $table_data = UsersController::getUsersData();
        $title = Auth::user()->is_admin ? Lang::get('ui.users_management') : Lang::get('ui.users');
        return view('pages.edit_table', compact(['title', 'table_data']));
    }

    public function areas()
    {
        $table_data = AreasController::getAreasData();
        $title = Auth::user()->is_admin ? Lang::get('ui.areas_management') : Lang::get('ui.areas');
        return view('pages.edit_table', compact(['title', 'table_data']));
    }

    public function shifts()
    {
        $table_data = ShiftsController::getShiftsData();
        $title = Auth::user()->is_admin ? Lang::get('ui.shifts_management') : Lang::get('ui.shifts');
        return view('pages.edit_table', compact(['title', 'table_data']));
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
            'shifts' => Shift::with('area')->whereIn('area_id', function ($query) {
                $query->select('id')->from((new Area())->getTable())
                    ->whereNull('deleted_at');
            })->get(),
            'staves' => User::all(),
            'shifts_arrangements' => ShiftArrangement::with(
                ['shift', 'onDutyStaff' => function ($query) {
                    $query->withTrashed(); // show deleted staves
                }])
                ->whereIn('shift_id', function ($query) {
                    $query->select('id')->from((new Shift())->getTable())
                        ->whereNull('deleted_at')
                        ->whereIn('area_id', function ($query) {
                            $query->select('id')->from((new Area())->getTable())
                                ->whereNull('deleted_at');
                        });
                })
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
            'download_table_url' => route('shifts_arrangements.download_xlsx')
        ];

        return view('pages.shifts_arrangements_table', compact(['data']));
    }

    public function shiftsArrangementsChanges()
    {
        $a_week_ago = now()->addDays(-7);
        $changes = ShiftArrangementChange::with(
            [
                'changer' => function ($query) { $query->withTrashed(); },
                'onDutyStaff' => function ($query) { $query->withTrashed(); },
                'shift' => function ($query) { $query->withTrashed(); },
            ])
            ->where('is_locked', true)
            ->where(function ($query) use ($a_week_ago)
            {
                $query->orWhere('created_at', '>=', $a_week_ago)
                    ->orWhere('date', '>=', $a_week_ago);
            })
            ->get()->sortByDesc('created_at');

        return view('pages.shifts_arrangements_changes', compact(['changes']));
    }

}
