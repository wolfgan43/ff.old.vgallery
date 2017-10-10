<?php
	require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);
	mod_security_check_session(true);
    
    if(get_session("UserNID") == MOD_SEC_GUEST_USER_ID)
        prompt_login();
        
	$cm->oPage->addContent("Page under costruction. Work in progess");
    
    http_response_code("404");
?>
