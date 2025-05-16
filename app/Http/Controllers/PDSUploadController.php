<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PdsImport;
use Illuminate\Support\Str;

class PDSUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            // Import the Excel file
            Excel::import(new PdsImport(), $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'PDS uploaded successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error uploading PDS: ' . $e->getMessage()
            ], 500);
        }
    }
}
