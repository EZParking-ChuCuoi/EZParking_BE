<?php

namespace App\Http\Controllers\Clound;

use App\Http\Controllers\Controller;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;

class CloudinaryStorage extends Controller
{
    private const folder_path = 'account';

    public static function path($path){
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public static function upload($image, $filename){
        $newFilename = str_replace(' ', '_', $filename);
        $public_id = date('Y-m-d_His').'_'.$newFilename;
        $result =Cloudinary::upload($image, [
            "public_id" => self::path($public_id),
            "folder"    => self::folder_path
        ])->getSecurePath();

        return $result;
    }

    public static function replace($path, $image, $public_id){
        self::delete($path);
        return self::upload($image, $public_id);
    }

    public static function delete($path){
        $public_id = self::folder_path.'/'.self::path($path);
        return Cloudinary::destroy($public_id);
    }
}
