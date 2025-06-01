<?php

namespace App\Http\Controllers;

use App\Models\Messages;
use App\Models\Matches;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    // Mengirim pesan baru
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = Messages::create([
            'sender_id' => $request->sender_id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
        ]);

        return response()->json(['message' => 'Message sent', 'data' => $message], 201);
    }

    // Mendapatkan chat antara 2 user (berdasarkan sender dan receiver)
    public function getMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user1_id' => 'required|exists:users,id',
            'user2_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $messages = Messages::where(function ($query) use ($request) {
            $query->where('sender_id', $request->user1_id)
                ->where('receiver_id', $request->user2_id);
        })->orWhere(function ($query) use ($request) {
            $query->where('sender_id', $request->user2_id)
                ->where('receiver_id', $request->user1_id);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        return response()->json($messages);
    }

    // Mendapatkan data current user dan matched user berdasarkan mutual match
    public function getMatchedUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_user_id' => 'required|exists:users,id',
            'matched_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $currentUserId = $request->current_user_id;
        $matchedUserId = $request->matched_user_id;

        // Cek apakah ada mutual match antara currentUser dan matchedUser
        $match = Matches::where(function ($query) use ($currentUserId, $matchedUserId) {
            $query->where('user_id', $currentUserId)->where('liked_user_id', $matchedUserId);
        })->where('is_mutual', 1)->first();

        if (!$match) {
            return response()->json(['error' => 'No mutual match found'], 404);
        }

        $currentUser = User::find($currentUserId);
        $matchedUser = User::find($matchedUserId);

        return response()->json([
            'currentUser' => $currentUser,
            'matchedUser' => $matchedUser,
        ]);
    }
    public function conversations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = $request->user_id;

        // Cari semua user yang sudah saling chat dengan $userId (sender atau receiver)
        // Bisa kita ambil distinct user yang berinteraksi dengan $userId
        $messageUserIds = Messages::where('sender_id', $userId)
            ->pluck('receiver_id')
            ->merge(
                Messages::where('receiver_id', $userId)->pluck('sender_id')
            )
            ->unique()
            ->values();

        // Ambil data user matched (chat partner)
        $matchedUsers = User::whereIn('id', $messageUserIds)->get();

        // Untuk setiap matched user, kita juga ingin menampilkan pesan terakhir dan waktu pesan terakhir
        $conversations = $matchedUsers->map(function ($matchedUser) use ($userId) {
            $lastMessage = Messages::where(function ($query) use ($userId, $matchedUser) {
                $query->where('sender_id', $userId)->where('receiver_id', $matchedUser->id);
            })->orWhere(function ($query) use ($userId, $matchedUser) {
                $query->where('sender_id', $matchedUser->id)->where('receiver_id', $userId);
            })->orderBy('created_at', 'desc')->first();

            return [
                'id' => $lastMessage?->id ?? null,
                'matchedUser' => $matchedUser,
                'lastMessage' => $lastMessage?->message ?? null,
                'lastMessageTime' => $lastMessage?->created_at?->toDateTimeString() ?? null,
            ];
        });

        return response()->json($conversations);
    }

}
