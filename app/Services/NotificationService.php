<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService{

public static function add($userId,$title,$message){

return Notification::create([
    'user_id'=>$userId,
    'title'=>$title,
    'message'=>$message
]);
}

public static function getUserNotifications($userId){

return Notification::where('user_id',$userId)
          ->orderBy('created_at','desc')
          ->get();
}

public static function unReadNotifications($userId){

return Notification::where('user_id',$userId)
          ->where('is_read',false)
          ->get();

}

public static function unReadCount($userId){

return Notification::where('user_id',$userId)
          ->where('is_read',false)
          ->count();

}

public static function  markAsRead($notificationId){

return  Notification::where('id',$notificationId)
          ->update(['is_read'=>true]);

}
}
