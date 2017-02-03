<?php 

namespace App\Helpers;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Carbon\Carbon;

use App\User;
use App\Cards;
use App\Provider;
use App\ServiceType;
use App\UserRequests;
use App\RequestFilter;
use App\RequestPayment;
use App\ProviderService;
use App\ProviderAvailability;

use Log;
use File;
use Hash;
use Mail;

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

    public static function send_ios_push($user_id, $title, $message, $type)
    {
        require_once app_path().'/ios/apns.php';

        $msg = array("alert" => "" . $title,
            "status" => "success",
            "title" => $title,
            "message" => $message,
            "badge" => 1,
            "sound" => "default");

        if (!isset($user_id) || empty($user_id)) {
            $deviceTokens = array();
        } else {
            $deviceTokens = $user_id;
        }

        $apns = new \Apns();
        $apns->send_notification($deviceTokens, $msg);

        Log::info($deviceTokens);
    }   

    public static function send_android_push($user_id, $title ,$message)
    {
        require_once app_path().'/gcm/GCM_1.php';
        require_once app_path().'/gcm/const.php';

        if (!isset($user_id) || empty($user_id)) {
            $registatoin_ids = "0";
        } else {
            $registatoin_ids = trim($user_id);
        }
        if (!isset($message) || empty($message)) {
            $msg = "Message not set";
        } else {
            $msg = $message;
        }
        if (!isset($title) || empty($title)) {
            $title1 = "Message not set";
        } else {
            $title1 = trim($title);
        }

        $message = array(TEAM => $title1, MESSAGE => $msg);

        $gcm = new \GCM();
        $registatoin_ids = array($registatoin_ids);
        $gcm->send_notification($registatoin_ids, $message);

    }


    public static function time_diff($start,$end) {
        $start_date = new \DateTime($start);
        $end_date = new \DateTime($end);

        $time_interval = date_diff($start_date,$end_date);
        return $time_interval;

    }

    public static function formatHour($date) {
        $hour_time  = date("H:i:s",strtotime($date));
        return $hour_time;
    }

    public static function formatDate($date) {
        $newdate  = date("Y-m-d",strtotime($date));
        return $newdate;
    }

    public static function add_date($date,$no_of_days) {

        $change_date = new \DateTime($date);
        $change_date->modify($no_of_days." hours");
        $change_date = $change_date->format("Y-m-d H:i:s");
        return $change_date;
    }

}
