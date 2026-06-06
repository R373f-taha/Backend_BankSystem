<?php

namespace App\Http\Middleware;

use App\Models\Notification as ModelsNotification;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Notification
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id=$request->route('id');

        if(!$id){
        return $next($request);
        }

        $notification=ModelsNotification::find($id);

        if(!$notification){

            return response()->json(['message' => 'This notification doesn`t exist'],404);
        }

          if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'This notification is not for you😐😐'], 403);
        }
 return $next($request);

    }
}
