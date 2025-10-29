<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\vwActive;
use App\Models\vwplantillastructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewActiveController extends Controller
{
    //

    public function fetch_all_employee()
    {
        $employee = vwplantillastructure::where('ControlNo', '001028')->get();
        return response()->json(['data' => $employee]);
    }

    public function getActiveCount()
    {
        $counts = DB::select("SELECT Status, COUNT(*) as count FROM vwActive GROUP BY Status");
        $result = [];

        foreach ($counts as $count) {
            $result[] = [
                'status' => $count->Status,
                'count' => $count->count
            ];
        }

        return response()->json(['data' => $result]);
    }

    public function getStatus(Request $request)

    {
        $status = $request->input('status');
        $results = DB::select("SELECT * FROM vwActive WHERE LOWER(Status) = LOWER(?)", [$status]);

        if (empty($results)) {
            return response()->json(['message' => 'No records found'], 404);
        }

        return response()->json(['data' => $results]);
    }

    // public function allCountStatus()
    // {
    //     $count = DB::select("SELECT COUNT(*) as total FROM vwActive")[0]->total;
    //     return response()->json(['total' => $count]);
    // }
    // public function allCountStatus()
    // {
    //     $count = DB::select("SELECT COUNT(*) as total FROM vwActive")[0]->total;
    //     return response()->json(['total' => $count]);
    // }


    public function allCountStatus()
    {
        $count = vwActive::whereNotNull('ControlNo')
            ->where('ControlNo', '!=', '')
            ->count();
        return response()->json(['total' => (string)$count]);
    }
    // public function getSexCount()
    // {
    //     $totalMale = DB::select("SELECT COUNT(*) as totalMale FROM vwActive WHERE SEX = 'MALE'")[0]->totalMale ?? 0;
    //     $totalFemale = DB::select("SELECT COUNT(*) as totalFemale FROM vwActive WHERE SEX = 'FEMALE'")[0]->totalFemale ?? 0;

    //     return response()->json([
    //         'totalMale' => (int)$totalMale,
    //         'totalFemale' => (int)$totalFemale
    //     ]);
    // }

    public function getSexCount()
    {
        $sexCounts = vwActive::select('Sex')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('Sex')
            ->pluck('total', 'Sex');

        // Normalize keys to uppercase in case your DB varies between 'MALE', 'Male', etc.
        $male = $sexCounts->get('MALE', 0) + $sexCounts->get('Male', 0) + $sexCounts->get('male', 0);
        $female = $sexCounts->get('FEMALE', 0) + $sexCounts->get('Female', 0) + $sexCounts->get('female', 0);

        return response()->json([
            'totalMale' => (string)$male,
            'totalFemale' => (string) $female
        ]);
    }

    public function plantilla_number()
    {
        $funded = vwplantillastructure::where('Funded', true)->count();
        $unfunded = vwplantillastructure::where('Funded', false)->count();
        $occupied = vwplantillastructure::where('Funded', true)
            ->whereNotNull('ControlNo')
            ->count();
        $unoccupied = vwplantillastructure::where('Funded', true)
            ->whereNull('ControlNo')
            ->count();
        $total = vwplantillastructure::count();

        return response()->json([
            'funded' => $funded,
            'unfunded' => $unfunded,
            'occupied' => $occupied,
            'unoccupied' => $unoccupied,
            'total' => $total,
        ]);
    }
}
