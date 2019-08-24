<?php

namespace Tests\Feature;

use App\Area;
use App\Shift;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use \Carbon\CarbonPeriod;
use \Carbon\CarbonInterval;

class APIShiftArrangementLocksTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminUpdateDateRange()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => true,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(8);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }


        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => false,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(7);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }
    }

    public function testAdminUpdateDate()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();
        
        $this->actingAs($admin);
        $date = now();

        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => true,
            'date' => $date->format('Y-m-d'),
        ]);
        $this->endQueryLog(8);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
            $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
        }


        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => false,
            'date' => $date->format('Y-m-d'),
        ]);
        $this->endQueryLog(7);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
            $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
        }
    }

    public function testAdminUpdateArea()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);
        $areas->load('shifts_eager');
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => true,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);

            foreach ($area->shifts as $shift)
            {
                $this->assertArrayHasKey($shift->uuid, $response_content);
                foreach ($period as $date)
                {
                    $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                    $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
                }
            }
        }


        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => false,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);

            foreach ($area->shifts as $shift)
            {
                $this->assertArrayHasKey($shift->uuid, $response_content);
                foreach ($period as $date)
                {
                    $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                    $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
                }
            }
        }
    }

    public function testAdminUpdateShift()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => true,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'shift' => $shift->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }


        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => false,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'shift' => $shift->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }
    }

    public function testAdminUpdateShifts()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => true,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
            'shifts' => $shifts->map(function ($shift) {
                return $shift->uuid;
            }),
        ]);
        $this->endQueryLog(7);
        
        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);
        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }


        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => false,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
            'shifts' => $shifts->map(function ($shift) {
                return $shift->uuid;
            }),
        ]);
        $this->endQueryLog(7);
        
        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);
        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }
    }

    public function testUpdateWithoutPermission()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();
        $new_user = factory(User::class, 1)->create()->first();

        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());

        $this->actingAs($admin);
        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => true,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(8);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }

        $this->actingAs($new_user);
        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => false,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(7);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }


        $this->actingAs($admin);
        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => false,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(7);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }

        $this->actingAs($new_user);
        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => true,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(7);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }
    }

    public function testIndex()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        
        $this->actingAs($admin);
        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => true,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(8);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }

        Auth::logout();
        $this->startQueryLog();
        $response = $this->json('GET', '/shift_arrangement_locks', [
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(3);
        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }


        $this->actingAs($admin);
        $this->startQueryLog();
        $response = $this->post('/shift_arrangement_locks', [
            'lock' => false,
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(7);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }

        Auth::logout();
        $this->startQueryLog();
        $response = $this->json('GET', '/shift_arrangement_locks', [
            'from_date' => $from_date->format('Y-m-d'),
            'to_date' => $to_date->format('Y-m-d'),
        ]);
        $this->endQueryLog(3);
        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);

        foreach ($shifts as $shift)
        {
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }
    }

    public function testIndexWithArea()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $areas->load('shifts_eager');
        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        
        $this->actingAs($admin);
        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => true,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);

            foreach ($area->shifts as $shift)
            {
                $this->assertArrayHasKey($shift->uuid, $response_content);
                foreach ($period as $date)
                {
                    $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                    $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
                }
            }
        }

        Auth::logout();
        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->json('GET', '/shift_arrangement_locks', [
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(4);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);

            foreach ($area->shifts as $shift)
            {
                $this->assertArrayHasKey($shift->uuid, $response_content);
                foreach ($period as $date)
                {
                    $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                    $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
                }
            }
        }


        $this->actingAs($admin);
        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => false,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);

            foreach ($area->shifts as $shift)
            {
                $this->assertArrayHasKey($shift->uuid, $response_content);
                foreach ($period as $date)
                {
                    $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                    $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
                }
            }
        }

        Auth::logout();
        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->json('GET', '/shift_arrangement_locks', [
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(4);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);

            foreach ($area->shifts as $shift)
            {
                $this->assertArrayHasKey($shift->uuid, $response_content);
                foreach ($period as $date)
                {
                    $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                    $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
                }
            }
        }
    }

    public function testIndexWithShift()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $from_date = now()->addDays(-7);
        $to_date = now()->addDays(14);
        $period = new CarbonPeriod($from_date, $to_date, CarbonInterval::days());
        
        $this->actingAs($admin);
        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => true,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'shift' => $shift->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }

        Auth::logout();
        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->json('GET', '/shift_arrangement_locks', [
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'shift' => $shift->uuid,
            ]);
            $this->endQueryLog(4);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertTrue($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }


        $this->actingAs($admin);
        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->post('/shift_arrangement_locks', [
                'lock' => false,
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'shift' => $shift->uuid,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }

        Auth::logout();
        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->json('GET', '/shift_arrangement_locks', [
                'from_date' => $from_date->format('Y-m-d'),
                'to_date' => $to_date->format('Y-m-d'),
                'shift' => $shift->uuid,
            ]);
            $this->endQueryLog(4);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            
            $this->assertArrayHasKey($shift->uuid, $response_content);
            foreach ($period as $date)
            {
                $this->assertArrayHasKey($date->format('Y-m-d'), $response_content[$shift->uuid]);
                $this->assertFalse($response_content[$shift->uuid][$date->format('Y-m-d')]);
            }
        }
    }
}
