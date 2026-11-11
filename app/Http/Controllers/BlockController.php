<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    public function block(Request $request, $id)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,User_ID'],
        ]);

        $blockedId = (int) $request->user_id;
        $uid = Auth::id();

        if ($blockedId === $uid) {
            return back()->with('error', 'You cannot block yourself.');
        }

        if (! User::find($blockedId)) {
            return back()->with('error', 'User not found.');
        }

        Block::firstOrCreate([
            'Blocker_ID' => $uid,
            'Blocked_User_ID' => $blockedId,
        ]);

        return back()->with('success', 'User blocked.');
    }

    public function unblock(Request $request, $id)
    {
        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,User_ID'],
        ]);

        $blockedId = (int) $request->user_id;
        $uid = Auth::id();

        Block::where('Blocker_ID', $uid)
            ->where('Blocked_User_ID', $blockedId)
            ->delete();

        return back()->with('success', 'User unblocked.');
    }
}
