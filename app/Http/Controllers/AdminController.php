<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Topic;
use App\Models\StudentPreference;
use App\Models\SupervisorPreference;
use App\Models\GroupStudent;
use App\Models\GroupSupervisor;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index() 
    {
        $userCount = User::where('user_type', '!=', 'ADMIN');
        $studentPreferenceCount = StudentPreference::distinct('student_id')->count('student_id');
        $supervisorPreferenceCount = SupervisorPreference::distinct('supervisor_id')->count('supervisor_id');
        $PreferencesCount = $studentPreferenceCount + $supervisorPreferenceCount;
        return Inertia::render('Admin/AdminDashboard', [
            'settings' => Setting::first(),
            'userCount' => $userCount->count(),
            'topicCount' => Topic::count(),
            'prefCount' => $studentPreferenceCount + $supervisorPreferenceCount,
            'nonActiveCount' => $userCount->where('activation_status', 0)->count(),
        ]);
    }

    public function process_new_user() 
    {
        validator(request()->all(), [
            'first_name' => 'required|min:2|max:30',
            'last_name' => 'required|min:2|max:30',
            'email' => 'required|email|unique:users',
            'user_type' => 'required'
        ],
        )->validate();

        $user = User::create(
            array_merge(
                request()->all(), 
                ['password' => bcrypt('password')]
            )
        );
        $user->save();

        return redirect(route("manage_user"))->with('success', 'User \'' . $user->first_name . ' ' . $user->last_name . '\' created.');
    }

    public function process_new_topic() 
    {
        validator(request()->all(), [
            'topic_name' => 'required|min:2|max:30|unique:topics',
            'description' => 'required|min:2',
            'due_date' => 'required',
        ],
        )->validate();

        $topic = Topic::create(
            array_merge(
                request()->all(),
            )
        );
        $topic->save();

        return redirect(route("manage_topic"))->with('success', 'Topic \'' . $topic->topic_name . '\' created.');
    }

    public function manage_user()
    {
        $preference_size = DB::table('settings')->first()->preference_size;
        $students = User::with('student_preferences.topic')->where('user_type', 'STUDENT')->get()->toArray();
        $supervisors = User::with('supervisor_preferences.topic')->where('user_type', 'SUPERVISOR')->get()->toArray();
        $admins = User::where('user_type', 'ADMIN')->get()->toArray();
        return Inertia::render('Admin/ManageUser', [
            'students' => $students,
            'supervisors' => $supervisors,
            'admins' => $admins,
            'preference_size' => $preference_size,
        ]);
    }

    public function manage_topic()
    {
        return Inertia::render('Admin/ManageTopic', [
            'topics' => Topic::orderBy('created_at', 'asc')->get(),
        ]);
    }

    public function edit_user($id)
    {
        $preference_size = DB::table('settings')->first()->preference_size;
        $user = User::with('student_preferences', 'supervisor_preferences')->find($id);
        if (!$user) {
            return redirect(route("manage_user"))->with('warning', 'User with id \'' . $id . '\' not found.');
        }
        return Inertia::render('Admin/EditUser', [
            'topics' => Topic::all(),
            'user' => $user->toArray(),
            'preference_size' => $preference_size,
        ]);
    }

    public function process_edit_user()
    {
        $user = User::find(request()['user_id']);
        // Prevent edit to taken email excluding users current email
        $check = request('email') === $user->email;
        validator(request()->all(), [
            'first_name' => 'required|min:2|max:30',
            'last_name' => 'required|min:2|max:30',
            'email' => $check ? 'required|email' : 'required|email|unique:users',
            'activation_status' => 'required',
            'user_type' => 'required'
        ],
        )->validate();

        // Delete Preferences if user_type is changed
        if ($user->user_type != request()->user_type) {
            if ($user->user_type == 'STUDENT'){
                GroupStudent::where("student_id", $user->id)->delete();
                $this->delete_preferences();
            }
            if ($user->user_type == 'SUPERVISOR'){
                GroupSupervisor::where("supervisor_id", $user->id)->delete();
                $this->reset_availability();
            }
        }

        $user->update([
            'first_name' => request()['first_name'],
            'last_name' => request()['last_name'],
            'email' => request()['email'],
            'activation_status' => request()['activation_status'],
            'user_type' => request()['user_type'],
        ]);

        if ($user->wasChanged()) {
            return redirect(route("manage_user"))->with('success', 'User \'' . $user->first_name . ' ' . $user->last_name . '\' successfully updated.');
        }
        else {
            return back()->with('warning', 'User \'' . $user->first_name . ' ' . $user->last_name . '\' not updated. No fields were changed.');
        }
    }

    public function process_edit_preference()
    {
        $preference_size = DB::table('settings')->first()->preference_size;
        $preference_validation = [];
        $newPreferences = [];
        $weight = 0;
        $topicIds = Topic::pluck('id')->all();
        $studentPreferences = StudentPreference::where('student_id', request()->user_id)->get();

        for ($i = 1; $i <= $preference_size; $i++) {
            $preference_validation['preference_' . $i] = 'required|integer';
        }
        validator(request()->all(), $preference_validation)->validate();

        for ($i = 1; $i <= $preference_size; $i++) {
            $topicValue = request('preference_' . $i);
            if (!in_array($topicValue, $topicIds)) {
                // Validation for topic existence
                return back()->with('warning', 'Topics not submitted. Topic \'' . $topicValue . '\' does not exist');
            }
            $newPreferences[$i] = $topicValue;
        }
        // Validation for duplicate inputs
        if (count(array_unique($newPreferences)) < count($newPreferences)) {
            return back()->with('warning', 'Preferences not submitted. No duplicate topics allowed.');
        }

        if ($studentPreferences->isEmpty()) {
            // Create preferences for first time
            for ($i = 1; $i <= $preference_size; $i++) {
                $topicValue = request('preference_' . $i);
                if ($topicValue !== 'none') {
                    StudentPreference::create([
                        'student_id' => request()['user_id'],
                        'topic_id' => $topicValue,
                        'weight' => ++$weight,
                    ]);
                }
            }
            return redirect(route('manage_user'))->with('success', 'Preferences successfully submitted.');
        } else {
            $updated = false;
            foreach ($studentPreferences as $preference) {
                if (in_array($preference->topic_id, $newPreferences) && $preference->topic_id !== $newPreferences[$preference->weight]) {
                    // Update the preference topic to null when topic is desired
                    $preference->update([
                        'topic_id' => null
                    ]);
                    $updated = true;
                }
            }
            foreach ($newPreferences as $weight => $topicId) {
                $preference = $studentPreferences->where('weight', $weight)->first();
                if ($preference) {
                    if ($preference->topic_id !== $topicId) {
                        $preference->update(['topic_id' => $topicId]);
                        $updated = true;
                    }
                }
            }
            if ($updated==true) {
                return redirect(route("manage_user"))->with('success', 'Preferences successfully updated.');
            }
            else {
                return back()->with('warning', 'Preferences not updated. No fields were changed.');
            }
        }
    }

    public function process_edit_availability()
    {
        $formData = request()->except('user_id');
        $supervisorId = request()->all()['user_id'];
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
        return redirect(route('manage_user'))->with('success', 'Supervisors availability successfully submitted.');
    }

    public function delete_preferences()
    {
        $studentPreferences = StudentPreference::where('student_id', request()->user_id)->get();
        foreach ($studentPreferences as $preference) {
            $preference->delete();
        }
        return redirect(route("manage_user"))->with('success', 'Preferences successfully deleted.');
    }

    public function reset_availability()
    {
        $supervisorPreferences = SupervisorPreference::where('supervisor_id', request()->user_id)->get();
        foreach ($supervisorPreferences as $preference) {
            $preference->delete();
        }
        return redirect(route("manage_user"))->with('success', 'Availability successfully reset.');
    }

    public function edit_topic($id)
    {
        $topic = Topic::find($id);
        if (!$topic) {
            return redirect(route("manage_topic"))->with('warning', 'Topic with id \'' . $id . '\' not found.');
        }
        return Inertia::render('Admin/EditTopic', [
            'topic' => $topic,
        ]);
    }

    public function process_edit_topic()
    {
        validator(request()->all(), [
            'topic_name' => 'required|min:2|max:30',
            'description' => 'required|min:2',
            'due_date' => 'required',
        ],
        )->validate();
        $topic = Topic::find(request()['topic_id']);
        $topic->update([
            'topic_name' => request()['topic_name'],
            'description' => request()['description'],
            'due_date' => request()['due_date'],
        ]);

        if ($topic->wasChanged()) {
            return redirect(route("manage_topic"))->with('success', 'Topic \'' . $topic->topic_name . '\' successfully updated.');
        }
        else {
            return back()->with('warning', 'Topic \'' . $topic->topic_name . '\' not updated. No fields were changed.');
        }
    }

    public function delete_user()
    {
        $user = $delete = User::find(request()['user_id']);
        if ($delete) {
            $delete->delete();
            return redirect(route('manage_user'))->with('danger', 'User \'' . $user->first_name . ' ' . $user->last_name . '\' deleted.');
        } else {
            return redirect(route('manage_user'))->with('warning', 'User not found.');
        }
    }

    public function delete_all_users()
    {
        $option = request()['option'];
        if (!in_array($option, [1,2,3] )) {
            return back()->with('warning', 'Invalid Input. No users deleted.');
        } 
        if ($option === 1) {
            User::where('user_type', 'STUDENT')->delete();
            User::where('user_type', 'SUPERVISOR')->delete();
            return redirect(route('manage_user'))->with('danger', 'All students and supervisors successfully deleted.');
        }
        if ($option === 2) {
            User::where('user_type', 'STUDENT')->delete();
            return redirect(route('manage_user'))->with('danger', 'All students successfully deleted.');
        }
        if ($option === 3) {
            User::where('user_type', 'SUPERVISOR')->delete();
            return redirect(route('manage_user'))->with('danger', 'All supervisors successfully deleted.');
        }
    }

    public function delete_topic()
    {
        $topic = $delete = Topic::find(request()['topic_id']);
        if ($delete) {
            $delete->delete();
            return redirect(route('manage_topic'))->with('danger', 'Topic \'' . $topic->topic_name . '\' deleted.');
        } else {
            return redirect(route('manage_topic'))->with('warning', 'Topic not found.');
        }
    }

    public function delete_all_topics()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Topic::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return redirect(route('manage_topic'))->with('danger', 'All topics successfully deleted.');
    }
}