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



    public function findAppointment()
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


    public function jobPost()
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

    public function employee(Request $request)
    {
        // Get page size (default 10)
        $perPage = (int) $request->input('per_page', 10);

        // Get search parameter
        $search = $request->input('search', '');

        $query = DB::table('xPersonal')
            ->select('ControlNo', 'Firstname', 'Surname', 'Occupation');

        // Add search filter if search term exists
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('Firstname', 'LIKE', "%{$search}%")
                    ->orWhere('Surname', 'LIKE', "%{$search}%")
                    ->orWhere('ControlNo', 'LIKE', "%{$search}%");
            });
        }

        $employee = $query->paginate($perPage);

        return response()->json($employee);
    }

    // public function employee(Request $request)
    // {
    //     $perPage = $request->input('per_page', 10); // default 10

    //     return DB::table('xPersonal')
    //         ->select('ControlNo', 'Firstname', 'Surname', 'Occupation')
    //         ->paginate($perPage);
    // }


    // get the employee Previous appiontment on the  position
    // public function getEmployeePreviousDesignation($position,$status){

    //     $employee = DB::table('vwpartitionforseparated')->select('ControlNo','FromDate','ToDate','Designation','Status','SepDate','Sepcause')
    //     ->where('Designation',$position)
    //     ->where('Status',$status)
    //     ->get();


    //     return response()->json($employee);

    // }

    public function getEmployeePreviousDesignation($position, $status)
    {
        $today = now()->toDateString();

        $employee = DB::table(DB::raw("(SELECT
            ControlNo,
            FromDate,
            ToDate,
            Designation,
            Status,
            SepDate,
            Sepcause,
            ROW_NUMBER() OVER (
                PARTITION BY ControlNo
                ORDER BY FromDate DESC
            ) AS rn
        FROM vwpartitionforseparated
        WHERE Designation = '$position'
          AND Status = '$status'
    ) AS t"))
            ->join('xPersonal as p', 'p.ControlNo', '=', 't.ControlNo')
            ->where('t.rn', 1)
            ->whereDate('t.ToDate', '<', $today) // inactive employees only
            ->select(
                't.ControlNo',
                'p.Surname',
                'p.Firstname',
                'p.Middlename',
                't.FromDate',
                't.ToDate',
                't.Designation',
                't.Status',
                't.SepDate',
                't.SepCause'
            )
            ->get();

        return response()->json($employee);
    }
}
