<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\xPersonal;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\TempRegHistory;
use Illuminate\Support\Facades\DB;
use App\Services\AppiontmentService;
use App\Services\ApplicantHiringService;

class AppointmentController extends Controller
{
    protected $appiontmentService;
    protected $hiringService;

    public function __construct(ApplicantHiringService $hiringService, AppiontmentService $appiontmentService)
    {
        $this->hiringService = $hiringService;
        $this->appiontmentService = $appiontmentService;
    }

    public function hireApplicant($submissionId)
    {
        // Call the service method
        return $this->hiringService->hireApplicant($submissionId);
    }

    public function appiontment(Request $request)
    {

        return $this->appiontmentService->appiontment($request);
    }



    public function find_appointment()
    {
        $data = DB::table('vwplantillaStructure')
            ->where(function ($query) {
                $query->whereNull('ControlNo')
                    ->orWhere('ControlNo', '');
            })
            ->where('office', 'OFFICE OF THE CITY ACCOUNTANT') // ✅ filter by office
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }


    public function maxControlNo()
    {

        $data = DB::table('xPersonal')->max('ControlNo');
        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }


    public function job_post()
    {

        $data = DB::table('tblStructureDetails')->limit(5)->get();
        return response()->json([
            'status' => 200,
            'data' => $data
        ]);
    }


    public function deleteControlNo($ControlNo)
    {
        // Example: check service record
        $hasDependencies = DB::table('xPersonal')
            ->where('ControlNo', $ControlNo)
            ->exists();

        if ($hasDependencies) {
            return response()->json([
                'status' => 400,
                'message' => 'Cannot delete, employee has service records.'
            ]);
        }

        $deleted = DB::table('xPersonal')->where('ControlNo', $ControlNo)->delete();

        return response()->json([
            'status' => 200,
            'deleted' => $deleted
        ]);
    }







    public function position()
    {

        $status = DB::table('yDesignation')->get();
        return response()->json($status);
    }

    public function  employee()
    {

        $employee = xPersonal::select('ControlNo', 'Firstname', 'Surname', 'Occupation')->get();

        return response()->json($employee);
    }

}
