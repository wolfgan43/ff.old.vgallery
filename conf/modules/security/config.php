<?php
    define("MOD_SEC_GROUPS", true);
	define("MOD_SEC_ENABLE_GEOLOCALIZATION", true);

    if(!defined("MOD_SEC_LOGIN_REGISTER_URL"))  define("MOD_SEC_LOGIN_REGISTER_URL", true);
    if(!defined("MOD_SEC_PASSWORD_RECOVER"))    define("MOD_SEC_PASSWORD_RECOVER", true);
    
    define("MOD_SEC_LOGIN_DOMAIN", false);
    define("MOD_SEC_LOGIN_BACK_URL", true);
    define("MOD_SEC_STRICT_FIELDS", false);
    define("MOD_SEC_ENABLE_TOKEN", true);
    
    
	define ("MOD_SEC_USER_AVATAR"		, "avatar");
	define ("MOD_SEC_USER_FIRSTNAME"	, "name");
	define ("MOD_SEC_USER_LASTNAME"		, "surname");
	define ("MOD_SEC_USER_COMPANY"		, "");    
    
   
    /*firstname,lastname*/
    define("MOD_SEC_DEFAULT_FIELDS" , "ID,ID_domains,avatar,name,surname,username,username_slug,password,level,status,expiration,email,primary_gid,time_zone,role,created,password_generated_at,temp_password,password_used,ID_packages,shippingreference,shippingaddress,shippingcap,shippingtown,shippingprovince,shippingstate,public,billreference,billaddress,billcap,billtown,billprovince,billstate,billpiva,billcf,name,surname,tel,avatar");
    
   // define("MOD_SEC_CSS_PATH", false);
   
    define ("MOD_SEC_MULTIDOMAIN", true);
    	
