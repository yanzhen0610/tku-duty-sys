<?php

namespace Tests\Feature;

use App\Area;
use App\Shift;
use App\User;
use App\ShiftArrangement;
use App\ShiftArrangementChange;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use \Carbon\Carbon;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

class APIShiftsArrangementsTest extends TestCase
{
    use RefreshDatabase;

    private static function arrangementExists($arrangements, $target)
    {
        foreach ($arrangements as $arrangement)
            if ($arrangement['uuid'] == $target['uuid'])
            {
                if ((new Carbon($arrangement['date'])) == 
                        (new Carbon($target['date']))
                    && $arrangement['shift']['uuid'] ==
                        $target['shift']['uuid']
                    && $arrangement['on_duty_staff']['username'] ==
                        $target['on_duty_staff']['username'])
                    return true;
                return false;
            }
        return false;
    }

    public function testIndex()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 5)->create();
        $shifts = factory(Shift::class, 15)->create();

        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        $arrangements = array();
        foreach ($period as $date)
            array_push($arrangements, ShiftArrangement::create([
                'date' => $date->format('Y-m-d'),
                'shift_id' => $shifts->random()->id,
                'on_duty_staff_id' => $users->random()->id,
            ]));
        $arrangements = Collection::make($arrangements);
        $arrangements->load(['shift_eager', 'on_duty_staff_eager']);

        $this->startQueryLog();
        $response = $this->json('GET', '/shifts_arrangements', [
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(3);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);
        foreach ($arrangements as $arrangement)
            $this->assertTrue(static::arrangementExists(
                $response_content,
                $arrangement
            ));
    }

    public function testShiftIndex()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 5)->create();
        $shifts = factory(Shift::class, 15)->create();

        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        $arrangements = array();
        foreach ($shifts as $shift)
            foreach ($period as $date)
                array_push($arrangements, ShiftArrangement::create([
                    'date' => $date->format('Y-m-d'),
                    'shift_id' => $shift->id,
                    'on_duty_staff_id' => $users->random()->id,
                ]));
        $arrangements = Collection::make($arrangements);
        $arrangements->load(['shift_eager', 'on_duty_staff_eager']);

        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->json('GET', '/shifts_arrangements', [
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'shift' => $shift->uuid,
            ]);
            $this->endQueryLog(4);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            foreach ($arrangements as $arrangement)
                if ($arrangement->shift_id == $shift->id)
                    $this->assertTrue(static::arrangementExists(
                        $response_content,
                        $arrangement
                    ));
        }
    }

    public function testAreaIndex()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 5)->create();
        $shifts = factory(Shift::class, 15)->create();

        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        $areas->load('shifts_eager');

        $arrangements = array();
        foreach ($areas as $area)
            foreach ($area->shifts as $shift)
                foreach ($period as $date)
                    array_push($arrangements, ShiftArrangement::create([
                        'date' => $date->format('Y-m-d'),
                        'shift_id' => $shift->id,
                        'on_duty_staff_id' => $users->random()->id,
                    ]));
        $arrangements = Collection::make($arrangements);
        $arrangements->load(['shift_eager', 'on_duty_staff_eager']);

        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->json('GET', '/shifts_arrangements', [
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(4);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            foreach ($arrangements as $arrangement)
            {
                $is_area = $area->shifts->contains(function ($key, $value) use ($arrangement) {
                    return $key->id == $arrangement->shift_id;
                });
                if (!$is_area) continue;
                $this->assertTrue(static::arrangementExists(
                    $response_content,
                    $arrangement
                ));
            }
        }
    }

    public function testShow()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 5)->create();
        $shifts = factory(Shift::class, 15)->create();

        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        $arrangements = array();
        foreach ($period as $date)
            array_push($arrangements, ShiftArrangement::create([
                'date' => $date->format('Y-m-d'),
                'shift_id' => $shifts->random()->id,
                'on_duty_staff_id' => $users->random()->id,
            ]));
        $arrangements = Collection::make($arrangements);
        $arrangements->load(['shift_eager', 'on_duty_staff_eager']);

        foreach ($arrangements as $arrangement)
        {
            $this->startQueryLog();
            $response = $this->json(
                'GET',
                '/shifts_arrangements/'.$arrangement->uuid
            );
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('date', $response_content);
            $this->assertArrayHasKey('shift', $response_content);
            $this->assertArrayHasKey('on_duty_staff', $response_content);
            $this->assertEquals($arrangement->uuid, $response_content['uuid']);
            $this->assertEquals($arrangement->date->format('Y-m-d'), $response_content['date']);
            $this->assertEquals($arrangement->shift->uuid, $response_content['shift']['uuid']);
            $this->assertEquals($arrangement->on_duty_staff->username, $response_content['on_duty_staff']['username']);
        }
    }

    public function testAdminStore()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 5)->create();
        $shifts = factory(Shift::class, 15)->create();

        $admin = User::where('username', 'admin')->first();
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        $this->actingAs($admin);
        
        foreach ($period as $date)
        {
            $shift = $shifts->random();
            $onDutyStaff = $users->random();

            $this->startQueryLog();
            $response = $this->post('/shifts_arrangements', [
                'shift' => $shift->uuid,
                'on_duty_staff' => $onDutyStaff->username,
                'date' => $date->format('Y-m-d'),
            ]);
            $this->endQueryLog(11);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);$this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('date', $response_content);
            $this->assertArrayHasKey('shift', $response_content);
            $this->assertArrayHasKey('on_duty_staff', $response_content);
            $this->assertEquals($date->format('Y-m-d'), $response_content['date']);
            $this->assertEquals($shift->uuid, $response_content['shift']['uuid']);
            $this->assertEquals($onDutyStaff->username, $response_content['on_duty_staff']['username']);
            
            $uuid = $response_content['uuid'];

            $changes = ShiftArrangementChange::query()
                ->where('date', $date->format('Y-m-d'))
                ->where('shift_id', $shift->id)
                ->where('on_duty_staff_id', $onDutyStaff->id)
                ->where('changer_id', $admin->id)
                ->where('is_up', true)
                ->get();
            $this->assertGreaterThanOrEqual(1, count($changes));


            $this->startQueryLog();
            $response = $this->json(
                'GET',
                '/shifts_arrangements/'.$uuid
            );
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->getContent(), true);$this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('date', $response_content);
            $this->assertArrayHasKey('shift', $response_content);
            $this->assertArrayHasKey('on_duty_staff', $response_content);
            $this->assertEquals($uuid, $response_content['uuid']);
            $this->assertEquals($date->format('Y-m-d'), $response_content['date']);
            $this->assertEquals($shift->uuid, $response_content['shift']['uuid']);
            $this->assertEquals($onDutyStaff->username, $response_content['on_duty_staff']['username']);
        }
    }

    public function testAdminDestroy()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 5)->create();
        $shifts = factory(Shift::class, 15)->create();

        $admin = User::where('username', 'admin')->first();
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        $this->actingAs($admin);

        $arrangements = array();
        foreach ($period as $date)
            array_push($arrangements, ShiftArrangement::create([
                'date' => $date->format('Y-m-d'),
                'shift_id' => $shifts->random()->id,
                'on_duty_staff_id' => $users->random()->id,
            ]));
        $arrangements = Collection::make($arrangements);
        $arrangements->load(['shift_eager', 'on_duty_staff_eager']);

        foreach ($arrangements as $arrangement)
        {
            $this->startQueryLog();
            $response = $this->json(
                'GET',
                '/shifts_arrangements/'.$arrangement->uuid
            );
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->getContent(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('date', $response_content);
            $this->assertArrayHasKey('shift', $response_content);
            $this->assertArrayHasKey('on_duty_staff', $response_content);
            $this->assertEquals($arrangement->uuid, $response_content['uuid']);
            $this->assertEquals($arrangement->date->format('Y-m-d'), $response_content['date']);
            $this->assertEquals($arrangement->shift->uuid, $response_content['shift']['uuid']);
            $this->assertEquals($arrangement->on_duty_staff->username, $response_content['on_duty_staff']['username']);


            $this->startQueryLog();
            $response = $this->delete('/shifts_arrangements/'.$arrangement->uuid);
            $this->endQueryLog(6);

            $this->assertContains($response->getStatusCode(), [200, 204]);

            $changes = ShiftArrangementChange::query()
                ->where('date', $arrangement->date->format('Y-m-d'))
                ->where('shift_id', $arrangement->shift_id)
                ->where('on_duty_staff_id', $arrangement->on_duty_staff_id)
                ->where('changer_id', $admin->id)
                ->where('is_up', false)
                ->get();
            $this->assertGreaterThanOrEqual(1, count($changes));


            $this->startQueryLog();
            $response = $this->json(
                'GET',
                '/shifts_arrangements/'.$arrangement->uuid
            );
            $this->endQueryLog(1);

            $response->setStatusCode(404);
        }
    }

    public function testUserDestroy()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 5)->create();
        $shifts = factory(Shift::class, 15)->create();

        $admin = User::where('username', 'admin')->first();
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        $this->actingAs($admin);

        // newly created
        $users = factory(User::class, 10)->create();

        foreach ($users as $user)
        {
            $this->actingAs($user);

            $arrangements = array();
            foreach ($period as $date)
                array_push($arrangements, ShiftArrangement::create([
                    'date' => $date->format('Y-m-d'),
                    'shift_id' => $shifts->random()->id,
                    'on_duty_staff_id' => $user->id,
                ]));
            $arrangements = Collection::make($arrangements);
            $arrangements->load(['shift_eager', 'on_duty_staff_eager']);

            foreach ($arrangements as $arrangement)
            {
                $this->startQueryLog();
                $response = $this->json(
                    'GET',
                    '/shifts_arrangements/'.$arrangement->uuid
                );
                $this->endQueryLog(3);

                $response->assertStatus(200);
                $response_content = json_decode($response->getContent(), true);
                $this->assertArrayHasKey('uuid', $response_content);
                $this->assertArrayHasKey('date', $response_content);
                $this->assertArrayHasKey('shift', $response_content);
                $this->assertArrayHasKey('on_duty_staff', $response_content);
                $this->assertEquals($arrangement->uuid, $response_content['uuid']);
                $this->assertEquals($arrangement->date->format('Y-m-d'), $response_content['date']);
                $this->assertEquals($arrangement->shift->uuid, $response_content['shift']['uuid']);
                $this->assertEquals($arrangement->on_duty_staff->username, $response_content['on_duty_staff']['username']);


                if ($arrangement->date <= now())
                {
                    $this->startQueryLog();
                    $response = $this->delete('/shifts_arrangements/'.$arrangement->uuid);
                    $this->endQueryLog(5);

                    $response->assertStatus(403);

                    $this->startQueryLog();
                    $response = $this->json(
                        'GET',
                        '/shifts_arrangements/'.$arrangement->uuid
                    );
                    $this->endQueryLog(3);

                    $response->setStatusCode(200);
                }
                else
                {
                    $this->startQueryLog();
                    $response = $this->delete('/shifts_arrangements/'.$arrangement->uuid);
                    $this->endQueryLog(7);

                    $this->assertContains($response->getStatusCode(), [200, 204]);

                    $changes = ShiftArrangementChange::query()
                        ->where('date', $arrangement->date->format('Y-m-d'))
                        ->where('shift_id', $arrangement->shift_id)
                        ->where('on_duty_staff_id', $arrangement->on_duty_staff_id)
                        ->where('changer_id', $user->id)
                        ->where('is_up', false)
                        ->get();
                    $this->assertGreaterThanOrEqual(1, count($changes));


                    $this->startQueryLog();
                    $response = $this->json(
                        'GET',
                        '/shifts_arrangements/'.$arrangement->uuid
                    );
                    $this->endQueryLog(1);

                    $response->setStatusCode(404);
                }
            }
        }
    }
}
