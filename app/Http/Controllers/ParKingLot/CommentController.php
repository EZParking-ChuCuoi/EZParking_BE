<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * @OA\Post(
     ** path="/api/comments", tags={"Comments"}, 
     *  summary="create comment", operationId="storeComment",
     *  @OA\Parameter(name="userId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *  @OA\Parameter(name="parkingId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *  @OA\Parameter(name="content",in="query",required=true,example="good", @OA\Schema( type="string" )),
     *  @OA\Parameter(name="ranting",in="query",required=true,example=2, @OA\Schema( type="integer" )),

     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer|exists:users,id',
            'parkingId' => 'required|integer|exists:parking_lots,id',
            'content' => 'required|string',
            'ranting' => 'required|integer|min:1|max:5',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $comment = new Comment();
        $comment->userId = $request->userId;
        $comment->parkingId = $request->parkingId;
        $comment->content = $request->content;
        $comment->ranting = $request->ranting;
        $comment->save();

        return response()->json($comment, 201);
    }
}
