<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testForgetAndResetPassword()
    {
        $users = factory(User::class, 10)->create();
        $admin = User::where('username', 'admin')->first();
        foreach ($users as $user) {
            $this->actingAs($admin);
            $response = $this->get('/users/' . $user->username);
            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($user->username, $response_content['username']);
            $this->assertNull($response_content['reset_password']);
            Auth::logout();
            $response = $this->post('/password/request_reset', [
                'username' => $user->username,
            ]);
            $response->assertStatus(302);
            $this->actingAs($admin);
            $response = $this->get('/users/' . $user->username);
            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($user->username, $response_content['username']);
            $this->assertNotNull($response_content['reset_password']);
            $response = $this->post('/admin/change_user_password/' . $user->username, [
                'new_password' => 'password',
            ]);
            $response->assertStatus(302);
            $response = $this->get('/users/' . $user->username);
            $response->assertStatus(200);
            $response_content = json_decode($response->content(), true);
            $this->assertEquals($user->username, $response_content['username']);
            $this->assertNull($response_content['reset_password']);
        }
    }

    public function testResetPassword()
    {
        $users = factory(User::class, 10)->create();
        foreach ($users as $user)
        {
            $this->actingAs($user);
            $response = $this->post('/user/self/reset_password', [
                'current_password' => 'password',
                'new_password' => 'password',
                'password_confirmation' => 'password',
            ]);
            $response->assertStatus(302);
        }
    }

    public function testResetPasswordWrongCurrent()
    {
        $users = factory(User::class, 10)->create();
        foreach ($users as $user)
        {
            $this->actingAs($user);
            $response = $this->post('/user/self/reset_password', [
                'current_password' => 'wrong_password',
                'new_password' => 'password',
                'password_confirmation' => 'password',
            ]);
            $response->assertStatus(302);
        }
    }

    public function testLogin()
    {
        $users = factory(User::class, 10)->create();
        foreach ($users as $user)
        {
            if (Auth::check()) Auth::logout();
            $response = $this->post('/login', [
                'username' => $user->username,
                'password' => 'password',
            ]);
            $response->assertStatus(302);
            $this->assertNotRegExp('/\/login$/', $response->headers->get('location'));
        }
    }
}
