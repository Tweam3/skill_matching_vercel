<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $uid = Auth::id();
        $notifications = Notification::where('User_ID', $uid)->latest('Created_At')->get();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('User_ID', Auth::id())->where('Notif_ID', $id)->firstOrFail();
        $notification->update(['Status' => 'Read']);

        return back()->with('success', 'Notification marked as read.');
    }

    public function readAndRedirect($id)
    {
        $notification = Notification::where('User_ID', Auth::id())->where('Notif_ID', $id)->firstOrFail();
        
        if ($notification->Status === 'Unread') {
            $notification->update(['Status' => 'Read']);
        }
        
        $redirectUrl = $notification->url ?? route('notifications.index');
        return redirect($redirectUrl);
    }
}
