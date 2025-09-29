<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;
use App\Models\User;
use App\Models\StudentPreference;
use App\Models\SupervisorPreference;

class CSVController extends Controller
{
    public function process_topic_csv()
    {
        validator(request()->all(), [
            'file' => 'required',
        ])->validate();

        $CSVfile = fopen(request()->file('file'), 'r');
        $header = fgetcsv($CSVfile, null, ','); // stores current headers
        $expectedHeaders = ['topic_name','description','due_date'];
        
        if ($header !== $expectedHeaders) {
            return redirect()->back()->withErrors(['file' => 'Invalid CSV file uploaded, Please ensure your CSV header\'s are in the following order: <i>' . implode("; ", $expectedHeaders) . '</i>.']);
        }

        $numInserted = 0; // store number of records inserted into the db
        $numUpdated = 0; // store number of records updated in the db
        $numFailed = 0; // store number of failed imports
        $numSame = 0; // store number of duplicate users
        while(!feof($CSVfile)) {
            $line = fgetcsv($CSVfile, null, ',');

            // ignore blank lines
            if(($line[0] ?? null) === null) {
                continue;
            }

            if (count($line) !== 3) {
                $numFailed++;
                continue;
            }
            
            $topicData = [
                'topic_name' => $line[0],
                'description' => $line[1],
                'due_date' => $line[2],
            ];

            // Check if topic_name already exists
            $existingTopic = Topic::where('topic_name', $topicData['topic_name'])->first();
            if ($existingTopic) {
                // Check if other fields are different
                if ($existingTopic->description !== $topicData['description'] ||
                    $existingTopic->due_date !== $topicData['due_date']) {
                    // Update the existing topic
                    $existingTopic->update($topicData);
                    $numUpdated++;
                } else {
                    $numSame++;
                }
            } else {
                // Add topic to database
                $create = Topic::create($topicData);
                if ($create) {
                    $numInserted++;
                }
            }
        }

        fclose($CSVfile);

        // Display import successful message
        if ($numInserted > 0 || $numUpdated > 0) {
            $successMessage = '';
            if ($numInserted > 0) {
                $successMessage .= $numInserted . ' ' . ($numInserted == 1 ? 'topic was' : 'topics were') . ' successfully imported.';
            }
            if ($numUpdated > 0) {
                $successMessage .= ' ' . $numUpdated . ' ' . ($numUpdated == 1 ? 'topic was' : 'topics were') . ' updated.';
            }
            if ($numFailed > 0) {
                $successMessage .= ' ' . $numFailed . ' ' . ($numFailed == 1 ? 'topic was' : 'topics were') . ' not imported due to error in input.';
            }
            if ($numSame > 0) {
                $successMessage .= ' ' . $numSame . ' ' . ($numSame == 1 ? 'topic was' : 'topics were') . ' not imported (already present in the database with the same data).';
            }
            return redirect(route("manage_topic"))->with('success', $successMessage);
        } else {
            $errorMessage = '';
            if ($numSame > 0) {
                $errorMessage .= $numSame . ' ' . ($numSame == 1 ? 'topic was' : 'topics were') . ' not imported (already present in the database with the same data).';
            }
            if ($numFailed > 0) {
                $errorMessage .= ' ' . $numFailed . ' ' . ($numFailed == 1 ? 'topic was' : 'topics were') . ' not imported due to error in input.';
            }
            return redirect(route("create_new_topic"))->with('warning', $errorMessage);
        }
    }

    public function process_user_csv()
    {
        validator(request()->all(), [
            'file' => 'required',
        ])->validate();

        $CSVfile = fopen(request()->file('file'), 'r');
        $header = fgetcsv($CSVfile, null, ','); // stores current headers
        $expectedHeaders = ['first_name','last_name','email','user_type'];
        
        if ($header !== $expectedHeaders) {
            return redirect()->back()->withErrors(['file' => 'Invalid CSV file uploaded, Please ensure your CSV header\'s are in the following order: <i>' . implode("; ", $expectedHeaders) . '</i>.']);
        }

        $numInserted = 0; // store number of records inserted into the db
        $numUpdated = 0; // store number of records updated in the db
        $numFailed = 0; // store number of failed imports
        $numSame = 0; // store number of duplicate users
        while(!feof($CSVfile)) {
            $line = fgetcsv($CSVfile, null, ',');

            // ignore blank lines
            if(($line[0] ?? null) === null) {
                continue;
            }

            if (count($line) !== 4) {
                $numFailed++;
                continue;
            }
        
            $userData = [
                'first_name' => trim($line[0]),
                'last_name' => trim($line[1]),
                'email' => trim(strtolower($line[2])),
                'user_type' => trim(strtoupper($line[3]))
            ];

            // Check if user type is valid
            if (!in_array($userData['user_type'], ['ADMIN', 'SUPERVISOR', 'STUDENT'])) {
                $numFailed++;
                continue;
            }
            // Check if email already exists
            $existingUser = User::where('email', $userData['email'])->first();
            if ($existingUser) {
                // Check if other fields are different
                if ($existingUser->first_name !== $userData['first_name'] ||
                    $existingUser->last_name !== $userData['last_name'] ||
                    $existingUser->user_type !== $userData['user_type']) {
                        // Remove preferences if they exist
                        if ($existingUser->user_type !== $userData['user_type'] && $existingUser->user_type == 'STUDENT') {
                            $studentPreferences = StudentPreference::where('student_id', $existingUser->id)->get();
                            foreach ($studentPreferences as $preference) {
                                $preference->delete();
                            }
                        }
                        else if ($existingUser->user_type !== $userData['user_type'] && $existingUser->user_type == 'SUPERVISOR') {
                            $supervisorPreferences = SupervisorPreference::where('supervisor_id', $existingUser->id)->get();
                            foreach ($supervisorPreferences as $preference) {
                                $preference->delete();
                            }
                        }
                        // Update the existing user
                        $existingUser->update($userData);
                        $numUpdated++;
                } else {
                    $numSame++;
                }
            } else {
                // Add user to database
                $create = User::create($userData);
                if ($create) {
                    $numInserted++;
                }
            }
        }

        fclose($CSVfile);

        // Display import successful message
        if ($numInserted > 0 || $numUpdated > 0) {
            $successMessage = '';
            if ($numInserted > 0) {
                $successMessage .= $numInserted . ' ' . ($numInserted == 1 ? 'user was' : 'users were') . ' successfully imported.';
            }
            if ($numUpdated > 0) {
                $successMessage .= ' ' . $numUpdated . ' ' . ($numUpdated == 1 ? 'user was' : 'users were') . ' updated.';
            }
            if ($numFailed > 0) {
                $successMessage .= ' ' . $numFailed . ' ' . ($numFailed == 1 ? 'user was' : 'users were') . ' not imported due to error in input.';
            }
            if ($numSame > 0) {
                $successMessage .= ' ' . $numSame . ' ' . ($numSame == 1 ? 'user was' : 'users were') . ' not imported (already present in the database with the same data).';
            }
            return redirect(route("manage_user"))->with('success', $successMessage);
        } else {
            $errorMessage = '';
            if ($numSame > 0) {
                $errorMessage .= $numSame . ' ' . ($numSame == 1 ? 'user was' : 'users were') . ' not imported (already present in the database with the same data).';
            }
            if ($numFailed > 0) {
                $errorMessage .= ' ' . $numFailed . ' ' . ($numFailed == 1 ? 'user was' : 'users were') . ' not imported due to error in input.';
            }
            return redirect(route("create_new_user"))->with('warning', $errorMessage);
        }
    }
}