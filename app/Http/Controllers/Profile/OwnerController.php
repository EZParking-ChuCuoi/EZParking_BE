<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OwnerController extends Controller
{
    public function becomeSpaceOwner(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required',
            'areaType' => 'required',
            'imageCardId' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($id);

        $user->phone = $request->input('phone');
        $user->areaType = $request->input('areaType');

        if ($request->hasFile('imageCardId')) {
            $imageCardId = $request->file('imageCardId');
            $filename = time() . '_' . $imageCardId->getClientOriginalName();
            $imageCardId->storeAs('public/images', $filename);
            $user->imageCardId = $filename;
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }
}
