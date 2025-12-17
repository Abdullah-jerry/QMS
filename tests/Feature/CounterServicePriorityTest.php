<?php

namespace Tests\Feature;

use App\Models\Counter;
use App\Models\Department;
use App\Models\Service;
use App\Models\Token;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounterServicePriorityTest extends TestCase
{
    use RefreshDatabase;

    protected $counterUser;
    protected $department;
    protected $counter;
    protected $serviceA;
    protected $serviceB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->department = Department::create(['name' => 'Test Dept', 'code' => 'T', 'is_active' => true]);

        $this->serviceA = Service::create(['name' => 'Service A', 'department_id' => $this->department->id, 'prefix' => 'A']);
        $this->serviceB = Service::create(['name' => 'Service B', 'department_id' => $this->department->id, 'prefix' => 'B']);

        $this->counter = Counter::create(['name' => 'C1', 'counter_number' => 'C1', 'department_id' => $this->department->id, 'is_active' => true]);
        
        // Assign Services: A = Priority 2, B = Priority 1
        // Counter prefers B over A for normal tokens
        $this->counter->services()->attach([
            $this->serviceA->id => ['priority' => 2],
            $this->serviceB->id => ['priority' => 1],
        ]);

        $this->counterUser = User::factory()->create(['is_active' => true]);
        $this->counterUser->assignRole('counter_user');
        $this->counterUser->departments()->attach($this->department->id);
    }

    public function test_vip_token_overrides_counter_priority()
    {
        // Issue Normal Token B (Priority 1 Service) - Older
        $tokenB = Token::create([
            'token_number' => 'B001',
            'department_id' => $this->department->id,
            'service_id' => $this->serviceB->id,
            'issued_by' => $this->counterUser->id,
            'status' => 'waiting',
            'issued_at' => now()->subMinutes(10),
            'is_vip' => false,
        ]);

        // Issue VIP Token A (Priority 2 Service) - Newer
        // Normally A is lower priority, but VIP makes it global Priority 1
        $tokenA = Token::create([
            'token_number' => 'VA001',
            'department_id' => $this->department->id,
            'service_id' => $this->serviceA->id,
            'issued_by' => $this->counterUser->id,
            'status' => 'waiting',
            'issued_at' => now()->subMinutes(5),
            'is_vip' => true,
        ]);

        // Call Next
        $response = $this->actingAs($this->counterUser)
            ->post(route('tokens.call'), ['counter_id' => $this->counter->id]);

        $response->assertRedirect();
        
        // Should call VIP A first, even though Service A is lower priority than B
        $this->assertEquals('called', $tokenA->fresh()->status);
        $this->assertEquals('waiting', $tokenB->fresh()->status);
    }

    public function test_normal_tokens_follow_counter_priority()
    {
        // Issue Normal Token A (Priority 2 Service) - Older
        $tokenA = Token::create([
            'token_number' => 'A001',
            'department_id' => $this->department->id,
            'service_id' => $this->serviceA->id,
            'issued_by' => $this->counterUser->id,
            'status' => 'waiting',
            'issued_at' => now()->subMinutes(10),
            'is_vip' => false,
        ]);

        // Issue Normal Token B (Priority 1 Service) - Newer
        $tokenB = Token::create([
            'token_number' => 'B001',
            'department_id' => $this->department->id,
            'service_id' => $this->serviceB->id,
            'issued_by' => $this->counterUser->id,
            'status' => 'waiting',
            'issued_at' => now()->subMinutes(5),
            'is_vip' => false,
        ]);

        // Call Next
        $response = $this->actingAs($this->counterUser)
            ->post(route('tokens.call'), ['counter_id' => $this->counter->id]);

        // Should call B first because Service B is Priority 1 (vs A's Priority 2)
        $this->assertEquals('called', $tokenB->fresh()->status);
        $this->assertEquals('waiting', $tokenA->fresh()->status);
    }
}
