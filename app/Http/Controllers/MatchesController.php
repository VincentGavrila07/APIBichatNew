<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
class MatchesController extends Controller
{
     // Simpan preferensi user (gender & kampus)
    public function updatePreferences(Request $request, $id)
    {
        $request->validate([
            'preferred_gender' => 'nullable|in:male,female',
            'preferred_campus' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $user->preferred_gender = $request->preferred_gender;
        $user->preferred_campus = $request->preferred_campus;
        $user->save();

        return response()->json([
            'message' => 'Preferences updated successfully',
            'user' => $user
        ]);
    }

    // Ambil calon matches berdasarkan preferensi user
    public function getMatches($id)
    {
        $user = User::findOrFail($id);

        $matches = User::where('id', '!=', $user->id)
            ->when($user->preferred_gender, function ($query) use ($user) {
                $query->where('gender', $user->preferred_gender);
            })
            ->when($user->preferred_campus, function ($query) use ($user) {
                $query->where('campus', $user->preferred_campus);
            })
            ->select('id', 'name', 'gender', 'campus', 'photos', 'description')
            ->get();

        return response()->json($matches);
    }
    public function showMatches(Request $request){
        $userId = $request->input('user_id'); // untuk POST
        // atau $userId = $request->query('user_id'); // untuk GET

        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $preferredGender = $user->preferred_gender;
        $preferredCampus = $user->preferred_campus;

        $matches = User::where('id', '!=', $user->id)
            ->when($preferredGender, function ($query, $preferredGender) {
                return $query->where('gender', $preferredGender);
            })
            ->when($preferredCampus, function ($query, $preferredCampus) {
                return $query->where('campus', $preferredCampus);
            })
            ->whereNotNull('photos')
            ->inRandomOrder()
            ->first();

        return response()->json($matches);
    }
}
