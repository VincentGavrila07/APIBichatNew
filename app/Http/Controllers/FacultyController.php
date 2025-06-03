<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faculty;

class FacultyController extends Controller
{
    public function getAllFaculty(){
        $faculties = Faculty::all();    
        return response()->json($faculties, 200);
    }
}
