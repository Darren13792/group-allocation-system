<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Topic;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\GroupSupervisor;
use App\Models\Setting;

class SharedController extends Controller
{
    public function index() 
    {
        return Inertia::render('Users/UserDashboard', [
            'deadline' => Setting::first()->deadline,
        ]);
    }

    public function view_topics()
    {
        return Inertia::render('Users/ViewTopics', [
            'topics' => Topic::all(),
        ]);
    }

    public function view_group()
    {
        if (Setting::first()->status !== 'approved') {
            return back()->with('danger', 'Groups are currently being approved. Please come back later.');
        }
        $userType = auth()->user()->user_type;
        $userId = auth()->id();
        $groups = [];
        if ($userType == "STUDENT") {
            $groups = GroupStudent::where('student_id', $userId)
            ->with('group.group_students.student', 'group.group_supervisors.supervisor', 'group.topic')->get()
            ->map(function ($groupStudent) {return $groupStudent->group;})->toArray();    
        } else if ($userType == "SUPERVISOR") {
            $groups = GroupSupervisor::where('supervisor_id', $userId)
            ->with('group.group_students.student', 'group.group_supervisors.supervisor', 'group.topic')->get()
            ->map(function ($groupSupervisor) {return $groupSupervisor->group;})->toArray();
        }
        return Inertia::render('Users/ViewGroup', [
            'groups' => $groups,
        ]);
    }
}
