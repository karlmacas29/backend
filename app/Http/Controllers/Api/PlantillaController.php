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
        $data = DB::table('vwplantillaStructure')
            ->leftJoin('vwActive as office', function($join) {
                $join->on('office.PMID', '=', 'vwplantillaStructure.officePMID')
                    ->orOn('office.PMID', '=', 'vwplantillaStructure.office2PMID')
                    ->orOn('office.PMID', '=', 'vwplantillaStructure.positionPMID')
                    ->orOn('office.PMID', '=', 'vwplantillaStructure.groupPMID')
                    ->orOn('office.PMID', '=', 'vwplantillaStructure.unitPMID')
                    ->orOn('office.PMID', '=', 'vwplantillaStructure.sectionPMID')
                    ->orOn('office.PMID', '=', 'vwplantillaStructure.divisionPMID');
            })
            ->get();
        return response()->json($data);
    }
}

// ->join('vwActive', 'vwActive.PMID', '=', 'vwplantillaStructure.office2PMID')
//             ->join('vwActive', 'vwActive.PMID', '=', 'vwplantillaStructure.groupPMID')
//             ->join('vwActive', 'vwActive.PMID', '=', 'vwplantillaStructure.sectionPMID')
//             ->join('vwActive', 'vwActive.PMID', '=', 'vwplantillaStructure.unitPMID')
//             ->join('vwActive', 'vwActive.PMID', '=', 'vwplantillaStructure.divisionPMID')
//             ->select('vwActive.PMID', 'vwActive.Status', 'vwActive.Name2',
//                 'vwplantillaStructure.PageNo', 'vwplantillaStructure.ItemNo',
//                 'vwplantillaStructure.SG', 'vwplantillaStructure.position',
//                 'vwplantillaStructure.office', 'vwplantillaStructure.office2',
//                 'vwplantillaStructure.group', 'vwplantillaStructure.division',
//                 'vwplantillaStructure.section', 'vwplantillaStructure.unit',
//                 'vwplantillaStructure.Funded')
