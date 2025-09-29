<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Topic;
use App\Models\Setting;
use App\Models\StudentPreference;
use App\Models\SupervisorPreference;

class AdminControllerTest extends TestCase
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
    public function dashboard_displays_correct_data()
    {
        $response = $this->get('/admin-dashboard');
        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            return $page->component('Admin/AdminDashboard')
                ->has('settings')
                ->has('userCount')
                ->has('topicCount')
                ->has('prefCount')
                ->has('nonActiveCount');
        });
    }

    /** @test */
    public function process_new_user_creates_a_user()
    {
        $response = $this->post('/process-new-user', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'user_type' => 'STUDENT'
        ]);

        $response->assertRedirect('manage-user');
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }

    /** @test */
    public function manage_user_page_displays_correct_data()
    {
        $response = $this->get('/manage-user');
        $response->assertStatus(200);
        $response->assertInertia(function ($page) {
            return $page->component('Admin/ManageUser')
                ->has('students')
                ->has('supervisors')
                ->has('admins')
                ->has('preference_size');
        });
    }

    /** @test */
    public function edit_user_page_is_accessible()
    {
        $user = User::factory()->create();
        $response = $this->get("/edit-user/{$user->id}");
        $response->assertStatus(200);
        $response->assertInertia(function ($page) use ($user) {
            return $page->component('Admin/EditUser')
                ->where('user.id', $user->id);
        });
    }

    /** @test */
    public function process_edit_user_correctly_updates_user()
    {
        $user = User::factory()->create(['first_name' => 'OldName', 'user_type' => 'STUDENT']);
        $response = $this->post("/process-edit-user", [
            'user_id' => $user->id,
            'first_name' => 'NewName',
            'last_name' => $user->last_name,
            'email' => $user->email,
            'activation_status' => $user->activation_status,
            'user_type' => $user->user_type,
        ]);

        $response->assertRedirect('/manage-user');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'NewName'
        ]);
    }

    /** @test */
    public function delete_user_removes_user_correctly()
    {
        $user = User::factory()->create();
        $response = $this->post("/delete-user", [
            'user_id' => $user->id,
        ]);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function process_new_topic_creates_a_topic()
    {
        $response = $this->post('process-new-topic', [
            'topic_name' => 'New Topic',
            'description' => 'Test description.',
            'due_date' => '2024-06-21'
        ]);

        $response->assertRedirect('/manage-topic');
        $this->assertDatabaseHas('topics', [
            'topic_name' => 'New Topic'
        ]);
    }

    /** @test */
    public function process_edit_topic_correctly_updates_topic()
    {
        $topic = Topic::factory()->create(['topic_name' => 'Old Topic']);
        $response = $this->post("/process-edit-topic", [
            'topic_id' => $topic->id,
            'topic_name' => 'New Topic',
            'description' => $topic->description,
            'due_date' => $topic->due_date
        ]);

        $response->assertRedirect('/manage-topic');
        $this->assertDatabaseHas('topics', [
            'id' => $topic->id,
            'topic_name' => 'New Topic'
        ]);
    }

    /** @test */
    public function delete_topic_removes_topic_correctly()
    {
        $topic = Topic::factory()->create();
        $response = $this->post("/delete-topic", [
            'topic_id' => $topic->id,
        ]);
        $response->assertRedirect('/manage-topic');
        $this->assertDatabaseMissing('topics', [
            'id' => $topic->id
        ]);
    }

    /** @test */
    public function preferences_are_correctly_processed_and_updated()
    {
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        $topics = Topic::factory(5)->create();
        $settings = Setting::first();
        $preferences = [];
        for ($i = 1; $i <= $settings->preference_size; $i++) {
            $preferences['preference_' . $i] = $topics[$i-1]->id;
        }
        $preferences['user_id'] = $student->id;
        // Create preferences
        $response = $this->post("/process-edit-preference", $preferences);
        $response->assertRedirect('/manage-user');
        for ($i = 1; $i <= $settings->preference_size; $i++) {
            $this->assertDatabaseHas('student_preferences', [
                'student_id' => $student->id,
                'topic_id' => $topics[$i-1]->id,
                'weight' => $i
            ]);
        }
        $preferences['preference_1'] = $topics[4]->id;
        // Update Preferences
        $response = $this->post("/process-edit-preference", $preferences);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseHas('student_preferences', [
            'student_id' => $student->id,
            'topic_id' => $topics[4]->id,
            'weight' => 1
        ]);
        $this->assertDatabaseMissing('student_preferences', [
            'student_id' => $student->id,
            'topic_id' => $topics[0]->id,
        ]);
    }

    /** @test */
    public function availability_is_correctly_processed_and_updated()
    {
        $topics = Topic::factory(5)->create();
        $supervisor = User::factory()->create(['user_type' => 'SUPERVISOR']);
        $availability = [
            'user_id' => $supervisor->id,
            $topics[0]->id => true,
            $topics[1]->id => false,
            $topics[2]->id => true,
            $topics[3]->id => false,
            $topics[4]->id => true,
        ];
        // Create availabilitys
        $response = $this->post("/process-edit-availability", $availability);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseHas('supervisor_preferences', [
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topics[0]->id,
            'topic_id' => $topics[2]->id,
            'topic_id' => $topics[4]->id,
        ]);
        $this->assertDatabaseMissing('supervisor_preferences', [
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topics[1]->id,
            'topic_id' => $topics[3]->id,
        ]);
        // Update availability
        $response = $this->post("/process-edit-availability", [
            'user_id' => $supervisor->id,
            $topics[0]->id => false,
            $topics[1]->id => true,
        ]);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseHas('supervisor_preferences', [
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topics[1]->id,
        ]);
        $this->assertDatabaseMissing('supervisor_preferences', [
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topics[0]->id,
        ]);
    }

    /** @test */
    public function preferences_are_deleted_when_student_changes_roles()
    {
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        $topic = Topic::factory()->create();
        StudentPreference::create([
            'student_id' => $student->id,
            'topic_id' => $topic->id,
            'weight' => 1
        ]);
        $response = $this->post("/process-edit-user", [
            'user_id' => $student->id,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'email' => $student->email,
            'activation_status' => $student->activation_status,
            'user_type' => 'SUPERVISOR',
        ]);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseMissing('student_preferences', [
            'student_id' => $student->id,
            'topic_id' => $topic->id
        ]);
    }

    /** @test */
    public function availability_is_reset_when_supervisor_changes_roles()
    {
        $supervisor = User::factory()->create(['user_type' => 'SUPERVISOR']);
        $topic = Topic::factory()->create();
        SupervisorPreference::create([
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topic->id
        ]);
        $response = $this->post("/process-edit-user", [
            'user_id' => $supervisor->id,
            'first_name' => $supervisor->first_name,
            'last_name' => $supervisor->last_name,
            'email' => $supervisor->email,
            'activation_status' => $supervisor->activation_status,
            'user_type' => 'STUDENT',
        ]);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseMissing('supervisor_preferences', [
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topic->id
        ]);
    }

    /** @test */
    public function delete_preferences_removes_preferences_correctly()
    {
        $student = User::factory()->create(['user_type' => 'STUDENT']);
        $topic = Topic::factory()->create();
        StudentPreference::create([
            'student_id' => $student->id,
            'topic_id' => $topic->id,
            'weight' => 1,
        ]);
        $response = $this->post("/delete-preferences", [
            'user_id' => $student->id
        ]);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseMissing('student_preferences', [
            'student_id' => $student->id,
            'topic_id' => $topic->id
        ]);
    }

    /** @test */
    public function reset_availability_removes_availability_correctly()
    {
        $supervisor = User::factory()->create(['user_type' => 'SUPERVISOR']);
        $topic = Topic::factory()->create();
        SupervisorPreference::create([
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topic->id,
        ]);
        $response = $this->post("/reset-availability", [
            'user_id' => $supervisor->id
        ]);
        $response->assertRedirect('/manage-user');
        $this->assertDatabaseMissing('supervisor_preferences', [
            'supervisor_id' => $supervisor->id,
            'topic_id' => $topic->id
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

