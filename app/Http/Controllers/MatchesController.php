<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Matches;
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
    public function getMatches(Request $request)
    {
        $userId = $request->query('user_id');
        // Ambil semua match yang saling suka (is_mutual = 1)
        $matches = Matches::with('likedUser')
            ->where('user_id', $userId)
            ->where('is_mutual', 1)
            ->get()
            ->map(function ($match) {
                return [
                    'id' => $match->likedUser->id,
                    'name' => $match->likedUser->name,
                    'photos' => $match->likedUser->photos,
                ];
            });

        return response()->json($matches);
    }

    public function showMatches(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $preferredGender = $user->preferred_gender;
        $preferredCampus = $user->preferred_campus;

        $interactedUserIds = Matches::where('user_id', $userId)
            ->pluck('liked_user_id')
            ->toArray();

        $candidate = User::with(['faculty', 'major'])
            ->where('id', '!=', $userId)
            ->whereNotIn('id', $interactedUserIds)
            ->when($preferredGender, fn($query) => $query->where('gender', $preferredGender))
            ->when($preferredCampus, fn($query) => $query->where('campus', $preferredCampus))
            ->whereNotNull('photos')
            ->inRandomOrder()
            ->first();

        if (!$candidate) {
            return response()->json(null);
        }

        return response()->json([
            'id' => $candidate->id,
            'name' => $candidate->name,
            'birthdate' => $candidate->birthdate,
            'description' => $candidate->description,
            'photos' => $candidate->photos,
            'faculty' => $candidate->faculty ? $candidate->faculty->name : null,
            'major' => $candidate->major ? $candidate->major->name : null,
            'campus' => $candidate->campus,
            'gender' => $candidate->gender,
        ]);
    }




    public function likeUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'liked_user_id' => 'required|exists:users,id',
        ]);

        $existing = Matches::where('user_id', $request->user_id)
            ->where('liked_user_id', $request->liked_user_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Already liked'], 200);
        }

        // Cek apakah liked_user_id sudah menyukai user_id
        $reverseMatch = Matches::where('user_id', $request->liked_user_id)
            ->where('liked_user_id', $request->user_id)
            ->first();

        $isMutual = $reverseMatch ? 1 : 0;

        // Simpan like baru
        Matches::create([
            'user_id' => $request->user_id,
            'liked_user_id' => $request->liked_user_id,
            'is_mutual' => $isMutual,
        ]);

        // Jika match mutual, update juga reverse record-nya
        if ($reverseMatch && !$reverseMatch->is_mutual) {
            $reverseMatch->is_mutual = 1;
            $reverseMatch->save();
        }

        return response()->json(['message' => $isMutual ? 'It’s a match!' : 'Liked successfully']);
    }

    public function dislikeUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'disliked_user_id' => 'required|exists:users,id',
        ]);

        // Cek apakah sudah pernah dislike sebelumnya
        $existing = Matches::where('user_id', $request->user_id)
            ->where('liked_user_id', $request->disliked_user_id)
            ->first();

        if (!$existing) {
            Matches::create([
                'user_id' => $request->user_id,
                'liked_user_id' => $request->disliked_user_id,
                'is_mutual' => 0, // bukan mutual
            ]);
        }

        return response()->json(['message' => 'User skipped']);
    }
    public function getNewMatches(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json(['message' => 'User ID is required'], 400);
        }

        // Ambil matches mutual yang belum diberi notifikasi
        $newMatches = Matches::with('likedUser')
            ->where('user_id', $userId)
            ->where('is_mutual', 1)
            ->where('notified', false)
            ->get();

        return response()->json($newMatches->map(function ($match) {
            return [
                'match_id' => $match->id,
                'id' => $match->likedUser->id,
                'name' => $match->likedUser->name,
                'photos' => $match->likedUser->photos,
            ];
        }));
    }

    // Method untuk update kolom notified jadi true setelah notifikasi muncul
    public function markMatchesNotified(Request $request)
    {
        $matchIds = $request->input('match_ids');

        if (!is_array($matchIds) || empty($matchIds)) {
            return response()->json(['message' => 'Match IDs required'], 400);
        }

        Matches::whereIn('id', $matchIds)
            ->update(['notified' => true]);

        return response()->json(['message' => 'Matches marked as notified']);
    }

}
