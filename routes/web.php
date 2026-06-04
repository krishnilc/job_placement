<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\JobApplicationController;
use App\Http\Controllers\admin\JobController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/contact', [HomeController::class, 'contact'])->name('front.contact');
Route::post('/contact/send', [HomeController::class, 'submitContact'])->name('front.contact.send');
Route::get('/jobs', [JobsController::class, 'index'])->name('front.jobs');
Route::get('/jobs/detail/{id}', [JobsController::class, 'detail'])->name('jobDetail');
Route::post('/apply-job', [JobsController::class, 'applyJob'])->name('applyJob');
Route::post('/save-job', [JobsController::class, 'saveJob'])->name('saveJob');

Route::get('/forgot-password', [AccountController::class, 'forgotPassword'])->name('account.forgotPassword');

// Route::get('/account/register', [AccountController::class, 'registration'])->name('account.registration');
// Route::post('/account/process-registration', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
// Route::get('/account/login', [AccountController::class, 'login'])->name('account.login');
// Route::post('/account/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
// Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
// Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');


Route::group(['prefix' => 'admin', 'middleware' => 'checkRole'], function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/update/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/delete', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/jobs', [JobController::class, 'index'])->name('admin.jobs');
    Route::get('/jobs/edit/{id}', [JobController::class, 'edit'])->name('admin.jobs.edit');
    Route::put('/jobs/update/{id}', [JobController::class, 'update'])->name('admin.jobs.update');
    Route::delete('/jobs/delete', [JobController::class, 'destroy'])->name('admin.jobs.destroy');
    Route::get('/job-applications', [JobApplicationController::class, 'index'])->name('admin.jobApplications');
    Route::delete('/job-applications/delete', [JobApplicationController::class, 'destroy'])->name('admin.jobApplications.destroy');
});


Route::group(['prefix' => 'account'], function () {
    //Guest routes
    Route::group(['middleware' => 'guest'], function () {
        Route::get('/register', [AccountController::class, 'registration'])->name('account.registration');
        Route::post('/process-registration', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
        Route::get('/login', [AccountController::class, 'login'])->name('account.login');
        Route::post('/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
    });

    //Authenticated user routes
    Route::group(['middleware' => 'auth'], function () {
        Route::get('/student-dashboard', [AccountController::class, 'index'])->name('account.dashboard');
        Route::get('/profile', [AccountController::class, 'profile'])->name('account.profile');
        Route::put('/update-profile', [AccountController::class, 'updateProfile'])->name('account.updateProfile');
        Route::get('/logout', [AccountController::class, 'logout'])->name('account.logout');
        Route::post('/update-profile-pic', [AccountController::class, 'updateProfilePic'])->name('account.updateProfilePic');
        Route::get('/create-job', [AccountController::class, 'createJob'])->name('account.createJob');
        Route::post('/save-job', [AccountController::class, 'saveJob'])->name('account.saveJob');
        Route::get('/my-jobs', [AccountController::class, 'myJobs'])->name('account.myJobs');
        Route::get('/my-jobs/edit-job/{id}', [AccountController::class, 'editJob'])->name('account.editJob');
        Route::post('/my-jobs/update-job/{id}', [AccountController::class, 'updateJob'])->name('account.updateJob');
        Route::post('/my-jobs/delete-job', [AccountController::class, 'deleteJob'])->name('account.deleteJob');
        Route::get('/my-job-applications', [AccountController::class, 'myJobApplications'])->name('account.myJobApplications');
        Route::post('/remove-job-application', [AccountController::class, 'removeJobApplication'])->name('account.removeJobApplication');
        Route::get('/saved-jobs', [AccountController::class, 'savedJobs'])->name('account.savedJobs');
        Route::post('/remove-saved-job', [AccountController::class, 'removeSavedJob'])->name('account.removeSavedJob');
        Route::post('/update-password', [AccountController::class, 'updatePassword'])->name('account.updatePassword');
    });
});
