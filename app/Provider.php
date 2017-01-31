<?php

namespace App;

use App\Notifications\ProviderResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Provider extends Authenticatable
{
    use Notifiable;

    protected $table = "providers";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name' ,'last_name', 'email', 'password', 'mobile', 'address', 'picture', 'gender'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'updated_at', 'created_at'
    ];


    /**
     * The services that belong to the user.
     */
    public function services()
    {
        return $this->hasMany('App\ProviderService');
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ProviderResetPassword($token));
    }

    /**
     * Check Availability.
     *
     * @param  string  $token
     * @return void
     */
    public function scopeCheckAvailability($query, $provider_id)
    {
        return $query->where('id' , $provider_id)->where('is_available' , DEFAULT_TRUE)->where('is_activated' , DEFAULT_TRUE)->where('is_approved' , DEFAULT_TRUE)->where('waiting_to_respond' ,DEFAULT_FALSE);
    }


    /**
     * Guest Provider List.
     *
     * @param  string  $token
     * @return void
     */
    public function GuestProviderList($latitude, $longitude, $service_id, $distance)
    {
        $providers = DB::select(DB::raw("SELECT 
                        providers.id,
                        providers.first_name,
                        providers.last_name,
                        providers.picture,
                        providers.address,
                        providers.latitude,
                        providers.longitude,
                        provider_services.service_type_id,
                        1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) AS distance
                        FROM providers
                        LEFT JOIN provider_services ON providers.id = provider_services.provider_id
                        WHERE provider_services.service_type_id = $service_id 
                        AND providers.is_available = 1 
                        AND providers.waiting_to_respond = 0
                        AND is_activated = 1
                        AND is_approved = 1
                        AND (1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance
                        ORDER BY distance"));

        return $providers;
    }

    public function NearByProviders($latitude, $longitude, $service_id, $distance){

    }
}
