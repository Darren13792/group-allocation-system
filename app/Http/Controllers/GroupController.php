<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Topic;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\GroupSupervisor;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendAllocation;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function manage_groups()
    {
        $preference_size = DB::table('settings')->first()->preference_size;
        $studentCounts = $this->get_student_counts($preference_size);
        $supervisorCounts = $this->get_supervisor_counts();
        $studentsNotInGroup = User::with('student_preferences.topic')->where('user_type', 'STUDENT')
        ->whereNotIn('id', function($query) {$query->select('student_id')->from('group_students');})->get();
        $supervisorsNotInGroup = User::with('supervisor_preferences.topic')->where('user_type', 'SUPERVISOR')
        ->whereNotIn('id', function($query) {$query->select('supervisor_id')->from('group_supervisors');})->get();
        return Inertia::render('Admin/ManageGroup', [
            'settings' => Setting::first(),
            'studentCounts' => $studentCounts,
            'supervisorCounts' => $supervisorCounts,
            'preference_size' => $preference_size,
            'studentsNotInGroup' => $studentsNotInGroup,
            'supervisorsNotInGroup' => $supervisorsNotInGroup,
            'groups' => Group::with('group_students.student.student_preferences.topic', 'group_supervisors.supervisor.supervisor_preferences.topic', 'topic')->orderBy('id')->get()->toArray(),
            'supervisorGroups' => Group::with('group_supervisors.supervisor.supervisor_preferences.topic', 'topic')->orderBy('id')->get()->toArray(),
        ]);
    }

    public function get_student_counts($preference_size)
    {
        $results = DB::table('student_preferences')
            ->join('group_students', 'student_preferences.student_id', '=', 'group_students.student_id')
            ->join('groups', 'group_students.group_id', '=', 'groups.id')
            ->select('student_preferences.student_id', 'student_preferences.topic_id', 'student_preferences.weight', 'groups.topic_id as assigned_topic_id')
            ->orderBy('student_preferences.student_id')
            ->orderBy('student_preferences.weight')
            ->get();

        $counts = array_fill(1, $preference_size, 0);
        $choice = [];

        foreach ($results as $result) {
            if ($result->topic_id == $result->assigned_topic_id) {
                $choice[$result->student_id] = $result->weight;
            }
        }
        foreach ($choice as $studentId => $weight) {
            $counts[$weight]++;
        }

        $noPreferenceCount = GroupStudent::count() - count($choice);
        return [
            'counts' => $counts,
            'none' => $noPreferenceCount
        ];
    }

    public function get_supervisor_counts()
    {
        $results = DB::table('supervisor_preferences')
            ->join('group_supervisors', 'supervisor_preferences.supervisor_id', '=', 'group_supervisors.supervisor_id')
            ->join('groups', 'group_supervisors.group_id', '=', 'groups.id')
            ->select('supervisor_preferences.supervisor_id', 'supervisor_preferences.topic_id', 'groups.topic_id as assigned_topic_id')
            ->orderBy('supervisor_preferences.supervisor_id')
            ->get();
        
        $supervisor_preference_match = [];

        foreach ($results as $result) {
            if (!isset($supervisor_preference_match[$result->supervisor_id])) {
                $supervisorAssignment = GroupSupervisor::where('supervisor_id', $result->supervisor_id)->count();
                $supervisor_preference_match[$result->supervisor_id] = [
                    'matches' => 0,
                    'assignments' => $supervisorAssignment
                ];
            }
            if ($result->topic_id == $result->assigned_topic_id) {
                $supervisor_preference_match[$result->supervisor_id]['matches']++;
            }
        }
        $noPreferenceCount = 0;
        $prefAssignmentCount = 0;
        foreach ($supervisor_preference_match as $supervisor_id => $data) {
            $prefAssignmentCount += $data['assignments'];
            if ($data['matches'] < $data['assignments']) {
                $noPreferenceCount += $data['assignments'] - $data['matches'];
            }
        }
        $allSupervisors = GroupSupervisor::all()->count();
    
        $preferenceSatisfiedCount = $allSupervisors - $noPreferenceCount - ($allSupervisors - $prefAssignmentCount);
        $noPreferenceCount += $allSupervisors - $prefAssignmentCount;
        return [
            'counts' => $preferenceSatisfiedCount,
            'none' => $noPreferenceCount
        ];
    }

    public function create_new_group()
    {
        $allStudents = User::where('user_type', 'STUDENT')->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        $allSupervisors = User::where('user_type', 'SUPERVISOR')->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        $studentsNotInGroup = User::where('user_type', 'STUDENT')->whereNotIn('id', function($query) {$query->select('student_id')->from('group_students');})->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        $supervisorsNotInGroup = User::where('user_type', 'SUPERVISOR')->whereNotIn('id', function($query) {$query->select('supervisor_id')->from('group_supervisors');})->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        return Inertia::render('Admin/CreateNewGroup', [
            'topics' => Topic::all(),
            'allStudents' => $allStudents,
            'studentsNotInGroup' => $studentsNotInGroup,
            'allSupervisors' => $allSupervisors,
            'supervisorsNotInGroup' => $supervisorsNotInGroup,
        ]);
    }

    public function process_new_group()
    {
        validator(request()->all(), [
            'group_topic' => [
                'required',
                Rule::exists('topics', 'id'),
            ],
            'students.*.student_id' => [
                'required',
                Rule::exists('users', 'id')->where('user_type', 'STUDENT'),
            ],
            'supervisors.*.supervisor_id' => [
                'required',
                Rule::exists('users', 'id')->where('user_type', 'SUPERVISOR'),
            ]
        ],
        [
            'group_topic.exists' => 'Topic with ID :input does not exist.',
            'students.*.student_id.required' => 'The student field is required.',
            'students.*.student_id.exists' => 'Student with ID :input does not exist.',
            'supervisors.*.supervisor_id.required' => 'The supervisor field is required.',
            'supervisors.*.supervisor_id.exists' => 'Supervisor with ID :input does not exist.',
        ])->validate();

        DB::beginTransaction();
        try {
            $group = new Group(['topic_id' => request()->group_topic]);
            $group->save();
            foreach (request('students') as $student) {
                if (isset($student['student_id'])) {
                    $groupStudent = GroupStudent::where('student_id', $student['student_id'])->first();
                    if ($groupStudent) {
                        $groupStudent->update([
                            'group_id' => $group->id
                        ]);
                    } else {
                        GroupStudent::create([
                            'group_id' => $group->id,
                            'student_id'=> $student['student_id'],
                        ]);
                    }
                }
            }
            $addedSupervisors = [];
            foreach (request('supervisors') as $supervisor) {
                if (isset($supervisor['supervisor_id']) && !in_array($supervisor['supervisor_id'], $addedSupervisors)) {
                    GroupSupervisor::create([
                        'group_id' => $group->id,
                        'supervisor_id'=> $supervisor['supervisor_id'],
                    ]);
                    $addedSupervisors[] = $supervisor['supervisor_id'];
                }
            }
            DB::commit();
            return redirect(route("manage_groups"))->with('success', 'Group \'' . $group->id . '\' successfully created.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('warning', $e->getMessage());
        }
    }

    public function edit_group($id)
    {
        $group = Group::with('group_students.student', 'group_supervisors.supervisor', 'topic')->find($id);
        if (!$group) {
            return redirect(route("manage_groups"))->with('warning', 'Group \'' . $id . '\' not found.');
        }
        $studentsInGroup = $group->group_students->pluck('student_id')->all();
        $allStudentsNotInGroup = User::where('user_type', 'STUDENT')->whereNotIn('id', $studentsInGroup)->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        $studentsNotInGroup = User::where('user_type', 'STUDENT')->whereNotIn('id', function($query) {$query->select('student_id')->from('group_students');})->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        $supervisorsInGroup = $group->group_supervisors->pluck('supervisor_id')->all();
        $allSupervisorsNotInGroup = User::where('user_type', 'SUPERVISOR')->whereNotIn('id', $supervisorsInGroup)->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        $supervisorsNotInGroup = User::where('user_type', 'SUPERVISOR')->whereNotIn('id', function($query) {$query->select('supervisor_id')->from('group_supervisors');})->orderBy('email', 'asc')->get(['id', 'first_name', 'last_name', 'email']);
        return Inertia::render('Admin/EditGroup', [
            'topics' => Topic::all(),
            'groups' => Group::with('topic')->orderBy('id')->get(),
            'group' => $group->toArray(),
            'allStudents' => $allStudentsNotInGroup,
            'studentsNotInGroup' => $studentsNotInGroup,
            'allSupervisors' => $allSupervisorsNotInGroup,
            'supervisorsNotInGroup' => $supervisorsNotInGroup,
        ]);
    }

    public function process_edit_group()
    {
        $formData = request()->all();
        validator($formData, [
            'group_topic' => [
                'required',
                Rule::exists('topics', 'id'),
            ],
            'students.*.student_id' => [
                'required',
                Rule::exists('users', 'id')->where('user_type', 'STUDENT'),
            ],
            'supervisors.*.supervisor_id' => [
                'required',
                Rule::exists('users', 'id')->where('user_type', 'SUPERVISOR'),
            ]
        ],
        [
            'group_topic.exists' => 'Topic with ID :input does not exist.',
            'students.*.student_id.required' => 'The student field is required.',
            'students.*.student_id.exists' => 'Student with ID :input does not exist.',
            'supervisors.*.supervisor_id.required' => 'The supervisor field is required.',
            'supervisors.*.supervisor_id.exists' => 'Supervisor with ID :input does not exist.',
        ])->validate();

        DB::beginTransaction();
        try {
            $updated = false;
            $group = Group::find($formData['group_id']);
            if ($group['topic_id'] !== $formData['group_topic']){
                $group->update([
                    'topic_id' => $formData['group_topic'],
                ]);
                $updated = true;
            }
    
            $filteredData = Arr::except($formData, ['group_id', 'group_topic', 'students', 'supervisors']);
            foreach ($filteredData as $userId => $groupId) {
                if ($groupId !== $formData['group_id']){
                    $groupStudent = GroupStudent::where(['student_id' => $userId, 'group_id' => $formData['group_id']])->first();
                    $groupSupervisor = GroupSupervisor::where(['supervisor_id' => $userId, 'group_id' => $formData['group_id']])->first();
                    if ($groupStudent) {
                        $groupStudent->update([
                            'group_id' => $groupId
                        ]);
                    } else if ($groupSupervisor) {
                        $existingGroupSupervisor = GroupSupervisor::where('group_id', $groupId)->where('supervisor_id', $groupSupervisor->supervisor_id)->first();
                        if ($existingGroupSupervisor) {
                            $name = User::where('id', $groupSupervisor->supervisor_id)->selectRaw("CONCAT(first_name, ' ', last_name) as full_name")->value('full_name');
                            throw new \Exception('Supervisor \'' . $name . '\' is already assigned group ' . $groupId . '.');
                        }
                        $groupSupervisor->update([
                            'group_id' => $groupId
                        ]);
                    }
                    $updated = true;
                }
            }
            foreach (request('students') as $student) {
                if (isset($student['student_id'])) {
                    $groupStudent = GroupStudent::where('student_id', $student['student_id'])->first();
                    if ($groupStudent) {
                        $groupStudent->update([
                            'group_id' => $formData['group_id']
                        ]);
                    } else {
                        GroupStudent::create([
                            'group_id' => $formData['group_id'],
                            'student_id'=> $student['student_id'],
                        ]);
                    }
                    $updated = true;
                }
            }
            $addedSupervisors = [];
            foreach (request('supervisors') as $supervisor) {
                if (isset($supervisor['supervisor_id']) && !in_array($supervisor['supervisor_id'], $addedSupervisors)) {
                    GroupSupervisor::create([
                        'group_id' => $formData['group_id'],
                        'supervisor_id'=> $supervisor['supervisor_id'],
                    ]);
                    $addedSupervisors[] = $supervisor['supervisor_id'];
                    $updated = true;
                }
            }
            if ($updated) {
                DB::commit();
                return redirect(route("manage_groups"))->with('success', 'Group \'' . $formData['group_id'] . '\' successfully updated.');
            }
            else {
                DB::rollBack();
                return back()->with('warning', 'Group \'' . $formData['group_id'] . '\' not updated. No fields were changed.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('warning', $e->getMessage());
        }
    }

    public function remove_group_user()
    {
        $name = request()['user_name'];
        $id = request()['user_id'];
        $groupId = request()['group_id'];
        $groupStudent = GroupStudent::where(['student_id' => $id, 'group_id' => $groupId])->first();
        $groupSupervisor = GroupSupervisor::where(['supervisor_id' => $id, 'group_id' => $groupId])->first();
        if ($groupStudent) {
            $groupStudent->delete();
            return back()->with('danger', 'User \'' . $name . '\' removed from group ' . $groupId . '.');
        } else if ($groupSupervisor) {
            $groupSupervisor->delete();
            return back()->with('danger', 'User \'' . $name . '\' removed from group ' . $groupId . '.');
        } else {
            return back()->with('warning', 'User \'' . $name . '\' is not in any group.');
        }
    }

    public function move_group()
    {
        $updated = false;
        $name = request()['user_name'];
        $userType = request()['user_type'];
        if ($userType === "STUDENT") {
            if (empty(request()['group_id'])) {
                return redirect(route("manage_groups"))->with('warning', 'Student \'' . $name . '\' not updated. No topic selected.');
            }
            if (isset(request()['user_id'])) {
                $oldGroup = GroupStudent::where(['student_id' => request()['user_id'], 'group_id' => request()['old_group_id']])->first();
                if (request()['old_group_id'] != request()['group_id']) {
                    if ($oldGroup) {
                        $oldGroup->update([
                            'group_id' => request()['group_id'],
                        ]);
                    } else {
                        GroupStudent::create([
                            'group_id' => request()['group_id'],
                            'student_id' => request()['user_id'],
                        ]);
                    }
                    $updated = true;
                }
            }
            if ($updated) {
                return back()->with('success', 'Student \'' . $name . '\' successfully moved to group ' . request()['group_id'] . '.');
            } else {
                return back()->with('warning', 'Student \'' . $name . '\' not updated. No fields were changed.');
            }
        } else {
            if (empty(request()['group_id'])|| !is_numeric(request()['group_id'])) {
                return back()->with('warning', 'Supervisor \'' . $name . '\' not updated. No group selected.');
            }
            if (isset(request()['user_id'])) {
                $oldGroup = GroupSupervisor::where(['supervisor_id' => request()['user_id'], 'group_id' => request()['old_group_id']])->first();
                if (request()['old_group_id'] != request()['group_id']) {
                    $existingGroupSupervisor = GroupSupervisor::where('group_id', request()['group_id'])->where('supervisor_id', request()['user_id'])->first();
                    if ($existingGroupSupervisor) {
                        return back()->with('warning', 'Supervisor \'' . $name . '\' is already assigned group ' . request()['group_id'] . '.');
                    }
                    if ($oldGroup) {
                        $oldGroup->update([
                            'group_id' => request()['group_id'],
                        ]);
                    } else {
                        GroupSupervisor::create([
                            'group_id' => request()['group_id'],
                            'supervisor_id' => request()['user_id'],
                        ]);
                    }
                    $updated = true;
                }
            }
            if ($updated) {
                return back()->with('success', 'Supervisor \'' . $name . '\' successfully moved to group ' . request()['group_id'] . '.');
            } else {
                return back()->with('warning', 'Supervisor \'' . $name . '\' not updated. No fields were changed.');
            }
        }
    }

    public function delete_group()
    {
        $group = $delete = Group::find(request()['group_id']);
        if ($delete) {
            $delete->delete();
            return back()->with('danger', 'Group \'' . $group->id . '\' successfully deleted.');
        } else {
            return back()->with('warning', 'Group not found.');
        }
    }

    public function delete_all_groups()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        GroupStudent::truncate();
        GroupSupervisor::truncate();
        Group::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        return back()->with('danger', 'All groups successfully deleted.');
    }

    public function process_allocation()
    {
        $check = request('ideal_size') === null;
        $rules = [
            'min_group_size' => 'required|integer|min:1',
            'max_group_size' => 'required|integer|min:1|gte:min_group_size',
            'max_groups_per_topic' => 'required|integer|min:1',
            // Exclude 'none' from validation
            'ideal_size' => $check ? 'nullable' : 'required|integer|min:1|gte:min_group_size|lte:max_group_size',
        ];
        $messages = [
            'max_group_size.gte' => 'The maximum group size must be greater than or equal to the minimum group size.',
            'max_groups_per_topic.min' => 'The maximum number of groups per topic must be at least 1.',
            'ideal_size.gte' => 'The ideal size must be greater than or equal to the minimum group size.',
            'ideal_size.lte' => 'The ideal size must be less than or equal to the maximum group size.',
        ];
        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $messages = $errors->all();
            $errorMessage = implode(' ', $messages);
            return back()->with('danger', $errorMessage);
        }
        $minGroupSize = request('min_group_size');
        $maxGroupSize = request('max_group_size');
        $maxGroupsPerTopic = request('max_groups_per_topic');
        $idealSize = request('ideal_size') === null ? 'None' : request('ideal_size');
        $studentCount = User::where('user_type', 'STUDENT')->count();
        $topicCount = Topic::count();

        $minGroupsNeeded = ceil($studentCount / $maxGroupSize);
        $maxGroupsPossible = floor($studentCount / $minGroupSize);
        if ($minGroupsNeeded > $maxGroupsPossible) {
            return back()->with('danger', 'Group sizes are not feasible with the total number of students.');
        }
        if ($maxGroupsPerTopic * $topicCount < $minGroupsNeeded) {
            return back()->with('danger', 'Not enough groups to allocate every student. Consider increasing the max group size or the max groups per topic.');
        }

        $topics = Topic::all();
        try {
            $studentAllocation = $this->get_student_allocation($minGroupSize, $maxGroupSize, $maxGroupsPerTopic, $idealSize, $topics);
            if ($studentAllocation) {
                try {
                    // Add to database
                    $this->handle_group_allocations($studentAllocation, 'student');
                } catch (\Exception $e) {
                    return back()->with('danger', $e->getMessage());
                }
            } else {
                return back()->with('danger', 'No solution found with given inputs. Try increasing the group size range');
            }
        } catch (\Exception $e) {
            return back()->with('danger', $e->getMessage());
        }

        try {
            $supervisorAllocation = $this->get_supervisor_allocation();
            if ($supervisorAllocation) {
                try {
                    // Add to database
                    $this->handle_group_allocations($supervisorAllocation, 'supervisor');
                } catch (\Exception $e) {
                    return back()->with('danger', $e->getMessage());
                }
            } else {
                return back()->with('danger', 'Supervisor allocation failed. No solution found.');
            }
        } catch (\Exception $e) {
            return back()->with('danger', $e->getMessage());
        }
        return redirect(route('manage_groups'))->with('success', 'Group allocation successful');
    }

    public function get_student_allocation($minGroupSize, $maxGroupSize, $maxGroupsPerTopic, $idealSize, $topics)
    {
        $students = User::where('user_type', 'STUDENT')->get();
        $studentMatrix = [];
        $assignments = [];
        $studentIndexToId = [];
        $topicIndexToId = [];
        
        foreach ($students as $index => $student) {
            $studentIndexToId[$index] = $student->id; // Map matrix row index to student ID
        }
        foreach ($topics as $index => $topic) {
            $topicIndexToId[$index] = $topic->id; // Map matrix column index to topic ID
        }
        foreach ($students as $student) {
            $row = [];
            foreach ($topics as $topic) {
                $preference = $student->student_preferences()->where('topic_id', $topic->id)->first();
                $row[] = $preference ? $preference->weight : 0; // Append the weight (or 0) to the row
            }
            $studentMatrix[] = $row; // Append row to the student matrix
        }

        $file = realpath(str_replace('\\', '/', __DIR__) . '/../../Scripts/student_assignment.py');
        if(!$file) {
            throw new \Exception('student_assignment file missing from directory');
        }
        $studentMatrixString = escapeshellarg(json_encode($studentMatrix));
        $studentIdString = escapeshellarg(json_encode($studentIndexToId));
        $topicIdString = escapeshellarg(json_encode($topicIndexToId));
    
        $command = "python {$file} {$studentMatrixString} $minGroupSize $maxGroupSize $maxGroupsPerTopic $idealSize {$studentIdString} {$topicIdString}";
        $result = shell_exec($command);
        return json_decode($result, true); // Return as PHP array
    }

    public function get_supervisor_allocation()
    {
        $supervisors = User::where('user_type', 'SUPERVISOR')->get();
        $groups = Group::all();
        $supervisorMatrix = [];
        $assignments = [];
        $groupIndexToId = [];
        $supervisorIndexToId = [];
        
        foreach ($groups as $index => $group) {
            $groupIndexToId[$index] = $group->id; // Map matrix column index to group ID
        }
        foreach ($supervisors as $index => $supervisor) {
            $supervisorIndexToId[$index] = $supervisor->id; // Map matrix row index to supervisor ID
        }
        foreach ($supervisors as $supervisor) {
            $row = [];
            foreach ($groups as $group) {
                $preference = $supervisor->supervisor_preferences()->where('topic_id', $group->topic_id)->first();
                $row[] = $preference ? 1 : 0; // Append 1 (or 0) to the row
            }
            $supervisorMatrix[] = $row; // Append row to supervisor matrix
        }
        
        $file = realpath(str_replace('\\', '/', __DIR__) . '/../../Scripts/supervisor_assignment.py');
        if(!$file) {
            throw new \Exception('supervisor_assignment file missing from directory');
        }
        $supervisorMatrixString = escapeshellarg(json_encode($supervisorMatrix));
        $supervisorIdString = escapeshellarg(json_encode($supervisorIndexToId));
        $groupIdString = escapeshellarg(json_encode($groupIndexToId));
        
        $command = "python {$file} {$supervisorMatrixString} {$supervisorIdString} {$groupIdString}";
        $result = shell_exec($command);
        return json_decode($result, true); // Return as PHP array
    }
    
    public function handle_group_allocations($allocations, $type)
    {
        DB::beginTransaction();
        try {
            // Disable foreign key checks and truncate appropriate tables
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            if ($type === 'student') {
                GroupStudent::query()->delete();
                GroupSupervisor::query()->delete();
                Group::query()->delete();
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            // DB::table('groups')->insert(['invalid_column' => 'value']);
            // Process allocations based on user type
            if ($type === 'student') {
                foreach ($allocations as $topicId => $groups) {
                    foreach ($groups as $groupNumber => $studentIds) {
                        $group = new Group(['topic_id' => $topicId]);
                        $group->save(); // Save new group to get ID
                        foreach ($studentIds as $studentId) {
                            $groupStudent = new GroupStudent([
                                'group_id' => $group->id,
                                'student_id' => $studentId,
                            ]);
                            $groupStudent->save();
                        }
                    }
                }
            } else if ($type === 'supervisor') {
                foreach ($allocations as $groupId => $supervisors) {
                    foreach ($supervisors as $supervisorId) {
                        $groupSupervisor = new GroupSupervisor([
                            'group_id' => $groupId,
                            'supervisor_id' => $supervisorId,
                        ]);
                        $groupSupervisor->save();
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw new \Exception('An error occurred: ' . $e->getMessage());
        }
    }

    public function send_allocation()
    {
        if (Setting::first()->status !== 'approved') {
            return back()->with('warning', 'Please approve the allocation before dispatching emails.');
        }
        $allUsers = User::where('user_type', '!=', 'ADMIN')
            ->with([
                'group_students.group.group_students.student', 
                'group_students.group.group_supervisors.supervisor', 
                'group_students.group.topic',
                'group_supervisors.group.group_students.student', 
                'group_supervisors.group.group_supervisors.supervisor', 
                'group_supervisors.group.topic'
            ])->get();
            
        foreach ($allUsers as $user) {
            if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $name = $user->first_name . " " . $user->last_name;
            $userType = $user->user_type;
            $groups = [];
            if ($userType == "STUDENT" && $user->group_students) {
                $groups[] = $user->group_students->group;
            } else if ($userType == "SUPERVISOR") {
                $groups = $user->group_supervisors->map(function ($groupSupervisor) {
                    return $groupSupervisor->group;
                })->unique('id')->all();
            }
            if (!empty($groups)) {
                // Currently set to platform email to prevent spam. Use commented line for proper functionality.

                // Mail::to($user->email)->queue(new SendAllocation($name, $groups));
                Mail::to('yourname@example.com')->queue(new SendAllocation($name, $groups));
            }

        }
        return redirect(route('manage_groups'))->with('success', 'Allocation Emails dispatched');
    }
}
