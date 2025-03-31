<?php

namespace App\Http\Controllers;
use App\Models\Employee;


class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return response()->json($employees);
    }
}
?>
