<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\xPDSController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RaterAuthController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\rater\rater_controller;
use App\Http\Controllers\Api\PlantillaController;
use App\Http\Controllers\DesignationQSController;
use App\Http\Controllers\JobBatchesRspController;
use App\Http\Controllers\OnCriteriaJobController;
use App\Http\Controllers\Api\ViewActiveController;
use App\Http\Controllers\StructureDetailController;
use App\Http\Controllers\OnFundedPlantillaController;
use App\Http\Controllers\ApplicantSubmissionController;

Route::post('/hire/{submissionId}', [AppointmentController::class, 'hireApplicant']);
Route::delete('/delete', [ApplicantSubmissionController::class, 'deleteAllUsers']);

Route::get('/plantilla/status', [DashboardController::class, 'plantilla_number']);
Route::get('/activity_log', [LogController::class, 'activityLogs']);



Route::post('/xPDS', [xPDSController::class, 'getPersonalDataSheet']);
Route::get('/employee/{ControlNo}', [EmployeeController::class, 'applied_employee']);
Route::get('/employee/applicant/xpds', [xPDSController::class, 'getPersonalDataSheet']);
Route::get('/logs', [LogController::class, 'index']);


Route::get('/office', [PlantillaController::class, 'arrangement']); // this is for the modal fetching  fetching the employye
Route::get('/active', [PlantillaController::class, 'vwActiveGet']);
Route::get('/on-funded-plantilla/by-funded/{positionID}/{itemNO}', [OnFundedPlantillaController::class, 'showByFunded']);
// Route::post('/store/criteria', [CriteriaController::class, 'store']);
Route::get('/view/criteria/{job_batches_rsp_id}', [CriteriaController::class, 'view_criteria']);

Route::delete('/job/delete/{id}', [JobBatchesRspController::class, 'destroy']); // delete job post  with the criteria and pdf
Route::post('/job-post/{job_batches_id}/', [JobBatchesRspController::class, 'update']);
Route::get('/job-post', [JobBatchesRspController::class, 'job_post']); // fetching all job post
Route::post('/job-post/store', [JobBatchesRspController::class, 'store']);

Route::post('/login', [AuthController::class, 'Token_Login']);
Route::get('/role', [AuthController::class, 'get_role']);
Route::post('/registration', [AuthController::class, 'Token_Register']);
Route::middleware('auth:sanctum')->post('/logs/auth', [LogController::class, 'logAuth']);



Route::prefix('applicant')->group(function () {

    Route::post('/submissions', [ApplicantSubmissionController::class, 'applicant_store']); // for external applicant with zip file
    Route::get('/submissions/index', [ApplicantSubmissionController::class, 'index']);
    Route::post('/employee', [ApplicantSubmissionController::class, 'employee_applicant']); // for employyee qpplicant
    Route::delete('/read', [ApplicantSubmissionController::class, 'read_excel']);
    Route::post('/image', [ApplicantSubmissionController::class, 'store_image']);
    // Route::delete('/delete', [ApplicantSubmissionController::class, 'deleteAllUsers']);
});

Route::prefix('rater')->group(function () {
    Route::get('/name', [rater_controller::class, 'get_rater_usernames']);
    Route::post('/login', [RaterAuthController::class, 'Raters_Login']);
});

Route::prefix('job-batches-rsp')->group(function () {
    Route::get('/', [JobBatchesRspController::class, 'index']);
    // Route::get('/applicant/count/{jobpostId}', [JobBatchesRspController::class, 'count']);
    Route::post('/', [JobBatchesRspController::class, 'store']);   // change old job-batches-rsp
    Route::put('/{id}', [JobBatchesRspController::class, 'update']);   // change old job-batches-rsp
    Route::put('/jobpost/{JobPostingId}', [JobBatchesRspController::class, 'Unoccupied']);   // change old job-batches-rsp

    Route::delete('/{id}', [JobBatchesRspController::class, 'destroy']);
    Route::get('/{PositionID}/{ItemNo}', [JobBatchesRspController::class, 'show']);
    // Route::post('/applicant/view/{id}', [JobBatchesRspController::class, 'getApplicants']);
    Route::post('/get/view/', [JobBatchesRspController::class, 'get_submission_table']);
    Route::get('/list', [JobBatchesRspController::class, 'job_list']); // fetching the all job post on the admin
    Route::get('/applicant/view/{id}', [JobBatchesRspController::class, 'get_applicant']); // fetching the applicant per job post
    Route::post('/applicant/evaluation/{applicantId}', [SubmissionController::class, 'evaluation']);

    Route::post('/update/{job_post_id}', [JobBatchesRspController::class, 'job_post_update']);
    Route::get('/{job_post_id}', [JobBatchesRspController::class, 'job_post_view']);
});

Route::prefix('on-criteria-job')->group(function () {
    Route::get('/', [OnCriteriaJobController::class, 'index']);
    Route::post('/', [OnCriteriaJobController::class, 'store']); // change old on-criteria-job
    Route::post('/{id}', [OnCriteriaJobController::class, 'update']);
    Route::delete('/{id}', [OnCriteriaJobController::class, 'destroy']);
    Route::get('/{PositionID}/{ItemNo}', [OnCriteriaJobController::class, 'show']);
});



