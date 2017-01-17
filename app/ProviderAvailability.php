<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProviderAvailability extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'available_date','provider_id','start_time','end_time','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function scopeProviders($query, $provider_id)
    {
        return $query->where('provider_id', $provider_id);
    }

    public function scopeAvilableProviders($query, $provider_id)
    {
        return $query->where('provider_id', $provider_id)->whereDate('available_date', '>=', date('Y-m-d'));
    }
}
