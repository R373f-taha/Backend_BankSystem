<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function index()
{
    $userId = Auth::user()->id;

    $notifications = NotificationService::getUserNotifications($userId);
    $unreadCount = NotificationService::unreadCount($userId);

    if ($notifications->isEmpty()) {
        return response()->json([
            'status' => 'success',
            'message' => 'No notifications are currently available.😒😒',
            'notifications' => [], //
            'unread_count' => 0,
        ], 200);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'All notifications retrived successfully ✅✅',
        'notifications' => $notifications,
        'unread_count' => $unreadCount,
    ], 200);
}

   public function markAsRead($notifyId){

        NotificationService::markAsRead($notifyId);

        return response()->json(['message' =>'Notification marked as read']);
   }

   public function unReadCount(){

     $count=NotificationService::unReadCount(Auth::user()->id);

       return response()->json(['count' => $count]);
   }

   public function markAllAsRead(){

     $userId=Auth::user()->id;

     Notification::where('user_id',$userId)
     ->where('is_read',false)
     ->update(['is_read'=>true]);

     return response()->json(['message' => 'All Notification marked as read']);


   }

   public function destroy($notifyId){

   Notification::where('id',$notifyId)->delete();

   return response()->json(['message' => 'The notification has been deleted.']);
   }

}
