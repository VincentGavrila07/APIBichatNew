<?php

namespace App\Http\Controllers;

use App\Models\Messages;
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
}
