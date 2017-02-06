<?php

namespace App;

use DB;

use Illuminate\Database\Eloquent\Model;

class UserRequests extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider_id','user_id','current_provider_id',
        'service_type_id','status','cancelled_by',
        'paid','distance','s_latitude','d_latitude','s_longitude',
        'd_longitude','paid','s_address', 'd_address',
        'assigned_at','schedule_at','started_at',
        'finished_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];

    /**
     * ServiceType Model Linked
     */
    public function service_type()
    {
        return $this->belongsTo('App\ServiceType');
    }
    
    /**
     * UserRequestPayment Model Linked
     */
    public function payment()
    {
        return $this->hasOne('App\UserRequestPayment', 'request_id');
    }

    /**
     * UserRequestRating Model Linked
     */
    public function rating()
    {
        return $this->hasOne('App\UserRequestRating', 'request_id');
    }

    /**
     * The user who created the request.
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * The provider assigned to the request.
     */
    public function provider()
    {
        return $this->belongsTo('App\Provider');
    }

    public function scopePendingRequest($query, $user_id)
    {
        return $query->where('user_id', $user_id)
                // ->where('later', 0) // Schedule - schedule_at != null
                ->whereNotIn('status' , ['CANCELLED', 'COMPLETED']);
    }

    public function scopeRequestHistory($query)
    {
        return $query->leftJoin('providers', 'user_requests.confirmed_provider', '=', 'providers.id')
                    ->leftJoin('users', 'user_requests.user_id', '=', 'users.id')
                    ->leftJoin('user_payments', 'user_requests.id', '=', 'user_payments.request_id')
                    ->select('user_requests.*','users.first_name as user_first_name', 'users.last_name as user_last_name',
                             'providers.first_name as provider_first_name', 'providers.last_name as provider_last_name', 
                             'users.id as user_id', 'providers.id as provider_id', 'user_payments.total as amount',
                            'user_payments.payment_mode as payment_mode', 'user_payments.status as payment_status')
                    ->orderBy('user_requests.created_at', 'desc');
    }

    public function scopeUserTrips($query, $user_id)
    {
        return $query->where('user_requests.user_id', '=', $user_id)
                    ->where('user_requests.status', '=', 'COMPLETED')
                    ->select('user_requests.*')
                    ->with('payment','service_type');
    }

    public function scopeUserTripDetails($query, $user_id, $request_id)
    {
        return $query->where('user_requests.user_id', '=', $user_id)
                    ->where('user_requests.id', '=', $request_id)
                    ->where('user_requests.status', '=', 'COMPLETED')
                    ->select('user_requests.*')
                    ->with('payment','service_type','user','provider','rating');
    }

    public function scopeUserRequestStatusCheck($query, $user_id, $check_status)
    {
        return $query->where('user_requests.user_id', $user_id)
                    ->where('user_requests.user_rated',0)
                    ->whereNotIn('user_requests.status', $check_status)
                    ->select('user_requests.*')
                    ->with('user','provider','service_type','rating','payment');
    }

}
