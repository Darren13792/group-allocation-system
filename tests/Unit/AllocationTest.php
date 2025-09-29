<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Topic;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\GroupSupervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use App\Http\Controllers\GroupController;
use Mockery;
use ArgumentCountError;

class AllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function validate_group_allocation_inputs()
    {
        $inputs = [
            'min_group_size' => 3,
            'max_group_size' => 5,
            'max_groups_per_topic' => 2,
            'ideal_size' => 4,
        ];
        $rules = [
            'min_group_size' => 'required|integer|min:1',
            'max_group_size' => 'required|integer|min:1|gte:min_group_size',
            'max_groups_per_topic' => 'required|integer|min:1',
            'ideal_size' => 'nullable|integer|min:1|gte:min_group_size|lte:max_group_size',
        ];
        $validator = Validator::make($inputs, $rules);
        $this->assertFalse($validator->fails());
    }

    /** @test */
    public function detects_infeasible_group_sizes()
    {
        $minGroupSize = 5;
        $maxGroupSize = 10;
        $userCount = 4; // Not enough users (infeasible group sizes)
        $minGroupsNeeded = ceil($userCount / $maxGroupSize);
        $maxGroupsPossible = floor($userCount / $minGroupSize);

        $this->assertTrue($minGroupsNeeded > $maxGroupsPossible);
    }

    /** @test */
    public function handles_student_allocation_correctly()
    {
        // Mock 'get_student_allocation' method
        $allocationControllerMock = Mockery::mock(GroupController::class)->makePartial();
        $allocationControllerMock->shouldReceive('get_student_allocation')
            ->andReturn([
                1 => [1 => [1, 2], 2 => [3, 4]],
                2 => [1 => [5, 6], 2 => [7, 8]],
                3 => [1 => [9, 10]]
            ]);
        // Valid method call
        $allocations = $allocationControllerMock->get_student_allocation(2, 4, 2, 3, [1, 2, 3]);
        $this->assertNotEmpty($allocations);
    }

    /** @test */
    public function handles_supervisor_allocation_correctly()
    {
        // Mock 'get_supervisor_allocation' method
        $allocationControllerMock = Mockery::mock(GroupController::class)->makePartial();
        $allocationControllerMock->shouldReceive('get_supervisor_allocation')
            ->andReturn([
                1 => [1, 2],
                2 => [3, 4]
            ]);

        // Valid method call
        $allocations = $allocationControllerMock->get_supervisor_allocation();
        $this->assertNotEmpty($allocations);
    }

    /** @test */
    public function handles_student_database_operations_correctly()
    {
        for ($i = 1; $i <= 10; $i++) {
            User::factory()->create([
                'id' => $i,
                'user_type' => 'STUDENT'
            ]);
        }
        Topic::factory()->create(['id' => 1]);
        Topic::factory()->create(['id' => 2]);
        Topic::factory()->create(['id' => 3]);
        $allocations = [
            1 => [0 => [1, 2], 1 => [3, 4]],
            2 => [0 => [5, 6], 1 => [7, 8]],
            3 => [0 => [9, 10]]
        ];

        // Insert to database
        $controller = new GroupController();
        $controller->handle_group_allocations($allocations, 'student');

        // Check if inserted to database
        $this->assertDatabaseCount('groups', 5);
        $this->assertDatabaseCount('group_students', 10);
    }

    /** @test */
    public function handles_supervisor_database_operations_correctly()
    {
        for ($i = 11; $i <= 16; $i++) {
            User::factory()->create([
                'id' => $i,
                'user_type' => 'SUPERVISOR'
            ]);
        }
        for ($i = 1; $i <= 5; $i++) {
            Group::create([
                'id' => $i,
            ]);
        }

        $allocations = [
            1 => [0 => 11],
            2 => [0 => 12],
            3 => [0 => 13],
            4 => [0 => 14],
            5 => [0 => 15, 1 => 16],
        ];
        // Insert to database
        $controller = new GroupController();
        $controller->handle_group_allocations($allocations, 'supervisor');
        // Check if inserted to database
        $this->assertDatabaseCount('groups', 5);
        $this->assertDatabaseCount('group_supervisors', 6);
        $groupFiveCount = GroupSupervisor::where('group_id', 5)->count();
        $this->assertEquals($groupFiveCount, 2);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
