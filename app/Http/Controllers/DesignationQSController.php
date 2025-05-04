<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DesignationQSController extends Controller
{
    public function getDesignation(Request $request)
    {
        $positionId = $request->input('PositionID');

        $designation = DB::table('yDesignationQS')
            ->where('PositionID', $positionId)
            ->get();

        return response()->json($designation);
    }
}
