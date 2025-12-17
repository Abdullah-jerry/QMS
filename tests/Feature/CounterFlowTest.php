<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Department;
use App\Models\Service;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounterFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $counterUser;
    protected $department;
    protected $counter;
    protected $vipService;
    protected $normalService;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Roles
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        // Create Department
        $this->department = Department::create([
            'name' => 'Test Dept',
            'code' => 'T',
            'is_active' => true,
        ]);

        // Create Services
        $this->vipService = Service::create([
            'name' => 'VIP Service',
            'department_id' => $this->department->id,
            'priority' => 1,
            'prefix' => 'V',
        ]);

        $this->normalService = Service::create([
            'name' => 'Normal Service',
            'department_id' => $this->department->id,
            'priority' => 10,
            'prefix' => 'N',
        ]);

        // Create Counter
        $this->counter = Counter::create([
            'name' => 'Test Counter',
            'counter_number' => 'C1',
            'department_id' => $this->department->id,
            'is_active' => true,
        ]);

        // Create User
        $this->counterUser = User::factory()->create(['is_active' => true]);
        $this->counterUser->assignRole('counter_user');
        $this->counterUser->departments()->attach($this->department->id);
    }

    public function test_counter_selection()
    {
        $response = $this->actingAs($this->counterUser)
            ->get(route('dashboard', ['select_counter' => $this->counter->id]));

        $response->assertStatus(200);
        $response->assertSessionHas('selected_counter_id', $this->counter->id);
    }

    public function test_call_next_priority()
    {
        // Issue Normal Token First
        $normalToken = Token::create([
            'token_number' => 'N001',
            'department_id' => $this->department->id,
            'issued_by' => $this->counterUser->id,
            'status' => 'waiting',
            'issued_at' => now()->subMinutes(10),
            'priority' => 10,
        ]);

        // Issue VIP Token Later
        $vipToken = Token::create([
            'token_number' => 'V001',
            'department_id' => $this->department->id,
            'issued_by' => $this->counterUser->id,
            'status' => 'waiting',
            'issued_at' => now()->subMinutes(5),
            'priority' => 1,
        ]);

        // Call Next
        $response = $this->actingAs($this->counterUser)
            ->post(route('tokens.call'), ['counter_id' => $this->counter->id]);

        $response->assertRedirect();
        
        // Verify VIP was called first despite being newer
        $this->assertEquals('called', $vipToken->fresh()->status);
        $this->assertEquals('waiting', $normalToken->fresh()->status);
    }

    public function test_complete_token()
    {
        $token = Token::create([
            'token_number' => 'N001',
            'department_id' => $this->department->id,
            'issued_by' => $this->counterUser->id,
            'status' => 'called',
            'counter_id' => $this->counter->id,
            'called_at' => now(),
            'priority' => 10,
            'issued_at' => now()->subMinutes(20),
        ]);

        $response = $this->actingAs($this->counterUser)
            ->post(route('tokens.complete', $token->id));

        $response->assertRedirect();
        $this->assertEquals('completed', $token->fresh()->status);
    }
}
