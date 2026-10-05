<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountManagementController;
use App\Http\Controllers\admin\AdminController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\CollegeController;
use App\Http\Controllers\admin\DashboardController;
use App\Http\Controllers\admin\EmployerController;
use App\Http\Controllers\admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\admin\JobApplicationController;
use App\Http\Controllers\admin\JobController;
use App\Http\Controllers\admin\JobTypeController;
use App\Http\Controllers\admin\StudentController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobsController;
use App\Http\Controllers\MyJobController;
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
Route::get('/download-application/{application}/{type}', [JobsController::class, 'downloadApplicationFile'])
    ->middleware('auth')
    ->name('application.download');

Route::get('/forgot-password', [AccountController::class, 'forgotPassword'])->name('account.forgotPassword');

// Route::get('/account/register', [AccountController::class, 'registration'])->name('account.registration');
// Route::post('/account/process-registration', [AccountController::class, 'processRegistration'])->name('account.processRegistration');
// Route::get('/account/login', [AccountController::class, 'login'])->name('account.login');
// Route::post('/account/authenticate', [AccountController::class, 'authenticate'])->name('account.authenticate');
// Route::get('/account/profile', [AccountController::class, 'profile'])->name('account.profile');
// Route::get('/account/logout', [AccountController::class, 'logout'])->name('account.logout');


