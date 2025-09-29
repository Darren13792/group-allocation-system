<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use App\Models\Setting;

class SettingController extends Controller
{
    public function settings()
    {
        return Inertia::render('Admin/Settings', [
            'settings' => Setting::first(),
        ]);
    }

    public function process_settings()
    {
        validator(request()->all(), [
            'status' => 'in:notstarted,started,ended,visible',
            'deadline' => 'nullable|date|after_or_equal:now',
        ],
        )->validate();
        $rules = [
            'min_group_size' => 'required|integer|min:1',
            'max_group_size' => 'required|integer|min:1|gte:min_group_size',
            'max_groups_per_topic' => 'required|integer|min:1',
            'preference_size' => 'required|integer|min:1',
            'ideal_size' => 'nullable|integer|min:1|gte:min_group_size|lte:max_group_size',
        ];

        $messages = [
            'max_group_size.gte' => 'The maximum group size must be greater than or equal to the minimum group size.',
            'max_groups_per_topic.min' => 'The maximum number of groups per topic must be at least 1.',
            'ideal_size.gte' => 'The ideal size must be between the minimum and maximum group size',
            'ideal_size.lte' => 'The ideal size must be between the minimum and maximum group size',
        ];

        $validator = Validator::make(request()->all(), $rules, $messages);
        
        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = $errors->all();
            $errorMessage = implode(' ', $messages);
            return back()->with('danger', $errorMessage);
        }

        $settings = Setting::first();
        $settings->update([
            'status' => request()['status'],
            'deadline' => request()['deadline'],
            'min_group_size' => request()['min_group_size'],
            'max_group_size' => request()['max_group_size'],
            'max_groups_per_topic' => request()['max_groups_per_topic'],
            'preference_size' => request()['preference_size'],
            'ideal_size' => request()['ideal_size'],
        ]);

        if ($settings->wasChanged()) {
            return redirect(route('admin_dashboard'))->with('success', 'Settings successfully updated.');
        }
        else {
            return back()->with('warning', 'Settings not updated. No fields were changed.');
        }
    }

    public function update_status()
    {
        validator(request()->all(), [
            'status' => 'required:in:notstarted,started,ended,visible',
        ],
        )->validate();
        $settings = Setting::first();
        $settings->update(['status' => request()['status']]);
        if ($settings->wasChanged()) {
            return redirect(route('admin_dashboard'))->with('success', 'Status successfully updated.');
        }
        else {
            return back()->with('warning', 'Status not updated. No changes were made.');
        }
    }
}
