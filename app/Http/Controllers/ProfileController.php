<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return response()->json([
        'name' => $user->name,
        'email' => $user->email,
        'birthdate' => $user->birthdate,
        'gender' => $user->gender,
        'status' => $user->status,
        'description' => $user->description,
        'photos' => $user->photos ?? [],
    ]);
    }
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'birthdate' => 'nullable|string',
                'gender' => 'nullable|string',
                'status' => 'nullable|string',
                'description' => 'nullable|string',
                'photos' => 'nullable|array',
                'campus' => 'nullable|string',
                'faculty' => 'nullable|string',
                'major' => 'nullable|string',
            ]);

            // Contoh update langsung user
            $user->update($validated);

            return response()->json([
                'message' => 'Profile saved',
                'profile' => $user,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save profile: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save profile'], 500);
        }
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'faculty_id' => 'nullable|exists:faculties,id',
            'major_id' => 'nullable|exists:majors,id',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female',
            'status' => 'nullable|string|in:single,taken,complicated',
            'description' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.*' => 'string',
            'campus' => 'nullable|in:BINUS @Kemanggisan,BINUS @Alam Sutera,BINUS @Senayan,BINUS @Bekasi,BINUS @Bandung,BINUS @Malang,BINUS @Semarang',
            
        ]);

        \Log::info('Profile update data:', $data);

        $user->update($data);

        \Log::info('User after update:', $user->toArray());

        return response()->json($user);
    }
    // public function update(Request $request)
    // {
    // $user = auth()->user();

    // // Validasi minimal
    // $request->validate([
    //     'faculty_id' => 'nullable|exists:faculties,id',
    //     'major_id' => 'nullable|exists:majors,id',
    //     'name' => 'required|string|max:255',
    //     'gender' => 'required|in:male,female',
    //     'description' => 'nullable|string|max:1000',
    //     'password' => 'nullable|string|min:8',
    //     'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp',
    //     'birthdate' => 'nullable|date',
    //     'status' => 'nullable|in:single,taken,complicated',
    //     'campus' => 'nullable|in:BINUS @Kemanggisan,BINUS @Alam Sutera,BINUS @Senayan,BINUS @Bekasi,BINUS @Bandung,BINUS @Malang,BINUS @Semarang',


    // ]);

    // // Update nama
    // $user->name = $request->name;
    // $user->gender = $request->gender;
    // $user->description = $request->description;
    // $user->birthdate = $request->birthdate;
    // $user->status = $request->status;
    // $user->campus = $request->campus;
    // $user->faculty_id = $request->faculty_id;
    // $user->major_id = $request->major_id;


    // // Update password jika diisi
    // if ($request->filled('password')) {
    //     $user->password = Hash::make($request->password);
    // }

    // // Ambil array foto sebelumnya
    // $photos = $user->photos ?? [];

    // // Ganti hanya index foto yang dikirim di form
    // if ($request->hasFile('photos')) {
    //     foreach ($request->file('photos') as $index => $file) {
    //         if ($file) {
    //             // Simpan file baru
    //             $path = $file->store('profile_photos', 'public');

    //             // Hapus file lama jika ada
    //             if (isset($photos[$index])) {
    //                 Storage::disk('public')->delete($photos[$index]);
    //             }

    //             // Replace index terkait
    //             $photos[$index] = $path;
    //         }
    //     }
    // }

    // // Pastikan maksimal 4 foto
    // $photos = array_slice($photos, 0, 4);
    // $user->photos = $photos;

    // $user->save();

    // session()->flash('success', 'Profile edited!');

    // return response()->json([
    // 'message' => 'Profile updated successfully',
    // 'user' => $user,
    // ]);
    // }
}
