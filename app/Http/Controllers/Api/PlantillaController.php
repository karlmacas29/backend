<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\vwplantillastructure;
use App\Models\TempRegAppointmentReorg;
use App\Models\xService;

class PlantillaController extends Controller
{


    public function test()
    {
        // Fetch data for COMPUTER PROGRAMMER II, showing office and funded status
        $plantilla = vwplantillastructure::select([
            'vwplantillaStructure.ControlNo',
            'vwplantillaStructure.ID',
            'vwplantillaStructure.office',
            'vwActive.BirthDate',
            'vwActive.Designation',
        ])
            ->leftJoin('vwActive', 'vwplantillaStructure.ControlNo', '=', 'vwActive.ControlNo')
            ->get();


        return response()->json($plantilla);
    }

    public function index()
    {
        // Fetch data from the existing 'plantilla' table in SSMS
        // $plantilla = DB::table('vwplantillaStructure')->get();
        // $plantilla = DB::table('vwplantillaStructure')->limit(50)->get();
        // $plantilla = vwplantillastructure::all();
        $plantilla = vwplantillastructure::select([
            'vwplantillaStructure.ControlNo',
            'vwplantillaStructure.ID',
            'vwplantillaStructure.office',
            'vwplantillaStructure.division',
            'vwplantillaStructure.section',
            'vwplantillaStructure.unit',
            'vwplantillaStructure.position',
            'vwplantillaStructure.PositionID',
            'vwplantillaStructure.PageNo',
            'vwplantillaStructure.ItemNo',
            'vwplantillaStructure.SG',
            'vwplantillaStructure.Funded',
            'vwplantillaStructure.level',
            'vwplantillaStructure.Name1',
            'vwplantillaStructure.Pics',
            'vwplantillaStructure.Status',
            'vwplantillaStructure.Name4',
            'vwplantillaStructure.OfficeID',
            'vwActive.BirthDate', // <-- Add this line
            'vwActive.Designation', // <-- Add this line
        ])
            ->leftJoin('vwActive', 'vwplantillaStructure.ControlNo', '=', 'vwActive.ControlNo')
            ->get();

        return response()->json($plantilla);
    }

    // office and rater on the modal rater mdoule
    public function fetch_office_rater()
    {
        $data = vwplantillastructure::select([
                'vwplantillaStructure.ControlNo',
                'vwplantillaStructure.office',
                'vwplantillaStructure.Name4',
                'vwplantillaStructure.OfficeID',
                'vwActive.BirthDate',
                'vwActive.Designation',
                'vwActive.Office',
            ])
            ->leftJoin('vwActive', 'vwplantillaStructure.ControlNo', '=', 'vwActive.ControlNo')
            ->whereNotNull('vwplantillaStructure.ControlNo')
            ->get();
        return response()->json($data);
    }



    public function vwActiveGet()
    {
        $data = DB::table('vwActive')
            ->get();
        return response()->json($data);
    }

// public function service($ControlNo)
// {
//     // Ensure $ControlNo is a string (if DB expects string)
//     $service = TempRegAppointmentReorg::with([
//         'vwplantillaStructure',
//         'xService'
//     ])
//     ->where('ControlNo', (string)$ControlNo)
//     ->get();

//     return response()->json($service);
// }


    public function service($ControlNo)
    {
        try {
            $data =xService::
                where('ControlNo', $ControlNo)
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function plantilla($ControlNo)
    {
        try {
            $data = vwplantillastructure::where('ControlNo', $ControlNo)
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function TempReg($ControlNo)
    {
        try {
            $data = TempRegAppointmentReorg::where('ControlNo', $ControlNo)
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllData($ControlNo)
    {
        try {
            $data = xService::select(['ControlNo', 'FromDate', 'ToDate'])->latest('FromDate', 'ToDate')->limit(1) // specify columns from xService
                ->with([
                    'plantilla' => function ($query) {
                        $query->select(['ControlNo', 'Name4']); // specify columns from vwplantillastructure
                    },
                    'tempRegAppointments' => function ($query) {
                        $query->select(['ControlNo', 'DesigCode','NewDesignation','Designation','SG','Step','Status','OffCode','NewOffice','Office',
                        'MRate','ItemNo','Pages','DivCode', 'SecCode','Official','Renew','StructureID','Groupcode','group','unitcode','sepcause','vicecause','sepdate'])->latest('sepdate')->limit(1); // specify columns from TempRegAppointmentReorg
                    }
                ])
                ->where('ControlNo', $ControlNo)
                ->get();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
