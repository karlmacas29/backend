<?php

namespace App\Http\Controllers;

use App\Models\xPersonal;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\table;
use App\Models\vwplantillastructure;

class EmployeeController extends Controller
{
    //

    public function appliedEmployee($ControlNo)
    {
        // Get all submissions of employee using ControlNo
        $employeeApplications = Submission::with('jobPost')
            ->where('ControlNo', $ControlNo)
            ->get();

        return response()->json([
            'data' => $employeeApplications->map(function ($submission) {
                return [
                    'submission_id' => $submission->id,
                    'status'        => $submission->status,
                    'position'      => $submission->jobPost->Position ?? null,
                    'office'        => $submission->jobPost->Office ?? null,
                    'applied_at'    => $submission->created_at,
                ];
            })
        ]);
    }

  //update tempreg and xservice and xpersonal  of the employee
  public function updateEmployeeCredentials($controlNo, Request $request){

      $validated = $request->validate([

             //xPersonal
            'Surname' => 'required|string',
            'Firstname' => 'required|string',
            'MIddlename' => 'required|string',
            'Sex' => 'required|string',
            'CivilStatus' => 'required|string',
            'BirthDate' => 'required|date',

            // tempreg
            'sepdate' => 'required|string',
            'sepcause' => 'required|string',
            'vicename' => 'required|string',
            'vicecause' => 'required|string',

            //xservice
            'FromDate' => 'required|date',
            'ToDate' => 'required|date',
      ]);

        //   $employee = $request->update($validated);

        $xPersonal  = DB::table('xPersonal')->where('ControlNo',$controlNo); 

        $xPersonal->update([
                'Surname' => $validated['Surname'],
                'Firstname' => $validated['Firstname'],
                'MIddlename' => $validated['MIddlename'],
                'Sex' => $validated['Sex'],
                'CivilStatus' => $validated['CivilStatus'],
                'BirthDate' => $validated['BirthDate'],
        ]);

        $tempreg = DB::table('xService')->where('ControlNo', $controlNo)->orderBy('ID', 'desc'); // getting the lastest using the ID

        $tempreg->update([
                'FromDate' => $validated['FromDate'],
                'ToDate' => $validated['ToDate'],

        ]);


        $xService = DB::table('xService')->where('ControlNo', $controlNo)->orderBy('PMID', 'desc');   // getting the lastest using the PMID

        return response()->json([
            'message' => 'Employee information updated successfully.',
            'xPersonal' => $xPersonal,
            'tempreg' => $tempreg,
            'xService' => $xService,
        ]);
  }


   // test

   public function findLastdata($controlNo){

        $tempreg = DB::table('xService')->where('ControlNo', $controlNo)->orderBy('PMID', 'desc') // or use sepdate if you want the most recent
            ->first();


        return response()->json($tempreg);

   }

}
