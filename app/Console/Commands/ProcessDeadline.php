<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Validator;
use App\Models\Setting;
use App\Models\User;
use App\Models\Topic;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\GroupSupervisor;
use DB;

class ProcessDeadline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status and run allocation algorithm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $timestamp = now()->format('Y-m-d H:i');
        $this->info("[$timestamp] Starting group allocation process...");
        $admin = User::where('user_type', 'ADMIN')->first();
        auth()->login($admin);

        $settings = Setting::first();
        if (now() < $settings->deadline) {
            $this->error("[$timestamp] Deadline has not passed yet");
            return;
        } 
        if (in_array($settings->status, ['ended', 'approved'])) {
            $this->error("[$timestamp] Process has already been run");
            return;
        }
        $check = $settings['ideal_size'] === null;
        $rules = [
            'min_group_size' => 'required|integer|min:1',
            'max_group_size' => 'required|integer|min:1|gte:min_group_size',
            'max_groups_per_topic' => 'required|integer|min:1',
            'ideal_size' => $check ? 'nullable' : 'required|integer|min:1|gte:min_group_size|lte:max_group_size',
        ];

        $messages = [
            'max_group_size.gte' => 'The maximum group size must be greater than or equal to the minimum group size.',
            'max_groups_per_topic.min' => 'The maximum number of groups per topic must be at least 1.',
            'ideal_size.gte' => 'The ideal size must be greater than or equal to the minimum group size.',
            'ideal_size.lte' => 'The ideal size must be less than or equal to the maximum group size.',
        ];

        $data = [
            'min_group_size' => $settings['min_group_size'],
            'max_group_size' => $settings['max_group_size'],
            'max_groups_per_topic' => $settings['max_groups_per_topic'],
            'ideal_size' => $settings['ideal_size'],
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return;
        }

        $data['ideal_size'] = $data['ideal_size'] === null ? 'None' : $data['ideal_size'];

        $studentCount = User::where('user_type', 'STUDENT')->count();
        $topicCount = Topic::count();

        $minGroupsNeeded = ceil($studentCount / $data['max_group_size']);
        $maxGroupsPossible = floor($studentCount / $data['min_group_size']);

        if ($minGroupsNeeded > $maxGroupsPossible) {
            $this->error("[$timestamp] Group sizes are not feasible with the total number of students.");
            return;
        }

        if ($data['max_groups_per_topic'] * $topicCount < $minGroupsNeeded) {
            $this->error("[$timestamp] Not enough groups to allocate every student. Increase the max groups per topic.");
            return;
        }

        $topics = Topic::all();

        $this->info("[$timestamp] Forming groups...");
        try {
            $studentAllocation = $this->get_student_allocation($data['min_group_size'], $data['max_group_size'], $data['max_groups_per_topic'], $data['ideal_size'], $topics);
            if ($studentAllocation) {
                $this->info("[$timestamp] Student allocation formed");
                $this->handle_group_allocations($studentAllocation, 'student');
                $this->info("[$timestamp] Students successfully allocated");
            } else {
                $this->error("[$timestamp] No solution found with given inputs. Try increasing the group size range");
                return;
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] " . $e->getMessage());
            return;
        }

        try {
            $supervisorAllocation = $this->get_supervisor_allocation();
            if ($supervisorAllocation) {
                $this->info("[$timestamp] Supervisor allocation formed");
                $this->handle_group_allocations($supervisorAllocation, 'supervisor');
                $this->info("[$timestamp] Supervisors successfully allocated");
            } else {
                $this->error("[$timestamp] Supervisor allocation failed. No solution found.");
                return;
            }
        } catch (\Exception $e) {
            $this->error("[$timestamp] " . $e->getMessage());
            return;
        }
        $settings->update(['status' => 'ended']);
        $this->info("[$timestamp] Status updated => Ended");
        $this->info("[$timestamp] Group allocation successful");
    }

    public function get_student_allocation($minGroupSize, $maxGroupSize, $maxGroupsPerTopic, $idealSize, $topics)
    {
        $students = User::where('user_type', 'STUDENT')->get();
        $studentMatrix = [];
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
            // DB::table('groups')->insert(['invalid_column' => 'value']); // Test Error
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



}
