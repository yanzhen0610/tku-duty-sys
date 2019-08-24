<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Faker\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class APIUsersTest extends TestCase
{
    use RefreshDatabase;

    private static function hasUser($users, $target)
    {
        foreach ($users as $user) {
            if ($user['username'] === $target['username'] &&
                    $user['display_name'] === $target['display_name'] &&
                    $user['mobile_ext'] === $target['mobile_ext'])
                return true;
        }
        return false;
    }

    public function testIndex()
    {
        $users = factory(User::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        foreach ($users as $user)
        {
            $user->is_admin = (bool) (rand() & 1);
            $user->is_disabled = (bool) (rand() & 1);
            $user->refresh();
        }

        $this->startQueryLog();
        $response = $this->actingAs($admin)->get('/users');
        $this->endQueryLog(3);

        $response->assertStatus(200);
        $response_content = json_decode($response->content(), true);
        $this->assertArrayHasKey('rows', $response_content);
        foreach ($users as $user) {
            $this->assertTrue(static::hasUser(
                $response_content['rows'], $user));
        }
    }

    public function testShow()
    {
        $users = factory(User::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);

        foreach ($users as $user)
        {
            $user->is_admin = (bool) (rand() & 1);
            $user->is_disabled = (bool) (rand() & 1);
            $user->refresh();
        }

        foreach ($users as $user) {
            $this->startQueryLog();
            $response = $this->get('/users/' . $user->username);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertArrayHasKey('key', $response_content);
            $this->assertArrayHasKey('username', $response_content);
            $this->assertArrayHasKey('display_name', $response_content);
            $this->assertArrayHasKey('mobile_ext', $response_content);
            $this->assertArrayHasKey('is_admin', $response_content);
            $this->assertArrayHasKey('is_disabled', $response_content);
            $this->assertArrayHasKey('reset_password', $response_content);
            $this->assertArrayHasKey('url', $response_content['reset_password']);
            $this->assertArrayHasKey('update_url', $response_content);
            $this->assertArrayHasKey('update_url', $response_content);
            $this->assertArrayHasKey('destroy_url', $response_content);
            $this->assertEquals($user->username, $response_content['username']);
            $this->assertEquals($user->display_name, $response_content['display_name']);
            $this->assertEquals($user->mobile_ext, $response_content['mobile_ext']);
        }
    }

    public function testUpdate()
    {
        $faker = Factory::create();
        $users = factory(User::class, 100)->create();
        $admin = User::where('username', 'admin')->first();

        foreach ($users as $user)
        {
            $user->is_admin = (bool) (rand() & 1);
            $user->is_disabled = (bool) (rand() & 1);
            $user->refresh();
        }

        $this->actingAs($admin);

        foreach ($users as $user) {
            $this->startQueryLog();
            $response = $this->get('/users/' . $user->username);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($user->username, $response_content['key']);
            $this->assertEquals($user->display_name, $response_content['display_name']);
            $this->assertEquals($user->mobile_ext, $response_content['mobile_ext']);
            $this->assertEquals($user->is_admin, $response_content['is_admin']);
            $this->assertEquals($user->is_disabled, $response_content['is_disabled']);

            
            $new_display_name = $faker->name;
            $new_mobile_ext = $faker->phoneNumber;
            $new_is_admin = (bool) (rand() & 1);
            $new_is_disabled = (bool) (rand() & 1);

            $this->startQueryLog();
            $response = $this->patch('/users/' . $user->username, [
                'display_name' => $new_display_name,
                'mobile_ext' => $new_mobile_ext,
                'is_admin' => $new_is_admin,
                'is_disabled' => $new_is_disabled,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($user->username, $response_content['key']);
            $this->assertEquals($new_display_name, $response_content['display_name']);
            $this->assertEquals($new_mobile_ext, $response_content['mobile_ext']);
            $this->assertEquals($new_is_admin, $response_content['is_admin']);
            $this->assertEquals($new_is_disabled, $response_content['is_disabled']);
        }
    }

    public function testDelete()
    {
        $users = factory(User::class, 10)->create();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);

        foreach ($users as $user)
        {
            $user->is_admin = (bool) (rand() & 1);
            $user->is_disabled = (bool) (rand() & 1);
            $user->refresh();
        }

        foreach ($users as $user) {
            $this->startQueryLog();
            $response = $this->get('/users/' . $user->username);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($user->username, $response_content['key']);
            $this->assertEquals($user->display_name, $response_content['display_name']);
            $this->assertEquals($user->mobile_ext, $response_content['mobile_ext']);
            $this->assertEquals($user->is_admin, $response_content['is_admin']);
            $this->assertEquals($user->is_disabled, $response_content['is_disabled']);


            $this->startQueryLog();
            $response = $this->delete('/users/' . $user->username);
            $this->endQueryLog(2);
            $this->assertContains($response->getStatusCode(), [200, 204]);


            $this->startQueryLog();
            $response = $this->get('/users/' . $user->username);
            $this->endQueryLog(1);
            $response->assertStatus(404);
        }
    }

    public function testStore()
    {
        $faker = Factory::create();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);

        for ($i = 0; $i < 10; ++$i) {
            $username = $faker->unique()->userName;
            $display_name = $faker->name;
            $mobile_ext = $faker->phoneNumber;
            $is_admin = (bool) (rand() & 1);
            $is_disabled = (bool) (rand() & 1);

            $this->startQueryLog();
            $response = $this->post('/users/', [
                'username' => $username,
                'display_name' => $display_name,
                'mobile_ext' => $mobile_ext,
                'is_admin' => $is_admin,
                'is_disabled' => $is_disabled,
            ]);
            $this->endQueryLog(8);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($username, $response_content['key']);
            $this->assertEquals($display_name, $response_content['display_name']);
            $this->assertEquals($mobile_ext, $response_content['mobile_ext']);
            $this->assertEquals($is_admin, $response_content['is_admin']);
            $this->assertEquals($is_disabled, $response_content['is_disabled']);


            $this->startQueryLog();
            $response = $this->get('/users/' . $username);
            $this->endQueryLog(3);

            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($username, $response_content['key']);
            $this->assertEquals($display_name, $response_content['display_name']);
            $this->assertEquals($mobile_ext, $response_content['mobile_ext']);
            $this->assertEquals($is_admin, $response_content['is_admin']);
            $this->assertEquals($is_disabled, $response_content['is_disabled']);
        }
    }

    public function testAdminResetPassword()
    {
        $user = factory(User::class, 1)->create()->first();
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin);

        $this->startQueryLog();
        $response = $this->delete('/users/'.$user->username.'/password');
        $this->endQueryLog(4);

        $response->assertStatus(200);
    }
}
