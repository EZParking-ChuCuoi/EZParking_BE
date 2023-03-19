<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/notifications/{userId}", tags={"Notification"}, 
     *  summary="get all notification with id user",  
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="id user",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(15)
        ->map(function ($notification) {
            $notification->data = json_decode($notification->data, true);
            return $notification;
        });
        return response()->json($notifications);
    }

    
}
