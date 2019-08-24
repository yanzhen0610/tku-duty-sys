<?php

namespace Tests;

use App\User;
use App\Area;
use App\Shift;
use App\ShiftArrangementLock;
use App\ShiftArrangement;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    public function startQueryLog()
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
    }

    public function endQueryLog(int $count)
    {
        DB::disableQueryLog();
        $this->assertLessThanOrEqual($count, count(DB::getQueryLog()));
    }

    public function dumpQueryLog()
    {
        print_r(DB::getQueryLog());
    }

    protected function dirtyDatabase()
    {
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        // items are going to be deleted
        $deleted_users = factory(User::class, 20)->create();
        $deleted_areas = factory(Area::class, 10)->create();
        $deleted_shifts = factory(Shift::class, 10)->create();
        
        // relation model deleted items
        $relation_deleted_shifts = factory(Shift::class, 10)->create();
        foreach ($relation_deleted_shifts as $shifts)
            $shifts->area = $deleted_areas->random();
        $relation_deleted_areas = factory(Area::class, 10)->create();
        foreach ($relation_deleted_areas as $areas)
            $areas->responsible_person = $deleted_users->random();
        factory(User::class, 10)->create();

        // deleting items
        $deleted_users->each(function ($item) {
            $item->delete();
        });
        $deleted_areas->each(function ($item) {
            $item->delete();
        });
        $deleted_shifts->each(function ($item) {
            $item->delete();
        });
        
        // normal items
        $normal_users = factory(User::class, 20)->create();
        $normal_areas = factory(Area::class, 10)->create();
        foreach ($normal_areas as $area)
            $area->responsible_person = $normal_users->random();
        $normal_shifts = factory(Shift::class, 10)->create();
        foreach ($normal_shifts as $shift)
            $shift->area = $normal_areas->random();

        // locks
        // expired
        $this->setLocks($period, $deleted_shifts[0], true, $from_date, $from_date);
        $this->setLocks($period, $relation_deleted_shifts[0], true, $from_date, $from_date);
        $this->setLocks($period, $normal_shifts[0], true, $from_date, $from_date);
        $this->setLocks($period, $deleted_shifts[1], false, $from_date, $from_date);
        $this->setLocks($period, $relation_deleted_shifts[1], false, $from_date, $from_date);
        $this->setLocks($period, $normal_shifts[1], false, $from_date, $from_date);

        // today
        $this->setLocks($period, $deleted_shifts[2], true, now(), now());
        $this->setLocks($period, $relation_deleted_shifts[2], true, now(), now());
        $this->setLocks($period, $normal_shifts[2], true, now(), now());
        $this->setLocks($period, $deleted_shifts[3], false, now(), now());
        $this->setLocks($period, $relation_deleted_shifts[3], false, now(), now());
        $this->setLocks($period, $normal_shifts[3], false, now(), now());

        // arrangements
        $this->setArrangements($period, $deleted_shifts[0], $deleted_users[0]);
        $this->setArrangements($period, $deleted_shifts[0], $normal_users[0]);
        $this->setArrangements($period, $deleted_shifts[1], $deleted_users[1]);
        $this->setArrangements($period, $deleted_shifts[1], $normal_users[1]);
        $this->setArrangements($period, $deleted_shifts[2], $deleted_users[2]);
        $this->setArrangements($period, $deleted_shifts[2], $normal_users[2]);
        $this->setArrangements($period, $deleted_shifts[3], $deleted_users[3]);
        $this->setArrangements($period, $deleted_shifts[3], $normal_users[3]);
        
        $this->setArrangements($period, $relation_deleted_shifts[0], $deleted_users[4]);
        $this->setArrangements($period, $relation_deleted_shifts[0], $normal_users[4]);
        $this->setArrangements($period, $relation_deleted_shifts[1], $deleted_users[5]);
        $this->setArrangements($period, $relation_deleted_shifts[1], $normal_users[5]);
        $this->setArrangements($period, $relation_deleted_shifts[2], $deleted_users[6]);
        $this->setArrangements($period, $relation_deleted_shifts[2], $normal_users[6]);
        $this->setArrangements($period, $relation_deleted_shifts[3], $deleted_users[7]);
        $this->setArrangements($period, $relation_deleted_shifts[3], $normal_users[7]);
        
        $this->setArrangements($period, $normal_shifts[0], $deleted_users[8]);
        $this->setArrangements($period, $normal_shifts[0], $normal_users[8]);
        $this->setArrangements($period, $normal_shifts[1], $deleted_users[9]);
        $this->setArrangements($period, $normal_shifts[1], $normal_users[9]);
        $this->setArrangements($period, $normal_shifts[2], $deleted_users[10]);
        $this->setArrangements($period, $normal_shifts[2], $normal_users[10]);
        $this->setArrangements($period, $normal_shifts[3], $deleted_users[11]);
        $this->setArrangements($period, $normal_shifts[3], $normal_users[11]);
    }

    private function setLocks($period, $shift, $lock, $created_at, $updated_at)
    {
        foreach ($period as $date)
        {
            ShiftArrangementLock::insert(array([
                'shift_id' => $shift->id,
                'date' => $date,
                'is_locked' => $lock,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ]));
        }
    }

    private function setArrangements($period, $shift, $on_duty_staff)
    {
        foreach ($period as $date)
        {
            ShiftArrangement::create([
                'shift_id' => $shift->id,
                'date' => $date,
                'on_duty_staff_id' => $on_duty_staff->id
            ]);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::dirtyDatabase();
    }
}
