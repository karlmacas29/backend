<?php

namespace App\Http\Controllers;

use App\Models\xPersonal;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\table;
use App\Models\vwplantillastructure;
use Illuminate\Support\Facades\Auth;

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
        $user = Auth::user(); // User performing the update
        $validated = $request->validate([

             //xPersonal
            'Surname' => 'required|string',
            'Firstname' => 'required|string',
            'MIddlename' => 'required|string',
            'Sex' => 'nullable|string',
            'CivilStatus' => 'required|string',
            'BirthDate' => 'nullable|date',
            'TINNo' => 'nullable|string',
            'Address' => 'nullable|string',


            // tempreg

            'sepdate' => 'nullable|date',
            'sepcause' => 'nullable|string',
            'vicename' => 'nullable|string',
            'vicecause' => 'nullable|string',

            // //xservice
            // 'FromDate' => 'required|date',
            // 'ToDate' => 'required|date',



            // tempRegAppointmentReorgExt
            'tempExtId' => 'nullable|string',
            'PresAppro'         => 'required|string',
            'PrevAppro'         => 'required|string',
            'SalAuthorized'     => 'required|string',
            'OtherComp'         => 'required|string',
            'SupPosition'       => 'required|string',
            'HSupPosition'      => 'required|string',
            'Tool'              => 'nullable|string',



            'Contact1'          => 'required|integer',
            'Contact2'          => 'required|integer',
            'Contact3'          => 'required|integer',
            'Contact4'          => 'required|integer',
            'Contact5'          => 'required|integer',
            'Contact6'          => 'required|integer',
            'ContactOthers'     => 'nullable|string',

            'Working1'          => 'required|integer',
            'Working2'          => 'required|integer',
            'WorkingOthers'     => 'nullable|string',

            'DescriptionSection'   => 'nullable|string',
            'DescriptionFunction'  => 'nullable|string',

            'StandardEduc'      => 'nullable|string',
            'StandardExp'       => 'nullable|string',
            'StandardTrain'     => 'nullable|string',
            'StandardElig'      => 'nullable|string',

            'Supervisor'        => 'nullable|string',

            'Core1'             => 'nullable|integer',
            'Core2'             => 'nullable|integer',
            'Core3'             => 'nullable|integer',

            'Corelevel1'        => 'required|integer',
            'Corelevel2'        => 'required|integer',
            'Corelevel3'        => 'required|integer',
            'Corelevel4'        => 'required|integer',

            'Leader1'           => 'required|integer',
            'Leader2'           => 'required|integer',
            'Leader3'           => 'required|integer',
            'Leader4'           => 'required|integer',

            'leaderlevel1'      => 'required|integer',
            'leaderlevel2'      => 'required|integer',
            'leaderlevel3'      => 'required|integer',
            'leaderlevel4'      => 'required|integer',

            'structureid'       => 'required|integer',


      ]);

        $xPersonal  = DB::table('xPersonal')
            ->where('ControlNo', $controlNo)
            ->update([
                'Surname' => $validated['Surname'] ?? null,
                'Firstname' => $validated['Firstname'] ?? null,
                'Middlename' => $validated['Middlename'] ?? null,
                'Sex' => $validated['Sex'] ?? 'N/A',
                'CivilStatus' => $validated['CivilStatus'] ?? null,
                'BirthDate' => $validated['BirthDate'] ?? null,
                'TINNo' => $validated['TINNo'] ?? null,
                'Address' => $validated['Address'] ?? null,

            ]);
        $updatedEmployee = DB::table('xPersonal')->where('ControlNo', $controlNo)->first();
        $employeeFullname = $updatedEmployee->Firstname . ' ' . $updatedEmployee->Surname;

        $xtempreg = DB::table('tempRegAppointmentReorg')
            ->where('ControlNo', $controlNo)
            ->orderByDesc('ID')
            ->first();

        if ($xtempreg) {
            DB::table('tempRegAppointmentReorg')
                ->where('ID', $xtempreg->ID)
                ->update([
                    'sepdate' => $validated['sepdate'] ?? null,
                    'sepcause' => $validated['sepcause'] ?? null,
                    'vicename' => $validated['vicename'] ?? null,
                    'vicecause' => $validated['vicecause'] ?? null,

                ]);
        }

        $tempregExt = DB::table('tempRegAppointmentReorgExt')
            ->where('ControlNo', $controlNo)
            ->orderByDesc('ID')
            ->first();

        $data = [
            'ControlNo' => $controlNo,
            'PresAppro'        => $validated['PresAppro'] ?? null,
            'PrevAppro'        => $validated['PrevAppro'] ?? null,
            'SalAuthorized'    => $validated['SalAuthorized'] ?? null,
            'OtherComp'        => $validated['OtherComp'] ?? null,
            'SupPosition'      => $validated['SupPosition'] ?? null,
            'HSupPosition'     => $validated['HSupPosition'] ?? null,
            'Tool'             => $validated['Tool'] ?? null,

            'Contact1'         => $validated['Contact1'] ?? null,
            'Contact2'         => $validated['Contact2'] ?? null,
            'Contact3'         => $validated['Contact3'] ?? null,
            'Contact4'         => $validated['Contact4'] ?? null,
            'Contact5'         => $validated['Contact5'] ?? null,
            'Contact6'         => $validated['Contact6'] ?? null,
            'ContactOthers'    => $validated['ContactOthers'] ?? null,

            'Working1'         => $validated['Working1'] ?? null,
            'Working2'         => $validated['Working2'] ?? null,
            'WorkingOthers'    => $validated['WorkingOthers'] ?? null,

            'DescriptionSection'  => $validated['DescriptionSection'] ?? null,
            'DescriptionFunction' => $validated['DescriptionFunction'] ?? null,

            'StandardEduc'     => $validated['StandardEduc'] ?? null,
            'StandardExp'      => $validated['StandardExp'] ?? null,
            'StandardTrain'    => $validated['StandardTrain'] ?? null,
            'StandardElig'     => $validated['StandardElig'] ?? null,

            'Supervisor'       => $validated['Supervisor'] ?? null,

            'Core1'            => $validated['Core1'] ?? null,
            'Core2'            => $validated['Core2'] ?? null,
            'Core3'            => $validated['Core3'] ?? null,

            'Corelevel1'       => $validated['Corelevel1'] ?? null,
            'Corelevel2'       => $validated['Corelevel2'] ?? null,
            'Corelevel3'       => $validated['Corelevel3'] ?? null,
            'Corelevel4'       => $validated['Corelevel4'] ?? null,

            'Leader1'          => $validated['Leader1'] ?? null,
            'Leader2'          => $validated['Leader2'] ?? null,
            'Leader3'          => $validated['Leader3'] ?? null,
            'Leader4'          => $validated['Leader4'] ?? null,

            'leaderlevel1'     => $validated['leaderlevel1'] ?? null,
            'leaderlevel2'     => $validated['leaderlevel2'] ?? null,
            'leaderlevel3'     => $validated['leaderlevel3'] ?? null,
            'leaderlevel4'     => $validated['leaderlevel4'] ?? null,

            'structureid'      => $validated['structureid'] ?? null,

        ];


        if ($tempregExt) {
            // Update only the latest row
            DB::table('tempRegAppointmentReorgExt')
                ->where('ID', $tempregExt->ID)
                ->update($data);
        } else {
            // Insert new row if none exists
            DB::table('tempRegAppointmentReorgExt')->insert($data);
        }

        activity('Appointment')
        ->causedBy($user)
        ->withProperties(['updated_employee' => $employeeFullname, 'control_no' => $controlNo,])
            ->log("User '{$user->name}' updated the appointment of employee '{$employeeFullname}'.");
        return response()->json([
            'success' => true,
            'message' => 'Update saved successfully. Please wait for an administrator to review and approve the changes.',
            'xPersonal' => $xPersonal,
            'xtempreg' => $xtempreg,
            'tempregExt' => $tempregExt
            // 'xService' => $xService,
        ]);
  }

}
