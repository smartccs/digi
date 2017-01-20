<?php

define('USER', 0);
define('PROVIDER',1);
define('NONE', 0);
define('DEFAULT_FALSE', 0);
define('DEFAULT_TRUE', 1);

// Payment Constants
define('COD',   'cod');
define('PAYPAL', 'paypal');
define('CARD',  'card');

define('REQUEST_NEW',        0);
define('REQUEST_WAITING',      1);
define('REQUEST_INPROGRESS',    2);
define('REQUEST_COMPLETE_PENDING',  3);
define('REQUEST_RATING',      4);   
define('REQUEST_COMPLETED',      5);
define('REQUEST_CANCELLED',      6);
define('REQUEST_NO_PROVIDER_AVAILABLE',7);
define('WAITING_FOR_PROVIDER_CONFRIMATION_COD',  8);


// Only when manual request
define('REQUEST_REJECTED_BY_PROVIDER', 9);

define('PROVIDER_NOT_AVAILABLE', 0);
define('PROVIDER_AVAILABLE', 1);

define('PROVIDER_NONE', 0);
define('PROVIDER_ACCEPTED', 1);
define('PROVIDER_STARTED', 2);
define('PROVIDER_ARRIVED', 3);
define('PROVIDER_SERVICE_STARTED', 4);
define('PROVIDER_SERVICE_COMPLETED', 5);
define('PROVIDER_RATED', 6);

define('REQUEST_META_NONE',   0);
define('REQUEST_META_OFFERED',   1);
define('REQUEST_META_TIMEDOUT', 2);
define('REQUEST_META_DECLINED', 3);

define('RATINGS', '0,1,2,3,4,5');

define('DEVICE_ANDROID', 'android');
define('DEVICE_IOS', 'ios');

define('WAITING_TO_RESPOND', 1);
define('WAITING_TO_RESPOND_NORMAL',0);

define('PROVIDER_AVAILABILITY_FREE' , 0);
define('PROVIDER_AVAILABILITY_SET' , 1);
define('PROVIDER_AVAILABILITY_BOOKED' , 2);