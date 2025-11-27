<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;
use App\Models\JobBatchesRsp;
use App\Models\vwplantillastructure;
use Illuminate\Container\Attributes\Auth;

class DashboardController extends Controller
{
    //

    public function index() // this for status
    {
        $qualified = Submission::where('status', 'qualified')->count();
        $pending = Submission::where('status', 'pending')->count();
        $unqualified = Submission::where('status', 'unqualified')->count();
        $total = Submission::count();

        return response()->json([
            'qualified' => $qualified,
            'pending' => $pending,
            'unqualified' => $unqualified,
            'total' => $total,
        ]);
    }

    public function plantillaNumber()
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
