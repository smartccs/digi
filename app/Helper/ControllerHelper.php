<?php 

   namespace App\Helpers;

   use Hash;

   use App\Admin;

   use App\User;
   use App\Provider;
   use App\Cards;
   use App\FavouriteProvider;
   use App\ProviderService;
   use App\Requests;
   use App\RequestsMeta;
   use App\RequestPayment;
   use App\Settings;
   use App\ServiceType;
   use App\ProviderRating;
   use App\ProviderAvailability;
   use App\Jobs\sendPushNotification;
   use App\Jobs\NormalPushNotification;

   use Mail;

   use File;

   use Log;

    class Helper 
    {
        public static function tr($key) {

            if (!\Session::has('locale'))
                \Session::put('locale', \Config::get('app.locale'));
            return \Lang::choice('messages.'.$key, 0, Array(), \Session::get('locale'));

        }

        public static function clean($string)
        {
            $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

            return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        }

        public static function web_url()
        {
            return url('/');
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

        // Note: $error is passed by reference
        public static function is_token_valid($entity, $id, $token, &$error)
        {
            if (
                ( $entity== 'USER' && ($row = User::where('id', '=', $id)->where('token', '=', $token)->first()) ) ||
                ( $entity== 'PROVIDER' && ($row = Provider::where('id', '=', $id)->where('token', '=', $token)->first()) )
            ) {
                if ($row->token_expiry > time()) {
                    // Token is valid
                    $error = NULL;
                    return $row;
                } else {
                    $error = array('success' => false, 'error' => Helper::get_error_message(103), 'error_code' => 103);
                    return FALSE;
                }
            }
            $error = array('success' => false, 'error' => Helper::get_error_message(104), 'error_code' => 104);
            return FALSE;
        }

        public static function send_user_welcome_email($provider)
        {
            $email = $provider->email;

            $subject = "Welcome to 5damati";

            $email_data = $provider;

            if(env('MAIL_USERNAME') && env('MAIL_PASSWORD')) {
                try
                {
                    $site_url=url('/');
                    Mail::send('emails.user.welcome', array('email_data' => $email_data,'site_url'=>$site_url), function ($message) use ($email, $subject) {
                            $message->to($email)->subject($subject);
                    });
                } catch(Exception $e) {
                    return Helper::get_error_message(123);
                }
                return Helper::get_message(105);
            } else {
                return Helper::get_error_message(123);
            }
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

                $s3_url = Helper::web_url().'/uploads/'.$local_url;
                
                return $s3_url;
            }
            return "";
        }

        // Convert all NULL values to empty strings
        public static function null_safe($arr)
        {
            $newArr = array();
            foreach ($arr as $key => $value) {
                $newArr[$key] = ($value == NULL) ? "" : $value;
            }
            return $newArr;
        }

        public static function generate_token()
        {
            return Helper::clean(Hash::make(rand() . time() . rand()));
        }

        public static function generate_token_expiry()
        {
            return time() + 24*3600*30;  // 30 days
        }

        public static function send_email($page,$subject,$email,$email_data)
        {       
            if(env('MAIL_USERNAME') && env('MAIL_PASSWORD')) {
                try
                {

                    $site_url=url('/');
                    Mail::queue($page, array('email_data' => $email_data,'site_url' => $site_url), function ($message) use ($email, $subject) {

                            $message->to($email)->subject($subject);
                    });
                } catch(Exception $e) {
                    return Helper::get_error_message(123);
                }
                return Helper::get_message(105);
            } else {
                return Helper::get_error_message(123);
            }
        }

        public static function send_invoice($request_id,$page,$subject,$email) {

            if($requests = Requests::find($request_id)) {

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

        public static function get_emails($status,$user_id,$provider_id) {

            $email = array();

            $user = User::find($user_id);
            $provider = Provider::find($provider_id);
            if($status == 3) {
                $admin_email = "";
                if($admin = Admin::first()) {
                    $admin_email = $admin->email;
                }
                $email = array($admin_email,$user->email,$provider->email);
            } else {
                $email = array($user->email,$provider->email);
            }

            return $email;
        }

        public static function send_users_welcome_email($email_data)
        {
            $email = $email_data['email'];
            $email_data = $email_data;

            $subject = "Welcome on Board";

        
            if(env('MAIL_USERNAME') && env('MAIL_PASSWORD')) {
                try
                {
                    Log::info("Provider welcome mail started.....");

                    Mail::send('emails.user.welcome', array('email_data' => $email_data), function ($message) use ($email, $subject) {
                            $message->to($email)->subject($subject);
                    });

                } catch(Exception $e) {

                    Log::info('Email send error message***********'.print_r($e,true));

                    return Helper::get_error_message(123);
                }

                return Helper::get_message(105);

            } else {

                return Helper::get_error_message(123);

            }
        }

        public static function get_message($code)
        {
            switch($code) {
                case 101:
                    $string = "Success";
                    break;
                case 102:
                    $string = "Changed password successfully.";
                    break;
                case 103:
                    $string = "Successfully logged in.";
                    break;
                case 104:
                    $string = "Successfully logged out.";
                    break;
                case 105:
                    $string = "Successfully signed up.";
                    break;
                case 106:
                    $string = "Mail sent successfully";
                    break;
                case 107:
                    $string = "Payment successfully done";
                    break;
                case 108:
                    $string = "Favourite provider deleted successfully";
                    break;
                case 109:
                    $string = "Payment mode changed successfully";
                    break;
                case 110:
                    $string = "Payment mode changed successfully";
                    break;
                case 111:
                    $string = "Service Accepted";
                    break;
                case 112:
                    $string = "provider started";
                    break;
                case 113:
                    $string = "Arrived to service location";
                    break;
                case 114:
                    $string = "Service started";
                    break;
                case 115:
                    $string = "Service completed";
                    break;
                case 116:
                    $string = "User rating done";
                    break;
                case 117:
                    $string = "Request cancelled successfully.";
                    break;
                case 118:
                    $string = "Request rejected successfully.";
                    break;
                case 119:
                    $string = "Payment confirmed successfully.";
                    break;
                default:
                    $string = "";
            }
            return $string;
        }

        public static function get_push_message($code) {

            switch ($code) {
                case 601:
                    $string = "No Provider Available";
                    break;
                case 602:
                    $string = "No provider available to take the Service.";
                    break;
                case 603:
                    $string = "Request completed successfully";
                    break;
                case 604:
                    $string = "New Request";
                    break;
                case 605:
                    $string = "User Scheduled request is going to start on a hour";
                    break;
                case 606:
                    $string = "New Message Received";
                    break;
                default:
                    $string = "";
            }

            return $string;
        
        }

        public static function generate_password()
        {
            $new_password = time();
            $new_password .= rand();
            $new_password = sha1($new_password);
            $new_password = substr($new_password,0,8);
            return $new_password;
        }

        public static function delete_picture($picture) {
            File::delete( public_path() . "/uploads/" . basename($picture));
            return true;
        }

        public static function send_notifications($id, $type, $title, $message)
        {
            Log::info('push notification');

            $push_notification = 1; // Check the push notifictaion is enabled

            // Check the user type whether "USER" or "PROVIDER"

            if ($type == PROVIDER) {
                $user = Provider::find($id);
            } else {
                $user = User::find($id);
            }

            if ($push_notification == 1) {
                if ($user->device_type == 'ios') {
                    Helper::send_ios_push($user->device_token, $title, $message, $type);
                } else {
                    Helper::send_android_push($user->device_token, $title, $message);
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

        public static function get_fav_providers($service_type,$user_id) {

            /** Favourite Providers Search Start */

            Log::info('Favourite Providers Search Start');

            $favProviders = array();  // Initialize the variable

             // Get the favourite providers list

            $fav_providers_query = FavouriteProvider::leftJoin('providers' , 'favourite_providers.provider_id' ,'=' , 'providers.id')
                    ->where('user_id' , $user_id)
                    ->where('providers.is_available' , DEFAULT_TRUE)
                    ->where('providers.is_activated' , DEFAULT_TRUE)
                    ->where('providers.is_approved' , DEFAULT_TRUE)
                    ->select('provider_id' , 'providers.waiting_to_respond as waiting');

            if($service_type) {

                $provider_services = ProviderService::where('service_type_id' , $service_type)
                                        ->where('is_available' , DEFAULT_TRUE)
                                        ->get();

                $provider_ids = array();

                if($provider_services ) {

                    foreach ($provider_services as $key => $provider_service) {
                        $provider_ids[] = $provider_service->provider_id;
                    }

                    $favProviders = $fav_providers_query->whereIn('provider_id' , $provider_ids)->orderBy('waiting' , 'ASC')->get();
                }
                               
            } else {
                $favProviders = $fav_providers_query->orderBy('waiting' , 'ASC')->get();
            }

            return $favProviders;

            /** Favourite Providers Search End */
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

        public static function request_push_notification($id,$user_type,$request_id,$title,$message) {

            Log::info("Request Push notifictaion started");
            // Trigger the job
            new sendPushNotification($id,$user_type,$request_id,$title,$message);
        }

        public static function settings($key) {
            $settings = Settings::where('key' , $key)->first();
            return $settings->value;
        }

        public static function check_later_request($user,$requested_date,$flag) {
            // Add +2 and -1 hours from the requested time
            $start_date = Helper::add_date($requested_date,"-1");  //Already validated in controller no need - an hour
            // $start_date = $requested_date;
            $end_date = Helper::add_date($requested_date,"+2");

            $check_status = array(REQUEST_NO_PROVIDER_AVAILABLE,REQUEST_TIME_EXCEED_CANCELLED,REQUEST_CANCELLED,REQUEST_COMPLETED);

            // Base Query
            $check_requests_query_base = Requests::where('user_id' , $user)
                                    ->whereNotIn('status' , $check_status)
                                    ->where('later',DEFAULT_TRUE);
            // Query for to check the user requested time any requests already created
            $check_request_query_time = $check_requests_query_base->whereBetween('requested_time' ,[$start_date,$end_date]);

            // For create request have to check two conditions ,already request on the time , on process request
            if($flag == DEFAULT_TRUE) {
                // Check already scheduled requests are available on user given requested time
                if($check_request_query_time->count() == 0) {
                    // Add +2 and -1 hours from the current time
                    $date = date('Y-m-d H:i:s');
                    $current_start_date = Helper::add_date($date,"-1");
                    $current_end_date = Helper::add_date($date,"+2");
                    // Check any on going requests is available
                    $check_requests = $check_requests_query_base->whereBetween('requested_time' ,[$current_start_date,$current_end_date])
                                        ->count();
                } else {
                    $check_requests = $check_requests_query_base->count();
                }
            } else {
                $check_requests = $check_request_query_time->count(); // Used in accept and cancel requests , to check the scheduled request is on processing
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
                    // Add 1 hour from the START TIME
                    $change_date = new \DateTime($start_new);
                    $change_date->modify("+1 hours");
                    $end_new = $change_date->format("Y-m-d H:i:s");

                    // Assign new END TIME to end variable
                    $start_end = $end_new;

                    $current_date = Helper::formatDate($end_new);
                    $start_time = Helper::formatHour($start_new);
                    $end_time = Helper::formatHour($end_new);

                    // Check already availability is filled
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

        // Usage : provider Incoming request and cron function
        public static function assign_next_provider($request_id,$provider_id) {

            if($requests = Requests::find($request_id)) {

                //Check the request is offered to the current provider
                if($provider_id) {
                    $current_offered_provider = RequestsMeta::where('provider_id',$provider_id)
                                    ->where('request_id',$request_id)
                                    ->where('status', REQUEST_META_OFFERED)
                                    ->first();

                    // Change waiting to respond state
                    if($current_offered_provider) {
                        $get_offered_provider = Provider::where('id',$current_offered_provider->provider_id)->first();
                        $get_offered_provider->waiting_to_respond = WAITING_TO_RESPOND_NORMAL;
                        $get_offered_provider->save();

                        // TimeOut the current assigned provider
                        $current_offered_provider->status = REQUEST_META_TIMEDOUT;
                        $current_offered_provider->save();
                    }
                }

                //Select the new provider who is in the next position.
                $next_request_meta = RequestsMeta::where('request_id', '=', $request_id)->where('status', REQUEST_META_NONE)
                                    ->leftJoin('providers', 'providers.id', '=', 'requests_meta.provider_id')
                                    ->where('providers.is_activated',DEFAULT_TRUE)
                                    ->where('providers.is_available',DEFAULT_TRUE)
                                    ->where('providers.is_approved',DEFAULT_TRUE)
                                    ->where('providers.waiting_to_respond',WAITING_TO_RESPOND_NORMAL)
                                    ->select('requests_meta.id','requests_meta.status','requests_meta.provider_id')
                                    ->orderBy('requests_meta.created_at')
                                    ->first();

                //Check the next provider exist or not.
                if($next_request_meta){

                    // change waiting to respond state
                    $provider_detail = Provider::find($next_request_meta->provider_id);
                    $provider_detail->waiting_to_respond = WAITING_TO_RESPOND;
                    $provider_detail->save();

                    //Assign the next provider.
                    $next_request_meta->status = REQUEST_META_OFFERED;
                    $next_request_meta->save();

                    $time = date("Y-m-d H:i:s");

                    //Update the request start time in request table
                    Requests::where('id', '=', $request_id)->update( array('request_start_time' => date("Y-m-d H:i:s")) );
                    Log::info('assign_next_provider_cron assigned provider to request_id:'.$request_id.' at '.$time);

                    // Push Start
                    
                    $service = ServiceType::find($requests->request_type);
                    $user = User::find($requests->user_id);
                    $request_data = Requests::find($request_id);

                    // Push notification has to add
                    $title = Helper::get_push_message(604);
                    $message = "You got a new request from".$user->first_name." ".$user->last_name;
                    // Send Push Notification to Provider 

                    dispatch(new sendPushNotification($next_request_meta->provider_id,PROVIDER,$request_id,$title,$message));

                } else {
                    Log::info("No provider available this time - cron");
                    //End the request
                    //Update the request status to no provider available
                    Requests::where('id', '=', $request_id)->update( array('status' => REQUEST_NO_PROVIDER_AVAILABLE) );

                    // No longer need request specific rows from RequestMeta
                    RequestsMeta::where('request_id', '=', $request_id)->delete();
                    // Log::info('assign_next_provider_cron ended the request_id:'.$request_id.' at '.$time);

                    // Send Push Notification to User
                    $title = Helper::tr('cron_no_provider_title');
                    $message = Helper::tr('cron_no_provider_message');

                    dispatch(new NormalPushNotification($requests->user_id,USER,$title,$message));
                
                }
            }
        
        }
    }
