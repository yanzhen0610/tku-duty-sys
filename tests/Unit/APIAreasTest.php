<?php

namespace Tests\Feature;

use App\User;
use App\Area;
use Tests\TestCase;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class APIAreasTest extends TestCase
{
    use RefreshDatabase;

    private function hasArea($areas, $target)
    {
        foreach ($areas as $area)
            if ($area['uuid'] === $target->uuid &&
                    $area['area_name'] === $target->area_name)
                return true;
        return false;
    }

    public function testIndex()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();
        $response = $this->actingAs($admin)->get('/areas');
        $this->endQueryLog(3);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);
        $this->assertArrayHasKey('rows', $response_content);
        foreach ($areas as $area)
            $this->assertTrue(static::hasArea(
                $response_content['rows'], $area));
    }

    public function testShow()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $admin = User::where('username', 'admin')->first();
        $areas->load('responsible_person_eager');

        $this->actingAs($admin);

        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->get('/areas/' . $area->uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('area_name', $response_content);
            $this->assertArrayHasKey('responsible_person', $response_content);
            $this->assertEquals($area->area_name, $response_content['area_name']);
            $this->assertEquals($area->responsible_person->username, $response_content['responsible_person']);
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
            $responsible_person = $users->random();
            $area_name = $faker->city;

            $this->startQueryLog();
            $response = $this->post('/areas', [
                'area_name' => $area_name,
                'responsible_person' => $responsible_person->username,
            ]);
            $this->endQueryLog(5);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('area_name', $response_content);
            $this->assertArrayHasKey('responsible_person', $response_content);
            $this->assertEquals($area_name, $response_content['area_name']);
            $this->assertEquals($responsible_person->username, $response_content['responsible_person']);
            $uuid = $response_content['uuid'];

            
            $this->startQueryLog();
            $response = $this->get('/areas/'.$uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('area_name', $response_content);
            $this->assertArrayHasKey('responsible_person', $response_content);
            $this->assertEquals($uuid, $response_content['uuid']);
            $this->assertEquals($area_name, $response_content['area_name']);
            $this->assertEquals($responsible_person->username, $response_content['responsible_person']);
        }
    }

    public function testUpdate()
    {
        $faker = Factory::create();
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $this->actingAs(User::where('username', 'admin')->first());

        foreach ($areas as $area)
        {
            $this->startQueryLog();
            $response = $this->get('/areas/' . $area->uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('area_name', $response_content);
            $this->assertArrayHasKey('responsible_person', $response_content);
            $this->assertEquals($area->area_name, $response_content['area_name']);
            $this->assertEquals($area->responsible_person->username, $response_content['responsible_person']);
            $uuid = $response_content['uuid'];


            $new_responsible_person = $users->random();
            $new_area_name = $faker->city;

            $this->startQueryLog();
            $response = $this->patch('/areas/'.$uuid, [
                'area_name' => $new_area_name,
                'responsible_person' => $new_responsible_person->username,
            ]);
            $this->endQueryLog(6);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('area_name', $response_content);
            $this->assertArrayHasKey('responsible_person', $response_content);
            $this->assertEquals($new_area_name, $response_content['area_name']);
            $this->assertEquals($new_responsible_person->username, $response_content['responsible_person']);
            $uuid = $response_content['uuid'];

            
            $this->startQueryLog();
            $response = $this->get('/areas/'.$uuid);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('uuid', $response_content);
            $this->assertArrayHasKey('area_name', $response_content);
            $this->assertArrayHasKey('responsible_person', $response_content);
            $this->assertEquals($uuid, $response_content['uuid']);
            $this->assertEquals($new_area_name, $response_content['area_name']);
            $this->assertEquals($new_responsible_person->username, $response_content['responsible_person']);
        }
    }

    public function testDelete()
    {
        $users = factory(User::class, 10)->create();
        $areas = factory(Area::class, 10)->create();
        $this->actingAs(User::where('username', 'admin')->first());
        foreach ($areas as $area)
        {
            $response = $this->delete('/areas/' . $area->uuid);
            $this->assertContains($response->getStatusCode(), [200, 204]);
            $response = $this->get('/areas/' . $area->uuid);
            $response->assertStatus(404);
        }
    }
}
