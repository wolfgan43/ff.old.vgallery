<?php
  $user_path = $cm->real_path_info;
//todo: ffCommon_crossDomains
 require FF_DISK_PATH . "/library/OAuth2/Autoloader.php";
 OAuth2\Autoloader::register();  

  switch(basename($user_path))
  {
  	case "user":
  	case "token":
  		require FF_DISK_PATH . "/modules/security/contents/oauth2/userauth." . FF_PHP_EXT;
  		break;
  	case "client":
  		require FF_DISK_PATH . "/modules/security/contents/oauth2/clientauth." . FF_PHP_EXT;
  		break;
  	case "public":
  		require FF_DISK_PATH . "/modules/security/contents/oauth2/publicauth." . FF_PHP_EXT;
  		break;
  	case "refreshtoken":
  		require FF_DISK_PATH . "/modules/security/contents/oauth2/refreshtoken." . FF_PHP_EXT;
  		break;
  	case "web":
  		require FF_DISK_PATH . "/modules/security/contents/oauth2/webauth." . FF_PHP_EXT;
  		break;
  		
  	default:
  }
