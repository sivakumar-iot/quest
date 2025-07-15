<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\TopicController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\Api\ModuleTopicApiController;
use App\Http\Controllers\TestAttemptController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // return view('dashboard');
    $user = Auth::user();

    // return view('dashboard');


    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->isStaff()) {
        return redirect()->route('staff.dashboard');
    } elseif ($user->isStudent()) {
        return redirect()->route('student.dashboard');
    }


    return "Welcome, Dashboard!";

    // Fallback or default dashboard if role is not recognized
    // return view('dashboard'); // Or redirect to a generic dashboard

})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


// Admin-only routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', function () {
        return "Welcome, Admin!";
    })->name('admin.dashboard');

    Route::get('/admin/users', function () {
        return "Admin: Manage Users";
    })->name('admin.users');




    Route::resource('modules', ModuleController::class);
    Route::resource('topics', TopicController::class);
    Route::resource('questions', QuestionController::class)->except(['show']);
    Route::resource('tests', TestController::class);



    // Route::get('/questions/bulk', function () {
    //     view('admin.questions.bulk');
    // })->name('questions.bulk');


    // Route::post('/questions/bulk', [QuestionController::class, 'bulk'])->name('questions.bulk');



    // Route::resource('questions', QuestionController::class);


    Route::get('/api/modules/{module}/topics', [ModuleTopicApiController::class, 'getTopics']);
});

// Staff-only routes (or admin/staff)
Route::middleware(['auth', 'role:' . User::ROLE_ADMIN . ',' . User::ROLE_STAFF])->group(function () {
    Route::get('/staff/dashboard', function () {
        return "Welcome, Staff!";
    })->name('staff.dashboard');

    Route::get('/staff/courses', function () {
        return "Staff: Manage Courses";
    })->name('staff.courses');
});

// Student-only routes (or admin/staff/student)
Route::middleware(['auth', 'role:' . User::ROLE_ADMIN . ',' . User::ROLE_STAFF . ',' . User::ROLE_STUDENT])->group(function () {
    Route::get('/student/dashboard', function () {
        return "Welcome, Student!";
    })->name('student.dashboard');

    Route::get('/student/enrollments', function () {
        return "Student: View Enrollments";
    })->name('student.enrollments');
});



// Test Entry Form and Start Test
Route::get('/take-test', [TestAttemptController::class, 'showTestEntryForm'])->name('test.entry');
Route::post('/start-test', [TestAttemptController::class, 'startTest'])->name('test.start');

// Test Page (protected by session check within controller)
Route::get('/test/{testAttempt}', [TestAttemptController::class, 'showTestPage'])->name('test.show');

// AJAX endpoint for saving individual answers
Route::post('/test/{testAttempt}/save-answer', [TestAttemptController::class, 'saveAnswer'])->name('test.save_answer');

// Manual test submission
Route::post('/test/{testAttempt}/submit', [TestAttemptController::class, 'submitTest'])->name('test.submit');

// Test Results page
Route::get('/test/{testAttempt}/results', [TestAttemptController::class, 'showResults'])->name('test.results');

// Endpoint for logging cheating attempts
Route::post('/test/{testAttempt}/log-cheating', [TestAttemptController::class, 'logCheatingAttempt'])->name('test.log_cheating');






require __DIR__ . '/auth.php';
