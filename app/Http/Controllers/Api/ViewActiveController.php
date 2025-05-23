<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViewActiveController extends Controller
{
    //
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
    public function allCountStatus()
    {
        $count = DB::select("SELECT COUNT(*) as total FROM vwActive")[0]->total;
        return response()->json(['total' => $count]);
    }
    public function getSexCount()
    {
        $totalMale = DB::select("SELECT COUNT(*) as totalMale FROM vwActive WHERE SEX = 'MALE'")[0]->totalMale ?? 0;
        $totalFemale = DB::select("SELECT COUNT(*) as totalFemale FROM vwActive WHERE SEX = 'FEMALE'")[0]->totalFemale ?? 0;

        return response()->json([
            'totalMale' => (int)$totalMale,
            'totalFemale' => (int)$totalFemale
        ]);
    }
}
