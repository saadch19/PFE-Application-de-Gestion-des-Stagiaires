<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $tab = (string) $request->string('tab', 'inbox');
        $user = $request->user();

        $messages = Message::query()
            ->with(['sender', 'receiver'])
            ->when($tab === 'sent', fn ($query) => $query->where('sender_id', $user->id))
            ->when($tab !== 'sent', fn ($query) => $query->where('receiver_id', $user->id))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $unreadCount = Message::query()
            ->where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        return view('messages.index', compact('messages', 'tab', 'unreadCount'));
    }

    public function create(): View
    {
        $users = User::query()
            ->where('is_active', true)
            ->where('id', '!=', auth()->id())
            ->orderBy('full_name')
            ->get();

        return view('messages.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:180'],
            'body' => ['required', 'string'],
        ]);

        Message::query()->create([
            'sender_id' => $request->user()->id,
            'receiver_id' => (int) $validated['receiver_id'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        return redirect()->route('messages.index')->with('success', 'Message envoye.');
    }

    public function show(Message $message): View
    {
        $userId = auth()->id();

        if ($message->sender_id !== $userId && $message->receiver_id !== $userId) {
            abort(403, 'Acces au message interdit.');
        }

        if ($message->receiver_id === $userId && ! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        $message->load(['sender', 'receiver']);

        return view('messages.show', compact('message'));
    }

    public function markAsRead(Message $message): JsonResponse
    {
        if ($message->receiver_id !== auth()->id()) {
            abort(403, 'Acces interdit.');
        }

        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Message marque comme lu.']);
    }
}
