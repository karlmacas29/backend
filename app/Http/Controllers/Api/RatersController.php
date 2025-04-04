<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatersController extends Controller
{
    public function index()
    {
        // Fetch data from the existing 'rater' table in SSMS
        $rater = DB::table('tblRater')->get();

        return response()->json($rater);
    }
}
