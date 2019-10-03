<?php

namespace Tests\Feature;

use App\User;
use App\Area;
use App\Shift;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHomepage()
    {
        $this->startQueryLog();

        $response = $this->get('/');
        $response->assertStatus(200);

        $this->endQueryLog(0);
    }

    public function testShiftsArrangementsTable()
    {
        $this->startQueryLog();

        $response = $this->get('/shifts_arrangements_table');
        $response->assertStatus(200);

        $this->endQueryLog(11);
    }

    public function testDownloadShiftsArrangementsXlsx()
    {
        $this->startQueryLog();

        $response = $this->get('/download_shifts_arrangements_xlsx');
        $response->assertStatus(200);

        $this->endQueryLog(8);
    }

    public function testShiftsArrangementsChanges()
    {
        $this->startQueryLog();

        $response = $this->get('/shifts_arrangements_changes');
        $response->assertStatus(200);

        $this->endQueryLog(3);
    }

    public function testAreasTable()
    {
        $this->startQueryLog();

        $response = $this->get('/areas_table');
        $response->assertStatus(302);

        $this->endQueryLog(0);
    }

    public function testShiftsTable()
    {
        $this->startQueryLog();

        $response = $this->get('/shifts_table');
        $response->assertStatus(302);

        $this->endQueryLog(0);
    }

    public function testUsersTable()
    {
        $this->startQueryLog();

        $response = $this->get('/users_table');
        $response->assertStatus(302);

        $this->endQueryLog(0);
    }

    public function testLoginForm()
    {
        $this->startQueryLog();

        $response = $this->get('/login');
        $response->assertStatus(200);

        $this->endQueryLog(0);
    }

    public function testPasswordReset()
    {
        $this->startQueryLog();

        $response = $this->get('/password/request_reset');
        $response->assertStatus(200);

        $this->endQueryLog(0);
    }

    public function testAdminHomepage()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/');
        $response->assertStatus(200);

        $this->endQueryLog(0);
    }

    public function testAdminShiftsArrangementsTable()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/shifts_arrangements_table');
        $response->assertStatus(200);

        $this->endQueryLog(11);
    }

    public function testAdminShiftsArrangementsChanges()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/shifts_arrangements_changes');
        $response->assertStatus(200);

        $this->endQueryLog(3);
    }

    public function testAdminAreasTable()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/areas_table');
        $response->assertStatus(200);

        $this->endQueryLog(3);
    }

    public function testAdminShiftsTable()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/shifts_table');
        $response->assertStatus(200);

        $this->endQueryLog(3);
    }

    public function testAdminUsersTable()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/users_table');
        $response->assertStatus(200);

        $this->endQueryLog(5);
    }

    public function testAdminLoginForm()
    {
        $response = $this->actingAs(User::where('username', 'admin')->first())
            ->get('/login');
        $response->assertStatus(302);
    }

    public function testAdminPasswordReset()
    {
        $response = $this->actingAs(User::where('username', 'admin')->first())
            ->get('/password/request_reset');
        $response->assertStatus(302);
    }

    public function testAdminUserSelf()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/user/self');
        $response->assertStatus(200);

        $this->endQueryLog(0);
    }

    public function testAdminChangeUserPasswordPage()
    {
        $admin = User::where('username', 'admin')->first();

        $this->startQueryLog();

        $response = $this->actingAs($admin)
            ->get('/admin/change_user_password/'.'admin');
        $response->assertStatus(200);

        $this->endQueryLog(1);
    }

    public function testMiddlewareAdmin()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/admin/change_user_password/'.'admin');
        $response->assertStatus(404);

        $this->endQueryLog(2);
    }

    public function testMiddlewareAuth()
    {
        $this->startQueryLog();

        $response = $this->json('GET', '/admin/change_user_password/'.'admin');
        $response->assertStatus(401);

        $this->endQueryLog(0);
    }

    public function testUserHomepage()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/');
        $response->assertStatus(200);

        $this->endQueryLog(1);
    }

    public function testUserShiftsArrangementsTable()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/shifts_arrangements_table');
        $response->assertStatus(200);

        $this->endQueryLog(12);
    }

    public function testUserShiftsArrangementsChanges()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/shifts_arrangements_changes');
        $response->assertStatus(200);

        $this->endQueryLog(3);
    }

    public function testUserAreasTable()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/areas_table');
        $response->assertStatus(200);

        $this->endQueryLog(4);
    }

    public function testUserShiftsTable()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/shifts_table');
        $response->assertStatus(200);

        $this->endQueryLog(4);
    }

    public function testUserUsersTable()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/users_table');
        $response->assertStatus(200);

        $this->endQueryLog(5);
    }

    public function testUserLoginForm()
    {
        $user = factory(User::class, 1)->create()->first();

        $response = $this->actingAs($user)
            ->get('/login');
        $response->assertStatus(302);
    }

    public function testUserPasswordReset()
    {
        $user = factory(User::class, 1)->create()->first();

        $response = $this->actingAs($user)
            ->get('/password/request_reset');
        $response->assertStatus(302);
    }

    public function testUserUserSelf()
    {
        $user = factory(User::class, 1)->create()->first();

        $this->startQueryLog();

        $response = $this->actingAs($user)
            ->get('/user/self');
        $response->assertStatus(200);

        $this->endQueryLog(1);
    }
}
