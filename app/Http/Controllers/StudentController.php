<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Topic;
use App\Models\StudentPreference;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index() 
    {
        return Inertia::render('Student/StudentDashboard', [
            'deadline' => Setting::first()->deadline,
        ]);
    }

    public function preference()
    {
        $user = User::with('student_preferences')->find(auth()->id())->toArray();
        $preference_size = Setting::first()->preference_size;
        return Inertia::render('Student/StudentPreferences', [
            'user' => $user,
            'topics' => Topic::all(),
            'preference_size' => $preference_size,
        ]);
    }

    public function process_preference()
    {
        $status = Setting::first()->status;
        if ( $status === 'notstarted') {
            return back()->with('warning', 'Submissions are currently closed. Please wait until the admin grants permission.');
        }
        if ($status === 'ended' || $status === 'approved') {
            return back()->with('danger', 'Deadline date passed. Submissions are closed.');
        }
        $preference_size = Setting::first()->preference_size;
        $preference_validation = [];
        $newPreferences = [];
        $weight = 0;
        $topicIds = Topic::pluck('id')->all();
        $studentPreferences = StudentPreference::where('student_id', auth()->id())->get();

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
                        'student_id' => auth()->id(),
                        'topic_id' => $topicValue,
                        'weight' => ++$weight,
                    ]);
                }
            }
            return redirect(route('user_dashboard'))->with('success', 'Preferences successfully submitted.');
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
                return redirect(route("user_dashboard"))->with('success', 'Preferences successfully updated.');
            }
            else {
                return back()->with('warning', 'Preferences not updated. No fields were changed.');
            }
        }
    }
}