// Protected routes that require authentication

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [UsersController::class, 'getAuthenticatedUser']);
    Route::post('/store/criteria', [CriteriaController::class, 'store']);

    Route::prefix('users')->group(function () {
        Route::get('/', [AuthController::class, 'getAllUsers']);
        Route::get('/{id}', [AuthController::class, 'getUserById']);
        Route::post('/logout', [AuthController::class, 'Token_Logout']);
        Route::put('/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/{id}', [AuthController::class, 'deleteUser']);
    });

    Route::prefix('rater')->group(function () {
        Route::get('/assigned-job-batches', [rater_controller::class, 'getAssignedJobs']);
        Route::get('/', [UsersController::class, 'getAuthenticatedrater']);
        // Route::get('/show/{jobpostId}', [rater_controller::class, 'showScores']);
        Route::post('/logout', [RaterAuthController::class, 'Rater_logout']);
        Route::get('/users', [AuthController::class, 'getAllUsers']);
        Route::get('/assigned-job-batches', [rater_controller::class, 'getAssignedJobs']);
        Route::get('/', [UsersController::class, 'getAuthenticatedrater']);
        Route::delete('/{id}', [RaterAuthController::class, 'deleteUser']);
        Route::get('/criteria/applicant/{id}', [rater_controller::class, 'get_criteria_applicant']);
        Route::get('/show/{jobpostId}', [rater_controller::class, 'showScoresWithHistory']);
        Route::post('/edit/{id}', [RaterAuthController::class, 'editRater']);
        Route::post('/logout', [RaterAuthController::class, 'Rater_logout']);
        Route::post('/changepassword', [RaterAuthController::class, 'change_password']);
        Route::post('/register', [RaterAuthController::class, 'RatersRegister']);
        Route::get('/list', [rater_controller::class, 'get_all_raters']);
        Route::get('/applicant/history/score/{applicantId}', [rater_controller::class, 'applicant_history_score']);
        Route::get('/{raterId}', [rater_controller::class, 'view']);
    });

    Route::prefix('rating')->group(function () {
        Route::delete('/score/{id}', [rater_controller::class, 'delete']);
        Route::get('/index', [rater_controller::class, 'index']);
        Route::delete('/delete/{id}', [SubmissionController::class, 'delete']);
        Route::post('/draft', [rater_controller::class, 'draft_score']); // draft score for applicant rating score
        Route::post('/score', [rater_controller::class, 'store_score']); // final submission of the applicant score
    });

    Route::prefix('appointment')->group(function () {
        Route::get('/jobpost', [AppointmentController::class, 'job_post']);
        Route::get('/', [AppointmentController::class, 'find_appointment']);
        Route::delete('/delete/{ControlNo}', [AppointmentController::class, 'deleteControlNo']);
    });



    Route::prefix('vw-Active')->group(function () {
        Route::post('/status', [ViewActiveController::class, 'getStatus']);
        Route::get('/', [ViewActiveController::class, 'getActiveCount']);
        Route::get('/Sex', [ViewActiveController::class, 'getSexCount']);
        //  Route::get('/Sex', [ViewActiveController::class, 'plantilla_number']);
        Route::get('/count', [ViewActiveController::class, 'allCountStatus']);
        Route::get('/all', [ViewActiveController::class, 'fetch_all_employee']);
    });

    Route::apiResource('/plantilla/funded', OnFundedPlantillaController::class);
    Route::prefix('structure-details')->group(function () {
        Route::post('/update-funded', [StructureDetailController::class, 'updateFunded']);
        Route::post('/update-pageno', [StructureDetailController::class, 'updatePageNo']);
    });

    Route::prefix('plantillaData')->group(function () {
        Route::get('/', [PlantillaController::class, 'vwActiveGet']);
        // Route::get('/qs', [DesignationQSController::class, 'getDesignation']);
        Route::post('/qs', [DesignationQSController::class, 'getDesignation']);
    });

    Route::prefix('criteria')->group(function () {
        Route::get('/{job_batches_rsp_id}', [CriteriaController::class, 'show']);
        // Route::post('/store/criteria', [CriteriaController::class, 'store']);
        Route::delete('/{criteria_id}', [CriteriaController::class, 'delete']);
        Route::delete('/{id}', [CriteriaController::class, 'delete']);
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/job/status', [DashboardController::class, 'job_post_status']);
    });

    Route::prefix('plantilla')->group(function () {
        Route::get('/ControlNo', [PlantillaController::class, 'getMaxControlNo']);
        Route::get('/', [PlantillaController::class, 'index']);
        Route::get('/office/rater', [PlantillaController::class, 'fetch_office_rater']);
        Route::delete('/delete/all', [OnFundedPlantillaController::class, 'deleteAllPlantillas']);
        Route::get('/appointment/{ControlNo}', [PlantillaController::class, 'getAllData']);
    });
});

