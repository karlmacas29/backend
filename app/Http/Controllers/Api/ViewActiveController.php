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
}
