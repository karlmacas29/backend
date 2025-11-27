<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\xPDSController;
use App\Http\Controllers\RaterController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlantillaController;
use App\Http\Controllers\RaterAuthController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\ViewActiveController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\DesignationQSController;
use App\Http\Controllers\JobBatchesRspController;
use App\Http\Controllers\OnCriteriaJobController;
use App\Http\Controllers\ExportApplicantController;
use App\Http\Controllers\StructureDetailController;
use App\Http\Controllers\OnFundedPlantillaController;
use App\Http\Controllers\ApplicantSubmissionController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\ScheduleController;


// testing route

Route::get('test/{controlNo}',[EmployeeController::class, 'findLastdata']);

//

Route::post('/verify-code', [VerificationController::class, 'verifyCode']); // verify the code
Route::post('/send-verification', [VerificationController::class, 'sendVerification']); // sending code on email

Route::middleware('auth:sanctum')->post('/logs/auth', [LogController::class, 'logAuth']);

Route::prefix('rater')->group(function () {
    Route::get('/name', [RaterController::class, 'get_rater_usernames']); // fetch list of raters
    Route::post('/login', [RaterAuthController::class, 'Raters_Login']); //  login for rater
});

Route::post('/login', [AuthController::class, 'Token_Login']); //  login for admin
Route::get('/role', [AuthController::class, 'get_role']); // role of user


// applying route
Route::prefix('applicant')->group(function () {
    Route::post('/submissions', [ApplicantSubmissionController::class, 'applicantStore']); // for external applicant with zip file
    Route::post('/employee', [ApplicantSubmissionController::class, 'employeeApplicant']); // employyee applicant applying job
    Route::post('/confirmation', [ApplicantSubmissionController::class, 'confirmDuplicateApplicant']); // confirmation for updating his excek file
});


Route::prefix('job-batches-rsp')->group(function () {
    Route::get('/', [JobBatchesRspController::class, 'index']); // fetching all job post
    Route::get('/list', [JobBatchesRspController::class, 'jobList']); // fetching the all job post on the admin
    Route::get('/{job_post_id}', [JobBatchesRspController::class, 'jobPostView']); // update the job-batches-rsp start date and end date

});

Route::get('/on-funded-plantilla/by-funded/{JobpostId}', [OnFundedPlantillaController::class, 'showByFunded']);

