<?php

namespace Tests\Feature;

use App\User;
use App\Area;
use App\Shift;
use Tests\TestCase;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APIShiftsTest extends TestCase
{
    use RefreshDatabase;

    private function hasShift($shifts, $target)
    {
        foreach ($shifts as $shift)
            if ($shift['uuid'] === $target->uuid &&
                    $shift['shift_name'] === $target->shift_name)
                return true;
        return false;
    }

    public function testIndex()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();
        $response = $this->actingAs($admin)->get('/shifts');
        $this->endQueryLog(3);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);
        $this->assertArrayHasKey('rows', $response_content);
        foreach ($shifts as $shift)
            $this->assertTrue(static::hasShift(
                $response_content['rows'], $shift));
    }

    public function testShow()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $admin = User::where('username', 'admin')->first();
        $shifts->load('area_eager');

        $this->actingAs($admin);

        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->get('/shifts/' . $shift->uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('shift_name', $response_content);
            $this->assertArrayHasKey('area', $response_content);
            $this->assertEquals($shift->shift_name, $response_content['shift_name']);
            $this->assertEquals($shift->area->uuid, $response_content['area']);
        }
    }

    public function testStore()
    {
        $faker = Factory::create();
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $this->actingAs(User::where('username', 'admin')->first());

        for ($i = 0; $i < 10; ++$i)
        {
            $area = $areas->random();
            $shift_name = $faker->streetName;

            $this->startQueryLog();
            $response = $this->post('/shifts', [
                'shift_name' => $shift_name,
                'area' => $area->uuid,
            ]);
            $this->endQueryLog(5);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('shift_name', $response_content);
            $this->assertArrayHasKey('area', $response_content);
            $this->assertEquals($shift_name, $response_content['shift_name']);
            $this->assertEquals($area->uuid, $response_content['area']);
            $uuid = $response_content['uuid'];

            
            $this->startQueryLog();
            $response = $this->get('/shifts/'.$uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('shift_name', $response_content);
            $this->assertArrayHasKey('area', $response_content);
            $this->assertEquals($uuid, $response_content['uuid']);
            $this->assertEquals($shift_name, $response_content['shift_name']);
            $this->assertEquals($area->uuid, $response_content['area']);
        }
    }

    public function testUpdate()
    {
        $faker = Factory::create();
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $this->actingAs(User::where('username', 'admin')->first());

        foreach ($shifts as $shift)
        {
            $this->startQueryLog();
            $response = $this->get('/shifts/' . $shift->uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('shift_name', $response_content);
            $this->assertArrayHasKey('area', $response_content);
            $this->assertEquals($shift->shift_name, $response_content['shift_name']);
            $this->assertEquals($shift->area->uuid, $response_content['area']);
            $uuid = $response_content['uuid'];


            $new_area = $areas->random();
            $new_shift_name = $faker->streetName;

            $this->startQueryLog();
            $response = $this->patch('/shifts/'.$uuid, [
                'shift_name' => $new_shift_name,
                'area' => $new_area->uuid,
            ]);
            $this->endQueryLog(6);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('shift_name', $response_content);
            $this->assertArrayHasKey('area', $response_content);
            $this->assertEquals($new_shift_name, $response_content['shift_name']);
            $this->assertEquals($new_area->uuid, $response_content['area']);
            $uuid = $response_content['uuid'];

            
            $this->startQueryLog();
            $response = $this->get('/shifts/'.$uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('shift_name', $response_content);
            $this->assertArrayHasKey('area', $response_content);
            $this->assertEquals($uuid, $response_content['uuid']);
            $this->assertEquals($new_shift_name, $response_content['shift_name']);
            $this->assertEquals($new_area->uuid, $response_content['area']);
        }
    }

    public function testDelete()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $shifts = factory(Shift::class, 10)->create();
        $this->actingAs(User::where('username', 'admin')->first());

        foreach ($shifts as $shift)
        {
            $response = $this->delete('/shifts/' . $shift->uuid);
            $this->assertContains($response->getStatusCode(), [200, 204]);
            $response = $this->get('/shifts/' . $shift->uuid);
            $response->assertStatus(404);
        }
    }
}
