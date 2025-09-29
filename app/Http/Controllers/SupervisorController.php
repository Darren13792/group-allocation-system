<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Topic;
use App\Models\SupervisorPreference;
use App\Models\Setting;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
    public function index() 
    {
        return Inertia::render('Supervisor/SupervisorDashboard', [
            'deadline' => Setting::first()->deadline,
        ]);
    }

    public function preference()
    {
        $user = User::with('supervisor_preferences')->find(auth()->id())->toArray();
        return Inertia::render('Supervisor/SupervisorPreferences', [
            'user' => $user,
            'topics' => Topic::all(),
        ]);
    }

    public function process_supervisor_preference()
    {
        $status = Setting::first()->status;
        if ( $status === 'notstarted') {
            return back()->with('warning', 'Submissions are currently closed. Please wait until the admin grants permission.');
        }
        if ($status === 'ended' || $status === 'approved') {
            return back()->with('danger', 'Deadline date passed. Submissions are closed.');
        }
        $formData = request()->all();
        $supervisorId = auth()->id();
        $topicIds = Topic::pluck('id')->all();

        foreach ($formData as $topicId => $value) {
            if (!in_array($topicId, $topicIds)) {
                // Validation for topic existence
                return back()->with('warning', 'Topics not submitted. Topic \'' . $topicId . '\' does not exist');
            }
            $existingPreference = SupervisorPreference::where('topic_id', $topicId)->where('supervisor_id', $supervisorId)->first();

            if ($value) {
                if (!$existingPreference) {
                    SupervisorPreference::create([
                        'topic_id' => $topicId,
                        'supervisor_id' => $supervisorId,
                    ]);
                }
            }
            else {
                // Delete if value is false
                if ($existingPreference) {
                    $existingPreference->delete();
                }
            }
        }
        return redirect(route('user_dashboard'))->with('success', 'Availability successfully submitted.');
    }
}
