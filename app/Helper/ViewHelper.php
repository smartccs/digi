<?php

function currency($value)
{
	if($value == ""){
		return '-';
	}else{
		return Setting::get('currency').$value;
	}
}

function img($img){
	if($img == ""){
		return asset('admin/avatar.jpg');
	}else{
		return $img;
	}
}