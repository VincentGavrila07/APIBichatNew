<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Major;

class MajorController extends Controller
{
    public function getAllMajor() {
        $majors = Major::with('faculty')->get();
        return response()->json($majors, 200);
    }
}
