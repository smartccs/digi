<?php

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
		return $img;
	}
}