Route::group(['prefix' => 'admin', 'middleware' => 'checkRole'], function () {
    Route::get('/home', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/reports/export/{report}/{format}', [DashboardController::class, 'exportReport'])->name('admin.reports.export');

    Route::middleware('auth')->prefix('account')->name('admin.account.')->group(function () {
        Route::get('/profile', [AccountManagementController::class, 'adminViewProfile'])->name('profile');
        Route::get('/edit-profile', [AccountManagementController::class, 'adminProfile'])->name('editProfile');
        Route::get('/edit-password', [AccountManagementController::class, 'adminEditPassword'])->name('editPassword');
    });
    Route::get('/users', [UserController::class, 'index'])->name('admin.users');
    Route::get('/users/students', [StudentController::class, 'index'])->name('admin.users.students');
    Route::get('/users/employers', [EmployerController::class, 'index'])->name('admin.users.employers');

    Route::middleware('checkSuperAdmin')->group(function () {
        Route::get('/users/admins', [AdminController::class, 'index'])->name('admin.users.admins');
        Route::get('/users/admins/create', [AdminController::class, 'create'])->name('admin.users.admins.create');
        Route::post('/users/admins/store', [AdminController::class, 'store'])->name('admin.users.admins.store');
    });

    Route::get('/users/profile/{id}', [UserController::class, 'profile'])->name('admin.users.profile');
    Route::get('/users/edit/{id}', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('/users/update/{id}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/delete', [UserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/jobs', [JobController::class, 'index'])->name('admin.jobs');
    Route::get('/jobs/create', [JobController::class, 'create'])->name('admin.jobs.create');
    Route::post('/jobs/store', [JobController::class, 'store'])->name('admin.jobs.store');
    Route::get('/jobs/edit/{id}', [JobController::class, 'edit'])->name('admin.jobs.edit');
    Route::put('/jobs/update/{id}', [JobController::class, 'update'])->name('admin.jobs.update');
    Route::delete('/jobs/delete', [JobController::class, 'destroy'])->name('admin.jobs.destroy');
    Route::get('/job-applications', [JobApplicationController::class, 'index'])->name('admin.jobApplications');
    Route::patch('/job-applications/{application}/status', [JobApplicationController::class, 'updateStatus'])->name('admin.jobApplications.status');
    Route::delete('/job-applications/delete', [JobApplicationController::class, 'destroy'])->name('admin.jobApplications.destroy');
    // Employers submit feedback about a placed student from this same group (CheckAdmin middleware allows the employer role).
    Route::post('/job-applications/{application}/feedback', [FeedbackController::class, 'store'])->name('admin.jobApplications.feedback.store');

    Route::middleware('checkAdminOrSuperAdmin')->group(function () {
        // Feedback is only ever visible to admins and super admins.
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('admin.feedback');

        Route::get('/users/students/create', [StudentController::class, 'create'])->name('admin.users.students.create');
        Route::post('/users/students/store', [StudentController::class, 'store'])->name('admin.users.students.store');
        Route::patch('/users/students/{id}/status', [StudentController::class, 'updateStatus'])->name('admin.users.students.status');
        Route::patch('/users/employers/{id}/status', [EmployerController::class, 'updateStatus'])->name('admin.users.employers.status');
        Route::get('/users/employers/create', [EmployerController::class, 'create'])->name('admin.users.employers.create');
        Route::post('/users/employers/store', [EmployerController::class, 'store'])->name('admin.users.employers.store');

        Route::get('/colleges', [CollegeController::class, 'index'])->name('admin.colleges');
        Route::get('/colleges/create', [CollegeController::class, 'create'])->name('admin.colleges.create');
        Route::post('/colleges/store', [CollegeController::class, 'store'])->name('admin.colleges.store');
        Route::get('/colleges/edit/{id}', [CollegeController::class, 'edit'])->name('admin.colleges.edit');
        Route::put('/colleges/update/{id}', [CollegeController::class, 'update'])->name('admin.colleges.update');
        Route::delete('/colleges/delete', [CollegeController::class, 'destroy'])->name('admin.colleges.destroy');

        Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/categories/store', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('/categories/update/{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/delete', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

        Route::get('/job-types', [JobTypeController::class, 'index'])->name('admin.jobTypes');
        Route::get('/job-types/create', [JobTypeController::class, 'create'])->name('admin.jobTypes.create');
        Route::post('/job-types/store', [JobTypeController::class, 'store'])->name('admin.jobTypes.store');
        Route::get('/job-types/edit/{id}', [JobTypeController::class, 'edit'])->name('admin.jobTypes.edit');
        Route::put('/job-types/update/{id}', [JobTypeController::class, 'update'])->name('admin.jobTypes.update');
        Route::delete('/job-types/delete', [JobTypeController::class, 'destroy'])->name('admin.jobTypes.destroy');
    });
});

Route::group(['prefix' => 'employer', 'middleware' => 'checkRole'], function () {
    Route::get('/home', [EmployerController::class, 'dashboard'])->name('employer.dashboard');

    Route::middleware('auth')->prefix('account')->name('employer.account.')->group(function () {
        Route::get('/profile', [AccountManagementController::class, 'employerViewProfile'])->name('profile');
        Route::get('/edit-profile', [AccountManagementController::class, 'employerProfile'])->name('editProfile');
        Route::get('/edit-password', [AccountManagementController::class, 'employerEditPassword'])->name('editPassword');
    });
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
        Route::get('/student-dashboard', [AccountController::class, 'index'])->name('student.dashboard');
        Route::get('/profile', [AccountManagementController::class, 'viewProfile'])->name('account.profile');
        Route::get('/edit-profile', [AccountManagementController::class, 'profile'])->name('account.editProfile');
        Route::get('/edit-password', [AccountManagementController::class, 'editPassword'])->name('account.editPassword');
        Route::post('/update-password', [AccountManagementController::class, 'updatePassword'])->name('account.updatePassword');
        Route::put('/update-profile', [AccountManagementController::class, 'updateProfile'])->name('account.updateProfile');
        Route::get('/logout', [AccountController::class, 'logout'])->name('account.logout');
        Route::post('/update-profile-pic', [AccountManagementController::class, 'updateProfilePic'])->name('account.updateProfilePic');
        Route::get('/create-job', [MyJobController::class, 'createJob'])->name('account.createJob');
        Route::post('/save-job', [MyJobController::class, 'saveJob'])->name('account.saveJob');
        Route::get('/my-jobs', [MyJobController::class, 'myJobs'])->name('account.myJobs');
        Route::get('/my-jobs/edit-job/{id}', [MyJobController::class, 'editJob'])->name('account.editJob');
        Route::post('/my-jobs/update-job/{id}', [MyJobController::class, 'updateJob'])->name('account.updateJob');
        Route::post('/my-jobs/delete-job', [MyJobController::class, 'deleteJob'])->name('account.deleteJob');
        Route::post('/my-jobs/block-job', [MyJobController::class, 'blockJob'])->name('account.blockJob');
        Route::post('/my-jobs/unblock-job', [MyJobController::class, 'unblockJob'])->name('account.unblockJob');
        Route::get('/my-job-applications', [AccountManagementController::class, 'myJobApplications'])->name('account.myJobApplications');
        Route::post('/remove-job-application', [AccountManagementController::class, 'removeJobApplication'])->name('account.removeJobApplication');
        // Students submit feedback about the company they were placed with.
        Route::post('/job-applications/{application}/feedback', [FeedbackController::class, 'store'])->name('account.feedback.store');
        Route::get('/saved-jobs', [AccountManagementController::class, 'savedJobs'])->name('account.savedJobs');
        Route::post('/remove-saved-job', [AccountManagementController::class, 'removeSavedJob'])->name('account.removeSavedJob');
    });
});
