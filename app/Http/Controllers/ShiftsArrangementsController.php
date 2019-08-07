<?php

namespace App\Http\Controllers;

use App\{
    Area,
    Shift,
    User,
    ShiftArrangement,
    ShiftArrangementLock,
    Events\ShiftArrangementChangeEvent
};
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Lang;

use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShiftsArrangementsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth'], ['except' => ['index', 'show', 'downloadShiftsArrangementsXlsx']]);
    }

    private static $DEFAULT_DURATION_DAYS = 30;
    private static $START_OF_WEEK = Carbon::SUNDAY;
    private static $END_OF_WEEK = Carbon::SATURDAY;

    private static function is_manager($shift)
    {
        return Auth::user()->is_admin
            || $shift->area->responsible_person_id == Auth::user()->id;
    }

    private static function is_locked(Carbon $date, $shift)
    {
        $time_now = now();
        $lock = ShiftArrangementLock::firstOrNew(
            [
                'date' => $date->format('Y-m-d'),
                'shift_id' => $shift->id,
            ],
            [
                'is_locked' => $date < $time_now,
            ]
        );
        if ($lock->updated_at == null) return $lock->is_locked;
        return $lock->date > $time_now || $lock->updated_at > $date
            ? $lock->is_locked : false;
    }

    static function getShiftsArrangements($from_date, $to_date, $area, $shift)
    {
        $query = ShiftArrangement::with([
            'shift' => function ($query) { $query->withTrashed(); },
            'onDutyStaff' => function ($query) { $query->withTrashed(); },
        ])->whereBetween('date', [$from_date, $to_date]);
        
        if ($area)
        {
            $query = $query->whereIn('shift_id', function($query) use ($area) {
                $query->select('id')->from((new Shift())->getTable())
                    ->whereNull('deleted_at')
                    ->where('area_id', function($query) use ($area) {
                        $query->select('id')->from((new Area())->getTable())
                            ->where('uuid', $area);
                    });
            });
        }
        else if ($shift)
        {
            $query = $query->where('shift_id', function ($query) use ($shift) {
                $query->select('id')->from((new Shift())->getTable())
                    ->where('uuid', $shift);
            });
        }
        else
        {
            $query = $query->whereIn('shift_id', function ($query) {
                $query->select('id')->from((new Shift())->getTable())
                    ->whereNull('deleted_at')
                    ->whereIn('area_id', function ($query) {
                        $query->select('id')->from((new Area())->getTable())
                            ->whereNull('deleted_at');
                    });
            });
        }

        return $query->get();
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => ['date_format:Y-m-d'],
            'to_date' => ['date_format:Y-m-d'],
            'area' => ['exists:areas,uuid'],
            'shift' => ['exists:shifts,uuid'],
        ]);

        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        try
        {
            $time_now = now();
            $from_date = new Carbon($request->input('from_date', $time_now));
            $to_date = new Carbon($request->input('to_date', (new Carbon($time_now))
                ->addDays(ShiftsArrangementsController::$DEFAULT_DURATION_DAYS)));
        } catch (Exception $e)
        {
            abort(400);
        }
        $from_date = $from_date->startOfWeek(ShiftsArrangementsController::$START_OF_WEEK);
        $to_date = $to_date->endOfWeek(ShiftsArrangementsController::$END_OF_WEEK);
        $area = $request->input('area');
        $shift = $request->input('shift');

        return ShiftsArrangementsController::getShiftsArrangements($from_date, $to_date, $area, $shift);
    }

    /**
     * Download shifts arrangements xlsx file.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function downloadShiftsArrangementsXlsx(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => ['date_format:Y-m-d'],
            'to_date' => ['date_format:Y-m-d'],
            'area' => ['exists:areas,uuid'],
            'shift' => ['exists:shifts,uuid'],
        ]);

        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        try
        {
            $time_now = now();
            $from_date = new Carbon($request->input('from_date', $time_now));
            $to_date = new Carbon($request->input('to_date', (new Carbon($time_now))
                ->addDays(ShiftsArrangementsController::$DEFAULT_DURATION_DAYS)));
        } catch (Exception $e)
        {
            abort(400);
        }
        $from_date = $from_date->startOfWeek(ShiftsArrangementsController::$START_OF_WEEK);
        $to_date = $to_date->endOfWeek(ShiftsArrangementsController::$END_OF_WEEK);
        $area_uuid = $request->input('area');
        $shift = $request->input('shift');

        $arrangements = ShiftsArrangementsController::getShiftsArrangements($from_date, $to_date, $area_uuid, $shift);
        $shifts = ShiftsArrangementsController::getShifts($shift, $area_uuid);
        $spreadsheet = ShiftsArrangementsController::prepareShiftsArrangementsSpreadsheet($from_date, $to_date, $arrangements, $shifts);
        $writer = new Xlsx($spreadsheet);
        $temporary_file_name = tempnam(realpath(sys_get_temp_dir()), 'shifts_arrangements_xlsx_');
        $writer->save($temporary_file_name);

        return response()->download(
            $temporary_file_name,
            sprintf(
                '%s_%s_%s_%s%s.xlsx',
                config('app.name'),
                Lang::get('ui.shifts_arrangements_table'),
                $from_date->format('Y-m-d'),
                $to_date->format('Y-m-d'),
                $area_uuid ? '_'.Area::where('uuid', $area_uuid)->first()->area_name : ''
            )
        )->deleteFileAfterSend();
    }

    private static function getShifts($shift_uuid, $area_uuid)
    {
        $query = Shift::query();

        if ($shift_uuid)
        {
            $query = $query->where('uuid', $shift_uuid);
        }
        else if ($area_uuid)
        {
            $query = $query->where('area_id', function ($query) use ($area_uuid)
            {
                $query->select('id')->from('areas')->where('uuid', $area_uuid);
            });
        }

        return $query->get();
    }

    private static function prepareShiftsArrangementsSpreadsheet($from_date, $to_date, $arrangements, $shifts)
    {
        $kv_arrangements = array(); // array[shift ID][unix timestamp]
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        foreach ($shifts as $key => $shift) foreach ($period as $date)
            $kv_arrangements[$shift->id][$date->timestamp] = array();

        foreach ($arrangements as $key => $arrangement)
            array_push($kv_arrangements[$arrangement->shift_id][$arrangement->date->timestamp], $arrangement);

        $from_week = intdiv(intdiv($from_date->timestamp, 86400) + 4, 7);
        $to_week = intdiv(intdiv($to_date->timestamp, 86400) + 4, 7);

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        for ($i = 1; $i < 9; ++$i)
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(true);

        # set title
        $sheet->getCellByColumnAndRow(1, 1)
            ->setValue(Lang::get('ui.week_days'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('a3cbfa');
        $sheet->getCellByColumnAndRow(2, 1)
            ->setValue(Lang::get('ui.sunday'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('c0c0c0');
        $sheet->getCellByColumnAndRow(3, 1)
            ->setValue(Lang::get('ui.monday'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('f8cda1');
        $sheet->getCellByColumnAndRow(4, 1)
            ->setValue(Lang::get('ui.tuesday'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('f8cda1');
        $sheet->getCellByColumnAndRow(5, 1)
            ->setValue(Lang::get('ui.wednesday'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('f8cda1');
        $sheet->getCellByColumnAndRow(6, 1)
            ->setValue(Lang::get('ui.thursday'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('f8cda1');
        $sheet->getCellByColumnAndRow(7, 1)
            ->setValue(Lang::get('ui.friday'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('f8cda1');
        $sheet->getCellByColumnAndRow(8, 1)
            ->setValue(Lang::get('ui.saturday'))->getStyle()->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('c0c0c0');

        $row_index = 2; // the title is 1

        for ($week = $from_week; $week <= $to_week; ++$week)
        {
            $sheet->getCellByColumnAndRow(1, $row_index)
                ->setValue(Lang::get('ui.date'))->getStyle()->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('a3cbfa');

            // week title
            for ($week_day = 0; $week_day < 7; ++$week_day)
            {
                $date = new Carbon(($week * 7 + $week_day - 4) * 86400);
                $sheet->getCellByColumnAndRow($week_day + 2, $row_index)
                    ->setValue($date->format('Y-m-d'))
                    ->getStyle()->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB(
                        $week_day == 0 || $week_day == 6 ? 'd9d9d9' : 'f8cda1'
                    );
            }
            $row_index += 1;

            // shifts
            foreach ($shifts as $key => $shift)
            {
                $sheet->getCellByColumnAndRow(1, $row_index)->setValue($shift->shift_name);
                $shift_arrangements = $kv_arrangements[$shift->id];
                for ($week_day = 0; $week_day < 7; ++$week_day)
                {
                    $names = array();
                    foreach ($shift_arrangements[($week * 7 + $week_day - 4) * 86400] as $key => $arrangement)
                    {
                        $staff = $arrangement->onDutyStaff;
                        $staff_name = $staff->display_name == null ? $staff->username : $staff->display_name;
                        array_push($names, $staff_name);
                    }
                    $cell_value = join("\n", $names);
                    $sheet->setCellValueByColumnAndRow($week_day + 2, $row_index, $cell_value);
                }
                $sheet->getRowDimension($row_index)->setZeroHeight(true);
                $row_index += 1;
            }
        }

        return $spreadsheet;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shift' => ['required', 'exists:shifts,uuid'],
            'on_duty_staff' => ['required', 'exists:users,username'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        if ($validator->fails())
            return response()->json($validator->messages(), 400);

        $on_duty_staff = $request->input('on_duty_staff');
        $date = Carbon::parse($request->input('date'));
        $shift = Shift::with('area')->where('uuid', $request->input('shift'))->first();

        $on_duty_staff_id = User::where('username', $on_duty_staff)->first()->id;
        
        $is_manager = static::is_manager($shift);
        $is_owner = $on_duty_staff_id == Auth::user()->id;
        $is_locked = static::is_locked($date, $shift);

        if (!$is_manager && (!$is_owner || $is_locked))
            return response(null, 403);

        $arrangement = ShiftArrangement::firstOrCreate([
            'shift_id' => $shift->id,
            'on_duty_staff_id' => $on_duty_staff_id,
            'date' => $date,
        ])->load('shift', 'onDutyStaff');

        event(new ShiftArrangementChangeEvent(
            $date,
            $arrangement->shift,
            $arrangement->on_duty_staff,
            $is_locked,
            true,
            Auth::user()
        ));

        return response()->json($arrangement);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function show(ShiftArrangement $shiftsArrangement)
    {
        return $shiftsArrangement;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function edit(ShiftArrangement $shiftArrangement)
    {
        abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShiftArrangement $shiftArrangement)
    {
        abort(501);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ShiftArrangement  $shiftArrangement
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShiftArrangement $shiftsArrangement)
    {
        $arrangement = $shiftsArrangement;

        $is_manager = static::is_manager($arrangement->shift);
        $is_owner = $arrangement->on_duty_staff_id == Auth::user()->id;
        $is_locked = static::is_locked($arrangement->date, $arrangement->shift);

        if (!$is_manager && (!$is_owner || $is_locked))
            return response(null, 403);

            
        if ($shiftsArrangement->delete())
        {
            event(new ShiftArrangementChangeEvent(
                $arrangement->date,
                $arrangement->shift,
                $arrangement->on_duty_staff,
                $is_locked,
                false,
                Auth::user()
            ));
            return response(null, 204);
        }
        return response(null, 400);
    }
}
