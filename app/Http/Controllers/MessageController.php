<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $uid = Auth::id();
        $withId = $request->query('with');
        $messages = [];
        if ($withId) {
            $query = Message::where(function ($q) use ($uid, $withId) {
                $q->where('Sender_ID', $uid)->where('Receiver_ID', $withId);
                $q->orWhere('Sender_ID', $withId)->where('Receiver_ID', $uid);
            })->orderBy('Sent_At')->take(200);

            if ($request->query('since')) {
                $query->where('Message_ID', '>', (int) $request->query('since'));
            }

            $messages = $query->get();

            if ($withId && !$request->query('ajax')) {
                Message::where('Sender_ID', $withId)
                    ->where('Receiver_ID', $uid)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }
        }
        $partners = Message::where('Sender_ID', $uid)
            ->orWhere('Receiver_ID', $uid)
            ->get()
            ->map(function ($m) use ($uid) {
                return $m->Sender_ID == $uid ? $m->Receiver_ID : $m->Sender_ID;
            })
            ->filter(function ($id) use ($uid) {
                return (int) $id !== (int) $uid;
            })
            ->unique()
            ->values();

        $conversations = [];
        foreach ($partners as $partnerId) {
            $user = User::find($partnerId);
            if (! $user) {
                continue;
            }

            $lastMessage = Message::where(function ($q) use ($uid, $partnerId) {
                $q->where('Sender_ID', $uid)->where('Receiver_ID', $partnerId);
                $q->orWhere('Sender_ID', $partnerId)->where('Receiver_ID', $uid);
            })->orderByDesc('Sent_At')->first();

            $unreadCount = Message::where('Sender_ID', $partnerId)
                ->where('Receiver_ID', $uid)
                ->whereNull('read_at')
                ->count();

            $conversations[] = [
                'user' => $user,
                'last_message_at' => $lastMessage?->Sent_At,
                'unread_count' => $unreadCount,
            ];
        }

        usort($conversations, function ($a, $b) {
            $ta = $a['last_message_at'] ? strtotime($a['last_message_at']) : 0;
            $tb = $b['last_message_at'] ? strtotime($b['last_message_at']) : 0;
            return $tb <=> $ta;
        });

        $users = $conversations;

        if ($request->query('ajax')) {
            return response()->json([
                'messages' => $messages,
                'last_id' => $messages->last()?->Message_ID ?? (int) $request->query('since', 0),
            ]);
        }

        return view('messages.index', compact('messages', 'withId', 'users'));
    }

    public function markUnread(Request $request, $id)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,User_ID'],
        ]);

        $uid = Auth::id();
        $partnerId = (int) $request->user_id;

        Message::where('Sender_ID', $partnerId)
            ->where('Receiver_ID', $uid)
            ->whereNotNull('read_at')
            ->update(['read_at' => null]);

        return back()->with('success', 'Conversation marked as unread.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'to_id' => ['required', 'integer', 'exists:users,User_ID'],
            'message' => ['required', 'string'],
        ]);

        $uid = Auth::id();
        $dup = Message::where('Sender_ID', $uid)
            ->where('Receiver_ID', $validated['to_id'])
            ->where('Message_Text', $validated['message'])
            ->where('Sent_At', '>=', now()->subSeconds(10))
            ->first();

        if ($dup) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $dup]);
            }
            return redirect()->route('messages.index', ['with' => $validated['to_id']]);
        }

        $message = Message::create([
            'Sender_ID' => $uid,
            'Receiver_ID' => $validated['to_id'],
            'Message_Text' => $validated['message'],
            'Sent_At' => now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('messages.index', ['with' => $validated['to_id']]);
    }
}