// Protected routes that require authentication
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/registration', [AuthController::class, 'userRegister']); // create an admin account
    Route::get('/user', [UsersController::class, 'getAuthenticatedUser']);// user

    Route::prefix('users')->group(function () {
        Route::get('/', [AuthController::class, 'getAllUsers']);
        Route::get('/{id}', [AuthController::class, 'getUserById']);
        Route::post('/logout', [AuthController::class, 'userLogout']); // logout
        Route::put('/{id}', [AuthController::class, 'updateUser']);
        Route::delete('/{id}', [AuthController::class, 'deleteUser']);
    });

    Route::prefix('rater')->group(function () {
        Route::get('/assigned-job-batches', [RaterController::class, 'getAssignedJobs']);
        Route::get('/', [UsersController::class, 'getAuthenticatedrater']);
        // Route::get('/show/{jobpostId}', [RaterController::class, 'showScores']);
        Route::post('/logout', [RaterAuthController::class, 'raterlogout']);
        Route::get('/users', [AuthController::class, 'getAllUsers']);
        Route::get('/assigned-job-batches', [RaterController::class, 'getAssignedJobs']);
        Route::get('/', [UsersController::class, 'getAuthenticatedrater']);
        Route::delete('/{id}', [RaterAuthController::class, 'deleteUser']);
        Route::get('/criteria/applicant/{id}', [RaterController::class, 'getCriteriaApplicant']);
        Route::get('/show/{jobpostId}', [RaterController::class, 'showScores']);

        Route::post('/edit/{id}', [RaterAuthController::class, 'editRater']);
        Route::post('/changepassword', [RaterAuthController::class, 'changePassword']);
        Route::post('/register', [RaterAuthController::class, 'raterRegister']);
        Route::get('/list', [RaterController::class, 'getAllRaters']);
        // Route::get('/applicant/history/score/{applicantId}', [RaterController::class, 'applicant_history_score']);
        Route::get('/{raterId}', [RaterController::class, 'view']);
        // Route::get('job/list', [RaterController::class, 'jobListAssigned']);
    });

    Route::prefix('rating')->group(function () {
        Route::delete('/score/{id}', [RaterController::class, 'delete']);
        Route::get('/index', [RaterController::class, 'index']);
        Route::delete('/delete/{id}', [SubmissionController::class, 'delete']);
        Route::post('/draft', [RaterController::class, 'draftScore']); // draft score for applicant rating score
        Route::post('/score', [RaterController::class, 'storeScore']); // final submission of the applicant score
    });

    Route::prefix('appointment')->group(function () {
        Route::get('/jobpost', [AppointmentController::class, 'jobPost']);
        Route::get('/', [AppointmentController::class, 'findAppointment']);
        Route::delete('/delete/{ControlNo}', [AppointmentController::class, 'deleteControlNo']);
        Route::post('/', [AppointmentController::class, 'appiontment']); // elective and  co-terminous also regular
        Route::get('/position', [AppointmentController::class, 'position']);
        Route::get('/vice/name/{position}/{status}', [AppointmentController::class, 'getEmployeePreviousDesignation']);
    });

    Route::prefix('vw-Active')->group(function () {
        Route::post('/status', [ViewActiveController::class, 'getStatus']);
        Route::get('/', [ViewActiveController::class, 'getActiveCount']);
        Route::get('/Sex', [ViewActiveController::class, 'getSexCount']);
        //  Route::get('/Sex', [ViewActiveController::class, 'plantilla_number']);
        Route::get('/count', [ViewActiveController::class, 'allCountStatus']);
        Route::get('/all', [ViewActiveController::class, 'fetchAllEmployee']);
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

        Route::post('/store', [CriteriaController::class, 'store']); // saving criteria
        Route::get('/sg/{sg}', [CriteriaController::class, 'fetchNonCriteriaJob']); // only will be use if the job post are dont have criteria yet
        Route::delete('/{criteria_id}', [CriteriaController::class, 'delete']);
        // Route::delete('/{id}', [CriteriaController::class, 'delete']);

        // library
        Route::post('/library/store', [CriteriaController::class, 'criteriaLibStore']);
        Route::get('/library', [CriteriaController::class, 'fetchCriteriaLibrary']); //  fetch list of criteria  available
        Route::get('/library/details/{criteriaId}', [CriteriaController::class, 'fetchCriteriaDetails']); // fetch the details of  criteria
        Route::delete('/library/delete/{criteriaId}', [CriteriaController::class, 'criteriaDelete']); // fetch the details of  criteria
        Route::post('/library/update/{criteriaId}', [CriteriaController::class, 'criteriaLibUpdate']); // criteria update on library
    });

    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        // Route::get('/job/status', [DashboardController::class, 'job_post_status']);
    });

    Route::prefix('plantilla')->group(function () {
        Route::get('/ControlNo', [PlantillaController::class, 'getMaxControlNo']);
        Route::get('/', [PlantillaController::class, 'index']);
        Route::get('/office/rater', [PlantillaController::class, 'fetchOfficeRater']);
        Route::delete('/delete/all', [OnFundedPlantillaController::class, 'deleteAllPlantillas']);
        Route::get('/appointment/{ControlNo}', [PlantillaController::class, 'getAllData']);
    });


    Route::post('/submissions/multiple', [ExportApplicantController::class, 'storeMultiple']); // store applicant multiple on jobpost usong export

    Route::get('/rater/job/list', [RaterController::class, 'jobListAssigned']);

    // Route::get('/on-funded-plantilla/by-funded/{JobpostId}', [OnFundedPlantillaController::class, 'showByFunded']);


    //
    Route::get('/export/applicant/{job_post_id}', [ExportApplicantController::class, 'historyApplicantAll']); // fetching all job post

    Route::post('/hire/{submissionId}', [AppointmentController::class, 'hireApplicant']); // hire an applicant external or internal

    Route::get('/plantilla/status', [DashboardController::class, 'plantillaNumber']);
    Route::get('/activity_log', [LogController::class, 'activityLogs']); // logs

    Route::post('/xPDS', [xPDSController::class, 'getPersonalDataSheet']);  // pds of internal

    Route::get('/logs', [LogController::class, 'index']);

    Route::get('/office', [PlantillaController::class, 'arrangement']); // this is for the modal fetching  fetching the employye // office arrangement
    Route::get('/active', [PlantillaController::class, 'vwActiveGet']); // fetching employee active
    Route::get('/view/criteria/{job_batches_rsp_id}', [CriteriaController::class, 'viewCriteria']); // view details of job criteria

    // Route::delete('/job/delete/{id}', [JobBatchesRspController::class, 'destroy']); // delete job post  with the criteria and pdf
    // Route::post('/job-post/{job_batches_id}/', [JobBatchesRspController::class, 'update']);
    Route::get('/job-post', [JobBatchesRspController::class, 'jobPost']); // fetching all job post
    Route::post('/job-post/store', [JobBatchesRspController::class, 'store']);  // create a job post
    // Route::get('/job-post/republished/', [JobBatchesRspController::class, 'getRepublishHistory']); // fetching all job post



    Route::prefix('applicant')->group(function () {
        // Route::post('/submissions', [ApplicantSubmissionController::class, 'applicant_store']); // for external applicant with zip file
        Route::get('/submissions/index', [ApplicantSubmissionController::class, 'index']);
        // Route::post('/employee', [ApplicantSubmissionController::class, 'employee_applicant']); // for employyee applicant
        Route::get('/list', [ApplicantSubmissionController::class, 'listOfApplicants']); // for employyee applicant
        Route::get('/schedule', [ScheduleController::class, 'applicantList']); // list of the applicant external and internal
        Route::get('/schedule/list', [ScheduleController::class, 'fetchApplicantHaveSchedule']); // list of schedule
        Route::get('/schedule/details/{date}/{time}', [ScheduleController::class, 'getApplicantInterview']); // schedule of the applicant details
        Route::post('/details', [ApplicantSubmissionController::class, 'getApplicantDetails']); //  fetch the applicant detail of jon post he apply
        // Route::delete('/read', [ApplicantSubmissionController::class, 'read_excel']);
        // Route::post('/image', [ApplicantSubmissionController::class, 'store_image']);
        Route::get('/{id}', [JobBatchesRspController::class, 'getApplicantPds']); // fetching the applicant per job post
        Route::get('/score/{applicantId}', [RaterController::class, 'showApplicantHistory']); // fetch the history of the applicant

    });

    Route::prefix('job-batches-rsp')->group(function () {
        Route::post('/', [JobBatchesRspController::class, 'store']);   //  create a new job post
        Route::post('/republished', [JobBatchesRspController::class, 'republished']);   // republish job-batches-rsp
        Route::put('/jobpost/{JobPostingId}', [JobBatchesRspController::class, 'unoccupied']);   // update the  job-post status to unoccupied there is no applicant hired
        Route::delete('/{id}', [JobBatchesRspController::class, 'destroy']); // delete job post
        Route::get('/{PositionID}/{ItemNo}', [JobBatchesRspController::class, 'show']);
        Route::get('/applicant/view/{id}', [JobBatchesRspController::class, 'getApplicant']); // fetching the applicant per job post
        Route::post('/applicant/evaluation/{applicantId}', [SubmissionController::class, 'evaluation']); // qualified or unqualified of the applicant
        Route::post('/update/{job_post_id}', [JobBatchesRspController::class, 'jobPostUpdate']); // updating the job post start date and end date
    });


    // qs of every position
    Route::prefix('on-criteria-job')->group(function () {
        Route::get('/', [OnCriteriaJobController::class, 'index']);
        Route::post('/', [OnCriteriaJobController::class, 'store']);
        Route::post('/{id}', [OnCriteriaJobController::class, 'update']);
        Route::delete('/{id}', [OnCriteriaJobController::class, 'destroy']);
    });


    Route::prefix('employee')->group(function () {
        Route::get('/applicant/xpds', [xPDSController::class, 'getPersonalDataSheet']); // employee pds
        Route::get('/list', [AppointmentController::class, 'employee']); // employe list
        Route::post('/update/{controlNo}', [EmployeeController::class, 'updateEmployeeCredentials']); //  updating the  employee appoitment
        Route::post('/confirmation', [EmployeeController::class, 'approveUpdate']); //  updating the  employee appoitment

        Route::get('/request', [EmployeeController::class, 'fetchApprovingTable']);
        Route::get('/{ControlNo}', [EmployeeController::class, 'appliedEmployee']);
        Route::get('/old/credentail/{pendingId}/{type}', [EmployeeController::class, 'fetchOldAndNew']);
    });

    Route::prefix('email')->group(function () {
        Route::post('/send/interview', [EmailController::class, 'sendEmailInterview']); // send an interview schedule for applicant
        Route::post('/send/status', [EmailController::class, 'sendEmailApplicantBatch']); // send an update of status applicant
    });
});

