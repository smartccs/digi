<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_type_id', 'provider_id', 'available', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function scopeCheckService($query, $provider_id, $service_id)
    {
        return $query->where('provider_id' , $provider_id)->where('service_type_id' , $service_id);
    }
}
