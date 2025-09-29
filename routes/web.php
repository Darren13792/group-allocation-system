<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CSVController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SharedController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TestController;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LoginController::class, 'index'])->name('login');
Route::inertia('forgot-password', 'Login/ForgotPassword')->name('forgot_password');
Route::post('/process-login', [LoginController::class, 'login'])->name('process_login');
Route::post('request-password', [LoginController::class, 'request_password'])->name('request_password');
Route::inertia('request-password-success', 'Login/ForgotPasswordSuccess')->name('forgot_password_success');
Route::get('/reset-password/{token}', function ($token) {
    return Inertia::render('Login/PasswordReset', ['token' => $token]);
})->name('password.reset');
Route::post('reset-password', [LoginController::class, 'reset_password'])->name('reset_password');
Route::post('process-first-time-login', [LoginController::class, 'process_first_time_login'])->name('process_first_time_login');

Route::middleware('auth')->group(function() {
    // ADMIN+SUPERVISOR+STUDENT Pages
    Route::get('/home', function() {
        if(auth()->user()->user_type === 'ADMIN') {
            return redirect(route('admin_dashboard'));
        } else {
            return redirect(route('user_dashboard'));
        } 
    })->name('home');

    Route::get('/first-time-login', function() {
        if(auth()->user()->activation_status === 0) {
            return inertia('Login/FirstTimeLogin', []);
        } else {
            return redirect(route('home'));
        } 
    })->name('first_time_login');

    Route::get('/logout', function() {
        return redirect()->back();
    })->name('get_logout');

    Route::post('/logout', function() {
        auth()->logout();
        return redirect('/');
    })->name('logout');

    // ADMIN Pages
    Route::middleware('user_type:ADMIN')->group(function() {
        Route::get('/admin-dashboard', [AdminController::class, 'index'])->name('admin_dashboard');
        Route::get('/manage-user', [AdminController::class, 'manage_user'])->name('manage_user');
        Route::inertia('/create-new-user', 'Admin/CreateNewUser')->name('create_new_user');
        Route::post('/process-new-user', [AdminController::class, 'process_new_user'])->name('process_new_user');
        Route::get('/edit-user/{id}', [AdminController::class, 'edit_user'])->name('edit_user');
        Route::post('/process-edit-user', [AdminController::class, 'process_edit_user'])->name('process_edit_user');
        Route::post('/process-edit-preference', [AdminController::class, 'process_edit_preference'])->name('process_edit_preference');
        Route::post('/process-edit-availability', [AdminController::class, 'process_edit_availability'])->name('process_edit_availability');
        Route::post('/process-user-csv', [CSVController::class, 'process_user_csv'])->name('process_user_csv');
        Route::post('/delete-user', [AdminController::class, 'delete_user'])->name('delete_user');
        Route::post('/delete-all-users', [AdminController::class, 'delete_all_users'])->name('delete_all_users');
        Route::post('/delete-preferences', [AdminController::class, 'delete_preferences'])->name('delete_preferences');
        Route::post('/reset-availability', [AdminController::class, 'reset_availability'])->name('reset_availability');

        Route::get('/manage-topic', [AdminController::class, 'manage_topic'])->name('manage_topic');
        Route::inertia('/create-new-topic', 'Admin/CreateNewTopic')->name('create_new_topic');
        Route::post('/process-new-topic', [AdminController::class, 'process_new_topic'])->name('process_new_topic');
        Route::get('/edit-topic/{id}', [AdminController::class, 'edit_topic'])->name('edit_topic');
        Route::post('/process-edit-topic', [AdminController::class, 'process_edit_topic'])->name('process_edit_topic');
        Route::post('/delete-topic', [AdminController::class, 'delete_topic'])->name('delete_topic');
        Route::post('/delete-all-topics', [AdminController::class, 'delete_all_topics'])->name('delete_all_topics');
        Route::post('/process-topic-csv', [CSVController::class, 'process_topic_csv'])->name('process_topic_csv');

        Route::get('/manage-groups', [GroupController::class, 'manage_groups'])->name('manage_groups');
        Route::get('/create-new-group', [GroupController::class, 'create_new_group'])->name('create_new_group');
        Route::post('/process-new-group', [GroupController::class, 'process_new_group'])->name('process_new_group');
        Route::get('/edit-group/{id}', [GroupController::class, 'edit_group'])->name('edit_group');
        Route::post('/process-edit-group', [GroupController::class, 'process_edit_group'])->name('process_edit_group');
        Route::post('/delete-group', [GroupController::class, 'delete_group'])->name('delete_group');
        Route::post('/delete-all-groups', [GroupController::class, 'delete_all_groups'])->name('delete_all_groups');
        Route::post('/move-group', [GroupController::class, 'move_group'])->name('move_group');
        Route::post('/remove-group-user', [GroupController::class, 'remove_group_user'])->name('remove_group_user');
        Route::post('/process-allocation', [GroupController::class, 'process_allocation'])->name('process_allocation');
        Route::post('/send-allocation', [GroupController::class, 'send_allocation'])->name('send_allocation');
        
        Route::get('/settings', [SettingController::class, 'settings'])->name('settings');
        Route::post('/process-settings', [SettingController::class, 'process_settings'])->name('process_settings');
        Route::post('/update-status', [SettingController::class, 'update_status'])->name('update_status');

        // For testing
        Route::get('/generate-preferences', [TestController::class, 'generate_preferences'])->name('generate_preferences');
        Route::get('/test-send-allocation', [TestController::class, 'test_send_allocation'])->name('test_send_allocation');
    });
    
    // Supervisor Pages
    Route::middleware('user_type:SUPERVISOR')->group(function() {
        Route::get('/supervisor-availability', [SupervisorController::class, 'preference'])->name('supervisor_preferences');
        Route::post('/process-supervisor-availability', [SupervisorController::class, 'process_supervisor_preference'])->name('process_supervisor_preference');
    });
    
    // Student Pages
    Route::middleware('user_type:STUDENT')->group(function() {
        Route::get('/student-preference', [StudentController::class, 'preference'])->name('student_preferences');
        Route::post('/process-student-preference', [StudentController::class, 'process_preference'])->name('process_student_preference');
    });
    
    // Student and Supervisor Pages
    Route::middleware('user_type:STUDENT,SUPERVISOR')->group(function() {
        Route::get('/user-dashboard', [SharedController::class, 'index'])->name('user_dashboard');
        Route::get('/view-topics', [SharedController::class, 'view_topics'])->name('view_topics');
        Route::get('/view-group', [SharedController::class, 'view_group'])->name('view_group');
    });
});
