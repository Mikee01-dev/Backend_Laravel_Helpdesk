<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, $ticketId)
    {
        $user = $request->user();
        $ticket = Ticket::findOrFail($ticketId);

        if ($user->role == 'user' && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $comments = Comment::with('user')
            ->where('ticket_id', $ticketId)
            ->oldest() 
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $comments
        ]);
    }

    public function store(Request $request, $ticketId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $user = $request->user();
        $ticket = Ticket::findOrFail($ticketId);

        if ($user->role == 'user' && $ticket->user_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $comment = Comment::create([
            'ticket_id' => $ticketId,
            'user_id'   => $user->id,
            'message'   => $request->message,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Komentar berhasil dikirim',
            'data' => $comment->load('user')
        ], 201);
    }
}