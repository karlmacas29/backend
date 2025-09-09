<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    //

    public function applied_employee($ControlNo)
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
}
