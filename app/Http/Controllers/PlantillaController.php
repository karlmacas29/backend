<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\vwplantillastructure;
use App\Models\TempRegAppointmentReorg;
use App\Models\vwActive;
use App\Models\vwofficearrangement;
use App\Models\xService;

class PlantillaController extends Controller
{

    public function getMaxControlNo()
    {
        // Fetch the maximum ControlNo from vwplantillastructure
        $maxControlNo = vwplantillastructure::max('ControlNo');

        // Return the maximum ControlNo as a JSON response
        return response()->json(['maxControlNo' => $maxControlNo]);
    }

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

    // public function index(Request $request)
    // {
    //     $query = vwplantillastructure::select([
    //         'vwplantillaStructure.ControlNo',
    //         'vwplantillaStructure.ID',
    //         'vwplantillaStructure.office',
    //         'vwplantillaStructure.office2',
    //         'vwplantillaStructure.group',
    //         'vwplantillaStructure.division',
    //         'vwplantillaStructure.section',
    //         'vwplantillaStructure.unit',
    //         'vwplantillaStructure.position',
    //         'vwplantillaStructure.PositionID',
    //         'vwplantillaStructure.PageNo',
    //         'vwplantillaStructure.ItemNo',
    //         'vwplantillaStructure.SG',
    //         'vwplantillaStructure.Funded',
    //         'vwplantillaStructure.level',
    //         'vwplantillaStructure.Name1',
    //         'vwplantillaStructure.Pics',
    //         'vwplantillaStructure.Status',
    //         'vwplantillaStructure.Name4',
    //         'vwplantillaStructure.OfficeID',
    //         'vwActive.BirthDate',
    //         'vwActive.Designation',
    //         'yDesignation.Status',
    //         'yDesignation.PMID',
    //     ])->leftJoin('vwActive', 'vwplantillaStructure.ControlNo', '=', 'vwActive.ControlNo')->leftJoin('yDesignation', 'vwplantillaStructure.PositionID', '=', 'yDesignation.PMID')->distinct();
    public function index(Request $request)
    {
        $query = vwplantillastructure::select([
            'vwplantillaStructure.ControlNo',
            'vwplantillaStructure.ID',
            'vwplantillaStructure.office',
            'vwplantillaStructure.office2',
            'vwplantillaStructure.group',
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
            'vwplantillaStructure.Status as plantillaStatus',
            'vwplantillaStructure.Name4',
            'vwplantillaStructure.OfficeID',
            'vwActive.BirthDate',
            'vwActive.Designation',
            'yDesignation.Status as designationStatus',
            'yDesignation.PMID as designationPositionId',

        ])
            ->leftJoin('vwActive', 'vwplantillaStructure.ControlNo', '=', 'vwActive.ControlNo')
            ->leftJoin('yDesignation', 'vwplantillaStructure.PositionID', '=', 'yDesignation.PMID')

            ->distinct();

// Filter by office if provided: /plantilla?office=OfficeName
if ($office = $request->query('office')) {
            $query->where('vwplantillaStructure.office', $office);
        }

        $plantilla = $query->get();

        return response()->json($plantilla);
    }

    // office and rater on the modal rater mdoule
    public function fetchOfficeRater()
    {
        $data = vwplantillastructure::select([
            'vwplantillaStructure.ControlNo',
            'vwplantillaStructure.office',
            'vwplantillaStructure.OfficeID',
            'vwActive.BirthDate',
            'vwActive.Designation',
            'vwActive.Name4',

        ])
            ->leftJoin('vwActive', 'vwplantillaStructure.ControlNo', '=', 'vwActive.ControlNo')
            ->whereNotNull('vwplantillaStructure.ControlNo')
            ->get();
        return response()->json($data);
    }


    public function arrangement()
    {
        $data = vwofficearrangement::select(['Office'])->get();
        return response()->json($data);
    }


    public function vwActiveGet(Request $request)
    {
        // Returns employees; if ?office=OfficeName is provided, filter by Office name
        $query = vwActive::select(['ControlNo', 'Name4', 'Designation', 'BirthDate', 'Office']);

        if ($request->filled('office')) {
            $query->where('Office', $request->query('office'));
        }

        return response()->json($query->get());
    }


    public function service($ControlNo)
    {
        try {
            $data = xService::where('ControlNo', $ControlNo)
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
            $data = xService::select(['ControlNo', 'FromDate', 'ToDate', 'Designation', 'Status', 'Office', 'RateYear', 'RateDay', 'RateMon'])->latest('FromDate', 'ToDate')->limit(1) // specify columns from xService
                ->with([

                    'xPersonal' => function ($query) {
                        $query->select(['ControlNo', 'Surname', 'TINNo', 'Address']); // specify columns from vwplantillastructure
                    },
                    'active' => function ($query) {
                        $query->select(['ControlNo', 'Name4', 'Sex']); // specify columns from vwplantillastructure
                    },

                'posting_date' => function ($query) {
                    $query->select(['ControlNo', 'post_date', 'end_date']); // specify columns from vwplantillastructure
                },
                    'tempRegAppointments' => function ($query) {
                        $query->select([
                            'ControlNo',
                            'DesigCode',
                            'NewDesignation',
                            'Designation',
                            'SG',
                            'Step',
                            'Status',
                            'OffCode',
                            'NewOffice',
                            'Office',
                            'MRate',
                            'ItemNo',
                            'Pages',
                            'DivCode',
                            'SecCode',
                            'Official',
                            'Renew',
                            'StructureID',
                            'Groupcode',
                            'group',
                            'unitcode',
                            'sepcause',
                            'vicecause',
                            'sepdate'
                        ])->latest('sepdate')->limit(1); // specify columns from TempRegAppointmentReorg
                    },

                    'plantilla' => function ($query) {
                        $query->select(['ControlNo', 'office', 'office2', 'group', 'division', 'section', 'unit', 'position', 'ID', 'StructureID', 'OfficeID', 'OfficeID1', 'GroupID', 'DivisionID', 'SectionID', 'UnitID', 'PositionID', 'PageNo', 'ItemNo', 'SG', 'Ordr', 'Funded', 'groupordr', 'divordr', 'secordr', 'unitordr', 'level', 'Status']);
                    },
                    'tempRegAppointmentReorgExt' => function ($query) {
                        $query->select([
                            'ControlNo',
                            'PresAppro',
                            'PrevAppro',
                            'SalAuthorized',
                            'OtherComp',
                            'SupPosition',
                            'HSupPosition',
                            'Tool',
                            'Contact1',
                            'Contact2',
                            'Contact3',
                            'Contact4',
                            'Contact5',
                            'Contact6',
                            'ContactOthers',
                            'Working1',
                            'Working2',
                            'WorkingOthers',
                            'DescriptionSection',
                            'DescriptionFunction',
                            'StandardEduc',
                            'StandardExp',
                            'StandardTrain',
                            'StandardElig',
                            'Supervisor',
                            'Core1',
                            'Core2',
                            'Core3',
                            'Corelevel1',
                            'Corelevel2',
                            'Corelevel3',
                            'Corelevel4',
                            'Leader1',
                            'Leader2',
                            'Leader3',
                            'Leader4',
                            'leaderlevel1',
                            'leaderlevel2',
                            'leaderlevel3',
                            'leaderlevel4',
                            'structureid',
                        ]); // specify columns from TempRegAppointmentReorg
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
