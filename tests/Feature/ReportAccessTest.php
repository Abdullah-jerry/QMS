<?php

namespace Tests\Feature;

use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $counterUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->admin = User::factory()->create(['is_active' => true]);
        $this->admin->assignRole('admin');

        $this->counterUser = User::factory()->create(['is_active' => true]);
        $this->counterUser->assignRole('counter_user');
    }

    public function test_admin_can_see_all_reports()
    {
        $response = $this->actingAs($this->admin)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Counter'); // Admin sees counter filter
    }

    public function test_counter_user_cannot_see_filters()
    {
        $response = $this->actingAs($this->counterUser)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertDontSee('id="counterFilter"', false); // Counter filter hidden
    }
}
