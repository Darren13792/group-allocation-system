<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
use App\Models\User;
use App\Models\Topic;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\GroupSupervisor;
use App\Models\Setting;
use App\Http\Controllers\GroupController;
use App\Mail\SendAllocation;

class SendEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (Setting::first()) {
            Setting::first()->update(['status' => 'approved']);
        }
        else {
            Setting::create([
                'min_group_size' => 4,
                'max_group_size' => 6,
                'max_groups_per_topic' => 2,
                'preference_size' => 4,
                'ideal_size' => 5,
                'status' => 'approved'
            ]);
        }
        User::factory()->create(['id' => 1,'email' => 'yourname@example.com', 'user_type' => 'STUDENT']);
        User::factory()->create(['id' => 2, 'user_type' => 'SUPERVISOR']);
        Topic::factory()->create(['id' => 1]);
        Group::create(['id' => 1,'topic_id' => 1]);
    }

    /** @test */
    public function handle_reset_password_invalid_email_correctly()
    {
        $response = $this->post('/request-password', ['email' => 'nonexistent@example.com']);
        $response->assertSessionHasErrors(['email']);
    }
    
    /** @test */
    public function handle_reset_password_valid_email_correctly()
    {
        // Mock Password facade
        Password::shouldReceive('sendResetLink')
                ->once()
                ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->post('/request-password', ['email' => 'yourname@example.com']);
        $response->assertRedirect('/request-password-success');
    }

    /** @test */
    public function handle_reset_password_throttled_error_correctly()
    {
        // Mock Password facade
        Password::shouldReceive('sendResetLink')
                ->once()
                ->andReturn(Password::RESET_THROTTLED);

        $response = $this->post('/request-password', ['email' => 'yourname@example.com']);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function handle_no_groups_allocation_emails_correctly()
    {
        Mail::fake();

        $controller = new GroupController();
        $controller->send_allocation();

        Mail::assertNotQueued(SendAllocation::class);
    }

    /** @test */
    public function send_allocation_emails_correctly()
    {
        Mail::fake();
        GroupStudent::create(['group_id' => 1,'student_id' => 1]);
        GroupSupervisor::create(['group_id' => 1,'supervisor_id' => 2]);
        $controller = new GroupController();
        $controller->send_allocation();

        Mail::assertQueued(SendAllocation::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

}
