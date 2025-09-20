<?php
namespace App\Domains\UserPanel\Controllers;

use App\Domains\Shared\Services\PythonAIService;
use App\Domains\UserPanel\Requests\SendMessageRequest;
use App\Domains\Shared\Models\Conversation;
use App\Domains\Shared\Models\Message;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Request;

class ConversationController
{
    protected $aiService;

    public function __construct(PythonAIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index(Request $request)
    {
        $query = Conversation::query();
        if (!$request->user()) {
            $query->where('device_id', $request->header('X-Device-Fingerprint'));
        } else {
            $query->where('user_id', $request->user()->id);
        }
        return QueryBuilder::for($query)
            ->allowedSorts(['created_at'])
            ->cursorPaginate();
    }

    public function messages($id)
    {
        return QueryBuilder::for(Message::class)
            ->where('conversation_id', $id)
            ->allowedFilters(['role' => 'user,ai,agent'])
            ->cursorPaginate();
    }

    public function sendMessage(SendMessageRequest $request, $id)
    {
        $aiResponse = $this->aiService->sendToAI($request->content, $id);
        $message = Message::create([
            'conversation_id' => $id,
            'role' => 'user',
            'content' => $request->content,
            'visibility' => 'public',
            'attachments' => $request->attachments ?? []
        ]);
        $aiMessage = Message::create([
            'conversation_id' => $id,
            'role' => 'ai',
            'content' => $aiResponse,
            'visibility' => 'public'
        ]);
        event(new \App\Domains\Shared\Events\MessageSent($id, $message, $aiMessage));
        return ['response' => $aiResponse, 'message_id' => $message->id];
    }

    public function handoff($id)
    {
        $ticket = \App\Domains\Shared\Models\Ticket::create(['conversation_id' => $id]);
        return ['ticket_id' => $ticket->id];
    }
}
