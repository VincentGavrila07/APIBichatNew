<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Hobby;
use App\Models\UserHobby;
use Illuminate\Support\Facades\Validator;


class ProfileController extends Controller
{
    public function showProfile()
    {
        $user = auth()->user();
        $user->photos = json_decode($user->photos, true); // Ubah ke array

        return response()->json($user);
    }


    public function update(Request $request)
    {
        $userId = $request->input('id');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'status' => 'nullable|in:single,taken,complicated',
            'faculty_id' => 'nullable|integer|exists:faculties,id',
            'major_id' => 'nullable|integer|exists:majors,id',
            'campus' => 'nullable|in:BINUS @Kemanggisan,BINUS @Alam Sutera,BINUS @Senayan,BINUS @Bekasi,BINUS @Bandung,BINUS @Malang,BINUS @Semarang',
            'description' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'string',
            'hobbies' => 'nullable|array',
            'hobbies.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (isset($validated['photos'])) {
            $oldPhotos = json_decode($user->photos, true) ?? [];
            $newPhotos = $validated['photos'];

            $photosToDelete = array_diff($oldPhotos, $newPhotos);
            foreach ($photosToDelete as $photo) {
                if (\Storage::disk('public')->exists($photo)) {
                    \Storage::disk('public')->delete($photo);
                }
            }

            $validated['photos'] = json_encode($newPhotos);
        } else {
            $validated['photos'] = $user->photos;
        }

        // Update data user kecuali hobbies
        $user->update(collect($validated)->except(['hobbies'])->toArray());

        // Sync hobbies jika ada
        if (isset($validated['hobbies'])) {
                // Anggap $validated['hobbies'] array of names
                $hobbyIds = Hobby::whereIn('name', $validated['hobbies'])->pluck('id')->toArray();
                $user->hobbies()->sync($hobbyIds);
            }


        $user->photos = json_decode($user->photos);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load('hobbies'), // return user lengkap dengan hobbies
        ]);
    }




    public function uploadPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $photo = $request->file('photo');
        $path = $photo->store('photos', 'public');

        return response()->json(['path' => $path], 201);
    }



}
