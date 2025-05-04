<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlantillaController extends Controller
{
    public function index()
    {
        // Fetch data from the existing 'plantilla' table in SSMS
        $plantilla = DB::table('vwplantillaStructure')->get();

        return response()->json($plantilla);
    }
    public function vwActiveGet()
    {
        $data = DB::table('vwActive')
            ->get();
        return response()->json($data);
    }
}

