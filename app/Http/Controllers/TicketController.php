<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketLog;
use App\Models\Notification;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    private function createLog($ticketId, $userId, $action) {
        TicketLog::create([
            'ticket_id' => $ticketId,
            'user_id'   => $userId,
            'action'    => $action,
        ]);
    }

    public function dashboard(Request $request) {
        $user = $request->user();
        $query = Ticket::query();

        if ($user->role == 'user') {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total_tickets' => $query->count(),
            'open'          => (clone $query)->where('status', 'open')->count(),
            'in_progress'   => (clone $query)->where('status', 'in_progress')->count(),
            'closed'        => (clone $query)->where('status', 'closed')->count(),
        ];

        return response()->json(['status' => 'success', 'data' => $stats]);
    }

    public function index(Request $request) {
        $user = $request->user();
        $query = Ticket::with(['category', 'creator', 'assignedToUser']);

        if ($user->role == 'user') {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->latest()->get()
        ]);
    }

    public function store(Request $request) {
        if ($request->user()->role !== 'user') {
            return response()->json(['message' => 'Hanya User yang bisa membuat tiket'], 403);
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'required',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = $request->hasFile('image') 
            ? $request->file('image')->store('tickets', 'public') 
            : null;

        $ticket = Ticket::create([
            'user_id'     => $request->user()->id,
            'category_id' => $request->category_id,
            'title'       => $request->title,
            'description' => $request->description,
            'image'       => $imagePath,
            'status'      => 'open',
        ]);

        $this->createLog($ticket->id, $request->user()->id, "Tiket baru dibuat oleh User");

        return response()->json(['status' => 'success', 'message' => 'Tiket berhasil dibuat', 'data' => $ticket], 201);
    }

    public function updateStatus(Request $request, $id) {
        $user = $request->user();
        if (!in_array($user->role, ['admin', 'helpdesk'])) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $request->validate([
            'status' => 'required|in:open,in_progress,closed',
            'assigned_to' => 'nullable|exists:users,id'
        ]);

        $ticket = Ticket::findOrFail($id);
        $oldStatus = $ticket->status;
    
        $ticket->update([
            'status' => $request->status,
            'assigned_to' => $request->assigned_to ?? $ticket->assigned_to
        ]);

        $action = "Status diubah dari $oldStatus ke {$request->status}";
        $this->createLog($id, $user->id, $action);

        Notification::create([
            'user_id' => $ticket->user_id,
            'title' => 'Update Tiket',
            'message' => "Tiket #{$ticket->id} Anda kini berstatus {$request->status}",
            'type' => 'status_update',
            'related_id' => $ticket->id,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Status berhasil diperbarui']);
    }

    public function destroy(Request $request, $id) {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Hanya Admin yang bisa menghapus data'], 403);
        }

        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return response()->json(['status' => 'success', 'message' => 'Tiket berhasil dihapus']);
    }
}