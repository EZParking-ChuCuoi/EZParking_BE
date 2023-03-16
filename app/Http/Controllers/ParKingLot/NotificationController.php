<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(10);
        return response()->json($notifications);
    }

    
}
