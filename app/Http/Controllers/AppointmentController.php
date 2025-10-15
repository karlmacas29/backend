<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\TempRegHistory;
use Illuminate\Support\Facades\DB;
use App\Services\ApplicantHiringService;

class AppointmentController extends Controller
{

    protected $hiringService;

    public function __construct(ApplicantHiringService $hiringService)
    {
        $this->hiringService = $hiringService;
    }

    public function hireApplicant($submissionId)
    {
        // Call the service method
        return $this->hiringService->hireApplicant($submissionId);
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


}
