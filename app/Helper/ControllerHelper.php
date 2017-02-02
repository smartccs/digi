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

        public static function save_provider_service($provider_id,$service_type,$update = null) {

            if($update) {
                $provider_service = ProviderService::where('provider_id' , $provider_id)->where('service_type_id' , $service_type)->first();
            } else {
                $provider_service = new ProviderService;
            }   
            $provider_service->provider_id = $provider_id;
            $provider_service->service_type_id = $service_type;
            $provider_service->is_available = 1;
            $provider_service->save();

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

        public static function send_invoice($request_id,$page,$subject,$email) {

            if($requests = UserRequests::find($request_id)) {

                if($request_payment = RequestPayment::where('request_id' , $request_id)->first()) {

                    $user = User::find($requests->user_id);
                    $provider = Provider::find($requests->confirmed_provider);

                    $card_token = $customer_id = $last_four = "";

                    if($request_payment->payment_mode == CARD) {
                        if($user_card = Cards::find($user->default_card)) {
                            $card_token = $user_card->card_token;
                            $customer_id = $user_card->customer_id;
                            $last_four = $user_card->last_four;
                        }
                    }

                    $invoice_data = array();
                    $invoice_data['request_id'] = $requests->id;
                    $invoice_data['user_id'] = $requests->user_id;
                    $invoice_data['provider_id'] = $requests->confirmed_provider;
                    $invoice_data['provider_name'] = $provider->first_name." ".$provider->last_name;
                    $invoice_data['provider_address'] = $provider->address;
                    $invoice_data['user_name'] = $user->first_name." ".$user->last_name;
                    $invoice_data['user_address'] = $requests->s_address;
                    $invoice_data['base_price'] = $request_payment->base_price;
                    $invoice_data['other_price'] = 0;
                    $invoice_data['tax_price'] = $request_payment->tax_price;;
                    $invoice_data['total_time_price'] = $request_payment->time_price;
                    $invoice_data['sub_total'] = $request_payment->time_price+$request_payment->base_price;
                    $invoice_data['bill_no'] = $request_payment->payment_id;

                    $invoice_data['total_time'] = $request_payment->total_time;
                    $invoice_data['start_time'] = $requests->start_time;
                    $invoice_data['end_time'] = $requests->end_time;

                    $invoice_data['total'] = $request_payment->total;
                    $invoice_data['payment_mode'] = $request_payment->payment_mode;
                    $invoice_data['payment_mode_status'] = $request_payment->payment_mode ? 1 : 0;
                    $invoice_data['card_token'] = $card_token;
                    $invoice_data['customer_id'] = $customer_id;
                    $invoice_data['last_four'] = $last_four;

                    Helper::send_email($page,$subject,$email,$invoice_data);
                }
            }
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

        public static function sort_waiting_providers($merge_providers) {
            $waiting_array = array();
            $non_waiting_array = array();
            $check_waiting_provider_count = 0;

            foreach ($merge_providers as $key => $val) {
                if($val['waiting'] == 1) {
                    $waiting_array[] = $val['id'];
                    $check_waiting_provider_count ++;
                } else {
                    $non_waiting_array[] = $val['id'];
                }
            }

            $providers = array_unique(array_merge($non_waiting_array,$waiting_array));

            return array('providers' => $providers , 'check_waiting_provider_count' => $check_waiting_provider_count);
        
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

        public static function check_later_request($user,$requested_date,$flag) {
            $start_date = Helper::add_date($requested_date,"-1"); 
            $end_date = Helper::add_date($requested_date,"+2");

            $check_status = [REQUEST_NO_PROVIDER_AVAILABLE,REQUEST_TIME_EXCEED_CANCELLED,
                                REQUEST_CANCELLED,REQUEST_COMPLETED];

            $check_requests_query_base = UserRequests::where('user_id' , $user)
                                    ->whereNotIn('status' , $check_status)
                                    ->where('later',DEFAULT_TRUE);
            $check_request_query_time = $check_requests_query_base->whereBetween('requested_time' ,[$start_date,$end_date]);

            if($flag == DEFAULT_TRUE) {
                if($check_request_query_time->count() == 0) {
                    $date = date('Y-m-d H:i:s');
                    $current_start_date = Helper::add_date($date,"-1");
                    $current_end_date = Helper::add_date($date,"+2");
                    $check_requests = $check_requests_query_base->whereBetween('requested_time' ,[$current_start_date,$current_end_date])->count();
                } else {
                    $check_requests = $check_requests_query_base->count();
                }
            } else {
                $check_requests = $check_request_query_time->count(); 
            }

            return $check_requests;
        }

        public static function provider_availability_change($requested_time,$provider_id,$status) {

            $start_time = Helper::add_date($requested_time,"-1");
            $end_time = Helper::add_date($requested_time,"+2");

            $get_time_diff = Helper::time_diff($start_time,$end_time);
            $hours = $get_time_diff->h;
            $start_end = "";

            if($hours) {
                for($i=0;$i<$hours;$i++) {

                    if($start_end == ""){
                        $start_new = $start_time;   
                    }else{
                        $start_new = $start_end; 
                    }
                    $change_date = new \DateTime($start_new);
                    $change_date->modify("+1 hours");
                    $end_new = $change_date->format("Y-m-d H:i:s");

                    $start_end = $end_new;

                    $current_date = Helper::formatDate($end_new);
                    $start_time = Helper::formatHour($start_new);
                    $end_time = Helper::formatHour($end_new);

                    $check_availability = ProviderAvailability::where('provider_id',$provider_id)
                                    ->where('available_date',$current_date)
                                    ->where('start_time',$start_time)
                                    ->where('end_time',$end_time)
                                    ->first();
                    if($check_availability) {
                        $check_availability->status = $status;
                        $check_availability->save();
                    }
                }
            }
        
        }

        public static function assign_next_provider($request_id,$provider_id) {

            if($requests = UserRequests::find($request_id)) {

                RequestFilter::where('provider_id',$provider_id)
                    ->where('request_id', $request_id)
                    ->where('status', 0)
                    ->update(['status' => 1]);

                try {
                    $next_provider = RequestFilter::where('request_id', $request_id)
                        ->where('status', 0)
                        ->orderBy('id')
                        ->firstOrFail();

                    UserRequests::where('id', $request_id)->update([
                            'current_provider_id' => $next_provider->provider_id,
                            'assigned_at' => Carbon::now(),
                        ]);
                    
                } catch (ModelNotFoundException $e) {
                    UserRequests::where('id', $request_id)->update(['status' => 'CANCELLED']);

                    // No longer need request specific rows from RequestMeta
                    RequestFilter::where('request_id', '=', $request_id)->delete();
                }

            }        
        }
    }
