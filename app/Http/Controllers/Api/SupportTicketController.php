<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    // GET /api/support-tickets
    public function index(): JsonResponse
    {
        $tickets = SupportTicket::with('latestMessage')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $tickets,
        ]);
    }

    // GET /api/support-tickets/{id}
    public function show($id): JsonResponse
    {
        $ticket = SupportTicket::with('messages')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $ticket,
        ]);
    }

    // POST /api/support-tickets
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject'  => 'required|string|max:255',
            'category' => 'required|in:loan,savings,account,general',
            'message'  => 'required|string|max:2000',
            'priority' => 'nullable|in:low,medium,high',
        ]);

        $ticket = SupportTicket::create([
            'user_id'  => Auth::id(),
            'subject'  => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'medium',
            'status'   => 'open',
        ]);

        // Create the first message
        SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(),
            'sender_type'       => 'customer',
            'message'           => $validated['message'],
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Support ticket created successfully',
            'data'    => $ticket->load('messages'),
        ], 201);
    }

    // POST /api/support-tickets/{id}/messages
    public function reply(Request $request, $id): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', Auth::id())
            ->whereNotIn('status', ['resolved', 'closed'])
            ->findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = SupportMessage::create([
            'support_ticket_id' => $ticket->id,
            'sender_id'         => Auth::id(),
            'sender_type'       => 'customer',
            'message'           => $request->message,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Reply sent successfully',
            'data'    => $message,
        ], 201);
    }

    // PUT /api/support-tickets/{id}/close
    public function close($id): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', Auth::id())
            ->findOrFail($id);

        $ticket->update(['status' => 'closed']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Ticket closed successfully',
        ]);
    }
}