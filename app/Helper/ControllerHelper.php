<?php 

namespace App\Helpers;

use File;

class Helper
{

    public static function upload_avatar($avatar) {
        $file_name = time();
        $file_name .= rand();
        $file_name = sha1($file_name);
        if ($avatar) {
            $ext = $avatar->getClientOriginalExtension();
            $avatar->move(public_path() . "/uploads/avatar", $file_name . "." . $ext);
            $avatar_url = url('/') . '/uploads/avatar/'.$file_name . "." . $ext;

            return $avatar_url;
        } else {
            return "";
        }
    }

    public static function delete_avatar($avatar) {
        File::delete( public_path() . "/uploads/avatar/" . basename($avatar));
        return 'Deleted!';
    }

    public static function upload_picture($picture)
    {
        $file_name = time();
        $file_name .= rand();
        $file_name = sha1($file_name);
        if ($picture) {
            $ext = $picture->getClientOriginalExtension();
            $picture->move(public_path() . "/uploads", $file_name . "." . $ext);
            $local_url = $file_name . "." . $ext;

            $s3_url = url('/').'/uploads/'.$local_url;
            
            return $s3_url;
        }
        return "";
    }


    public static function delete_picture($picture) {
        File::delete( public_path() . "/uploads/" . basename($picture));
        return true;
    }

}
