<?php

namespace App\Http\Controllers;

use App\Models\xPersonal;
use Illuminate\Http\Request;
use App\Services\ElectiveService;
use Illuminate\Support\Facades\DB;

class ElectiveController extends Controller
{
    //

    // protected $electiveService;

    // public function __construct(ElectiveService $electiveService)
    //     {
    //         $this->electiveService = $electiveService;
    //     }

    // public function Elective(Request $request){

    //     return $this->electiveService->Elective($request);
    // }

    // public function position(){

    //     $status = DB::table('yDesignation')->get();
    //     return response()->json($status);
    // }

    // public function  employee()
    // {

    //     $employee = xPersonal::select('ControlNo', 'Firstname', 'Surname', 'Occupation')->get();

    //     return response()->json($employee);
    // }
}
