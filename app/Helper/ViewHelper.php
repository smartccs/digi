<?php
use App\PromocodeUsage; 
use Illuminate\Support\Facades\Storage;

function currency($value = '')
{
	if($value == ""){
		return Setting::get('currency');
	}else{
		return Setting::get('currency').$value;
	}
}

function img($img){
	if($img == ""){
		return asset('main/avatar.jpg');
	}else{
		return asset('storage/'.$img);
	}
}

function promo_used_count($promo_id)
{
	return PromocodeUsage::where('status','USED')->where('promocode_id',$promo_id)->count();
}
