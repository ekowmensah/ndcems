<?php

namespace App\Http\Controllers\Operations;

use App\Model\OperationalNotification;
use App\Services\Operations\OperationalNotificationService;
use Illuminate\Support\Facades\Auth;

class NotificationController extends OperationsController
{
    public function index(OperationalNotificationService $service)
    {
        $service->syncFor(Auth::user());
        $notifications = OperationalNotification::where('recipient_user_id', Auth::id())
            ->whereNull('dismissed_at')->latest('id')->paginate(30);
        $unread = OperationalNotification::where('recipient_user_id', Auth::id())
            ->whereNull('read_at')->whereNull('dismissed_at')->count();
        return view('operations.notifications', compact('notifications', 'unread') + $this->viewContext());
    }

    public function read(int $id)
    {
        OperationalNotification::where('recipient_user_id', Auth::id())->findOrFail($id)->update(['read_at' => now()]);
        return redirect()->back();
    }

    public function readAll()
    {
        OperationalNotification::where('recipient_user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);
        return redirect()->back()->with('message', 'All notifications marked as read.');
    }
}
