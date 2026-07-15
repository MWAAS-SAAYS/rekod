<?php

use App\Http\Controllers\AttachmentDetailsController;
use App\Http\Controllers\AttachmentSelectedController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\IndustrialSupervisorController;
use App\Http\Controllers\OpportunityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TEMPORARY COOKIE TEST
|--------------------------------------------------------------------------
| Remove this route after we finish debugging Railway.
|--------------------------------------------------------------------------
*/
Route::get('/cookie-test', function (Request $request) {

    session(['hello' => 'world']);

    cookie()->queue(cookie(
        'test_cookie',
        'working',
        60,
        '/',
        null,
        true,
        true,
        false,
        'lax'
    ));

    return response()->json([
        'success' => true,
        'session_id' => session()->getId(),
        'session_value' => session('hello'),
        'csrf_token' => csrf_token(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Welcome Page
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (Auth::check() && Auth::user()->role) {
        return redirect()->route(Auth::user()->role . '.portal');
    }

    return view('welcome');
})->name('welcome');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    | Profile
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    | Companies
    */
    Route::get('/companies', [CompanyController::class, 'companies'])->name('companies');
    Route::post('/companies', [CompanyController::class, 'storeCompany'])->name('companies.store');

    Route::get(
        '/get-company-industrial-supervisors/{id}',
        [IndustrialSupervisorController::class, 'getCompanyIndustrialSupervisors']
    )->name('get_company_industrial_supervisors');

    /*
    | Attachment Selection
    */
    Route::get('/select-attachment', [AttachmentSelectedController::class, 'index'])
        ->name('attachment_selected.select');

    Route::post('/attachment/select', [AttachmentSelectedController::class, 'store'])
        ->name('attachment_selected.store');

    /*
    | Daily Reports
    */
    Route::get('/student/logbook/{attachment_student_id}', [DailyReportController::class, 'index'])
        ->name('logbook');

    /*
    | Attachment Details
    */
    Route::get('/attachments/{id}', [AttachmentDetailsController::class, 'show'])
        ->name('attachments.show');

    Route::get('/attachment-details/{attachment_student_id}', [AttachmentDetailsController::class, 'show'])
        ->name('attachmentDetails.show');

    /*
    | Opportunities
    */
    Route::get('/opportunities', [OpportunityController::class, 'index'])
        ->name('opportunities.index');

    Route::get('/opportunities/{opportunity}/apply', [OpportunityController::class, 'showApplyForm'])
        ->name('opportunities.apply');

    Route::post('/opportunities/{opportunity}/apply', [OpportunityController::class, 'submitApplication'])
        ->name('opportunities.apply.submit');

    Route::get('/opportunities/{opportunity}/applications', [OpportunityController::class, 'showApplications'])
        ->name('opportunities.applications');
});

/*
|--------------------------------------------------------------------------
| Student Portal
|--------------------------------------------------------------------------
*/

Route::middleware(['portal:student'])
    ->prefix('students')
    ->group(base_path('routes/student.php'));

/*
|--------------------------------------------------------------------------
| Admin Portal
|--------------------------------------------------------------------------
*/

Route::middleware(['portal:admin'])
    ->prefix('admin')
    ->group(base_path('routes/admin.php'));

/*
|--------------------------------------------------------------------------
| Lecturer
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('lecturer')
    ->name('lecturer.')
    ->group(function () {

        Route::get('/student/logbook/{id}', [DailyReportController::class, 'index'])
            ->name('student.logbook');
    });

/*
|--------------------------------------------------------------------------
| Industrial Supervisor
|--------------------------------------------------------------------------
*/

Route::middleware('auth')
    ->prefix('industrial-supervisor')
    ->name('industrial_supervisor.')
    ->group(function () {

        Route::get('/student/logbook/{id}', [DailyReportController::class, 'index'])
            ->name('student.logbook');
    });