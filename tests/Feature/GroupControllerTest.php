<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Topic;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\GroupSupervisor;
use App\Models\Setting;
use App\Models\StudentPreference;
use App\Models\SupervisorPreference;

class GroupControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create([
            'min_group_size' => 4,
            'max_group_size' => 6,
            'max_groups_per_topic' => 2,
            'preference_size' => 4,
            'ideal_size' => 5,
        ]);
        $admin = User::factory()->create([
            'user_type' => 'ADMIN',
            'activation_status' => 1,
        ]);
        $this->actingAs($admin);
    }

    /** @test */
    public function manage_groups_displays_correct_data()
    {
        $response = $this->get('/manage-groups');
        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            return $page->component('Admin/ManageGroup')
                ->has('studentCounts')
                ->has('supervisorCounts')
                ->has('preference_size')
                ->has('studentsNotInGroup')
                ->has('supervisorsNotInGroup')
                ->has('groups')
                ->has('supervisorGroups');
        });
    }

    /** @test */
    public function process_new_group_creates_a_group()
    {
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        $supervisor = User::factory()->create(['user_type' => 'SUPERVISOR']);
        $topic = Topic::factory()->create();

        $response = $this->post('process-new-group', [
            'group_topic' => $topic->id,
            'students' => [['student_id' => $student->id]],
            'supervisors' => [['supervisor_id' => $supervisor->id]],
        ]);

        $response->assertRedirect('manage-groups');
        $this->assertDatabaseHas('groups', ['topic_id' => $topic->id]);
        $this->assertDatabaseHas('group_students', ['student_id' => $student->id]);
        $this->assertDatabaseHas('group_supervisors', ['supervisor_id' => $supervisor->id]);
    }

    /** @test */
    public function edit_group_page_is_accessible()
    {
        $group = Group::create();
        $response = $this->get("/edit-group/{$group->id}");
        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($group) {
            return $page->component('Admin/EditGroup')
                ->where('group.id', $group->id);
        });
    }

    /** @test */
    public function edit_group_updates_correctly()
    {
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        $Topics = Topic::factory(2)->create();
        $group = Group::create(['topic_id' => $Topics[0]->id]);
        $groupSupervisorCount = GroupSupervisor::count();

        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'topic_id' => $Topics[0]->id
        ]);

        $response = $this->post('process-edit-group', [
            'group_id' => $group->id,
            'group_topic' => $Topics[1]->id,
            'students' => [['student_id' => $student->id]],
            'supervisors' => []
        ]);

        $response->assertRedirect('manage-groups');
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'topic_id' => $Topics[1]->id
        ]);
        $this->assertDatabaseHas('group_students', ['student_id' => $student->id]);
        $this->assertDatabaseCount('group_supervisors', $groupSupervisorCount);
    }

    /** @test */
    public function delete_group_removes_group_correctly()
    {
        $group = Group::create();

        $response = $this->post('delete-group', ['group_id' => $group->id]);
        $response->assertRedirect('manage-groups');
        $this->assertDatabaseMissing('groups', ['id' => $group->id]);
    }

    /** @test */
    public function delete_all_groups_removes_all_groups()
    {
        $topics = Topic::factory(2)->create();
        foreach ($topics as $topic) {
            Group::create(['topic_id' => $topic->id]);
        }

        $response = $this->post('delete-all-groups');
        $response->assertRedirect('manage-groups');
        $this->assertDatabaseCount('groups', 0);
    }

    /** @test */
    public function a_user_can_be_removed_from_a_group()
    {
        $group = Group::create();
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        $groupStudent = GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        $this->assertDatabaseHas('group_students', [
            'student_id' => $student->id,
            'group_id' => $group->id,
        ]);

        $response = $this->post(route('remove_group_user'), [
            'user_id' => $student->id,
            'group_id' => $group->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('group_students', [
            'student_id' => $student->id,
            'group_id' => $group->id,
        ]);
    }

    /** @test */
    public function user_can_be_moved_to_another_group()
    {
        $oldGroup = Group::create();
        $newGroup = Group::create();
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        GroupStudent::create([
            'group_id' => $oldGroup->id,
            'student_id' => $student->id,
        ]);

        $response = $this->post('move-group', [
            'user_id' => $student->id,
            'old_group_id' => $oldGroup->id,
            'group_id' => $newGroup->id,
            'user_type' => 'STUDENT',
        ]);
        $response->assertRedirect('manage-groups');
        $this->assertDatabaseHas('group_students', [
            'student_id' => $student->id,
            'group_id' => $newGroup->id,
        ]);
        $this->assertDatabaseMissing('group_students', [
            'student_id' => $student->id,
            'group_id' => $oldGroup->id,
        ]);
    }

    /** @test */
    public function moving_a_user_fails_when_no_new_group_is_specified()
    {
        $group = Group::create();
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        $name = $student->first_name . " " . $student->last_name;
        GroupStudent::create([
            'group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        $response = $this->post('move-group', [
            'user_id' => $student->id,
            'old_group_id' => $group->id,
            'user_name' => $name,
            'user_type' => 'STUDENT',
        ]);

        $response->assertRedirect('manage-groups');
        $response->assertSessionHas('warning', 'Student \'' . $name . '\' not updated. No topic selected.');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
