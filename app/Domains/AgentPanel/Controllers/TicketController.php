<?php

use App\Domains\Shared\Models\Ticket;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketController extends Controller
{
// GET /api/v1/agent/tickets
    public function index(Request $request)
    {
        $this->authorize('agent-access'); // با Spatie permission

        $tickets = Ticket::with(['user', 'conversation', 'assignedAgent'])
            ->filter($request->all())
            ->paginate(20);

        return response()->json([...]);
    }

// POST /api/v1/agent/tickets/{ticket}/claim
    public function claim(Ticket $ticket)
    {
        $ticket->update(['assigned_agent_id' => auth()->id(), 'status' => 'in_progress']);
        return response()->json($ticket);
    }
}
