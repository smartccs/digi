<?php

function currency($value)
{
	if($value == ""){
		return '-';
	}else{
		return Setting::get('currency').$value;
	}
}