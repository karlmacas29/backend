<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;

class DashboardController extends Controller
{
    //

    public function index() // this for status
    {
        $qualified = Submission::where('status', 'qualified')->count();
        $pending = Submission::where('status', 'pending')->count();
        $unqualified = Submission::where('status', 'unqualified')->count();
        $total = Submission::count();

        return response()->json([
            'qualified' => $qualified,
            'pending' => $pending,
            'unqualified' => $unqualified,
            'total' => $total,
        ]);
    }


    // public function job_post_status()
    // {
    //     $jobPosts = JobBatchesRsp::select('id', 'Position','post_date')
    //         ->withCount([
    //             'applicants as total_applicants',
    //             'applicants as qualified_count' => function ($query) {
    //                 $query->where('status', 'qualified');
    //             },
    //             'applicants as unqualified_count' => function ($query) {
    //                 $query->where('status', 'unqualified');
    //             },
    //             'applicants as pending_count' => function ($query) {
    //                 $query->where('status', 'pending');
    //             },
    //         ])
    //         ->get();

    //     return response()->json($jobPosts);
    // }
}
