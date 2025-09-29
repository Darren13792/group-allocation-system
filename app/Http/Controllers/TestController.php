<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Topic;
use App\Models\StudentPreference;
use App\Models\SupervisorPreference;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendAllocation;

class TestController extends Controller
{
    public function generate_preferences()
    {
        StudentPreference::truncate();
        SupervisorPreference::truncate();
        $studentIds = User::where('user_type', 'STUDENT')->get(['id'])->pluck('id');
        $supervisorIds = User::where('user_type', 'SUPERVISOR')->get(['id'])->pluck('id');
        $topicIds = Topic::all('id')->pluck('id');
        $weights = [1, 2, 3, 4];

        foreach ($studentIds as $studentId) {
            $selectedTopicIds = $topicIds->shuffle()->take(4);
            foreach ($selectedTopicIds->zip($weights) as $pair) {
                StudentPreference::create([
                    'student_id' => $studentId,
                    'topic_id' => $pair[0],
                    'weight' => $pair[1]
                ]);
            }
        }
        foreach ($supervisorIds as $supervisorId) {
            $selectedTopicIds = $topicIds->shuffle()->take(rand(2, 6))->sort()->values();
            foreach ($selectedTopicIds as $topicId) {
                SupervisorPreference::create([
                    'supervisor_id' => $supervisorId,
                    'topic_id' => $topicId,
                ]);
            }
        }
        return redirect(route('manage_user'))->with('success', 'User preferences generated');
    }

    public function test_send_allocation()
    {
        $testEmail = 'yourname@example.com'; // Change to personal email
        $testUser = User::where('email', $testEmail)
        ->with([
            'group_students.group.group_students.student', 
            'group_students.group.group_supervisors.supervisor', 
            'group_students.group.topic',
            'group_supervisors.group.group_students.student', 
            'group_supervisors.group.group_supervisors.supervisor', 
            'group_supervisors.group.topic'
        ])->first();
        $name = $testUser->first_name . " " . $testUser->last_name;
        $userType = $testUser->user_type;
        $groups = [];
        if ($userType == "STUDENT" && $testUser->group_students) {
            $groups[] = $testUser->group_students->group;
        } else if ($userType == "SUPERVISOR") {
            $groups = $testUser->group_supervisors->map(function ($groupSupervisor) {
                return $groupSupervisor->group;
            })->unique('id')->all();
        }
        if (!empty($groups)) {
            Mail::to($testEmail)->send(new SendAllocation($name, $groups));
            return redirect(route("manage_groups"))->with('success', 'Test email sent to \'' . $testEmail . '\'.');
        } else {
            return redirect(route("manage_groups"))->with('warning', 'User with email \'' . $testEmail . '\' does not exist or is not in a group.');
        }
    }
}
