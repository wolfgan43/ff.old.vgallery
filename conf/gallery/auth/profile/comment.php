<?php
use_cache(false);

require(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

$UserNID = get_session("UserNID");
//$user_path = $cm->real_path_info;
$UserID = get_session("UserID");

$ret_url = urldecode($_REQUEST["ret_url"]);
if(!strlen($ret_url))
    $ret_url = FF_SITE_PATH . "/";

/*
if(check_function("check_user_request"))
	$additionaldata = check_user_form_request(array("ID" => $UserNID));
if($additionaldata)
    ffRedirect(FF_SITE_PATH . USER_RESTRICTED_PATH . "/additionaldata/" . $additionaldata . "?ret_url=" . urlencode($ret_url));
*/
//Controllo permessi e settaggio per la parte di modifica
if($UserID == MOD_SEC_GUEST_USER_NAME && !strlen(basename($cm->real_path_info))) { 
    ffRedirect(FF_SITE_PATH . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
} 

if(strlen(basename($cm->real_path_info))) {
	if(ffCommon_url_rewrite($UserID) == basename($cm->real_path_info)) {
        $actual_uid = $UserNID;
        $allow_edit = true;
    } else {
		$sSQL = "SELECT * 
				FROM " . CM_TABLE_PREFIX . "mod_security_users 
				WHERE " . CM_TABLE_PREFIX . "mod_security_users.username = " . $db_gallery->toSql(basename($cm->real_path_info)) . "
					AND " . CM_TABLE_PREFIX . "mod_security_users.public = '1'";
		$db_gallery->query($sSQL);
	    if($db_gallery->nextRecord()) {
	        $actual_uid = $db_gallery->getField("ID", "Number", true);
	        
	        if($actual_uid == $UserNID) {
	            $allow_edit = true;
	        } else {
	            $allow_edit = false;
	        }
	    } else {
	    	$allow_edit = false;
	        //ffRedirect(FF_SITE_PATH . "/login?ret_url=" . urlencode($_SERVER['REQUEST_URI']) . "&relogin");
	    }       	
    }
} else {
	$actual_uid = $UserNID;
	$allow_edit = true;
}

$db_gallery->query("SELECT " . CM_TABLE_PREFIX . "mod_security_users.ID
                            , " . CM_TABLE_PREFIX . "mod_security_users.enable_ecommerce_data AS enable_ecommerce_data
                            , " . CM_TABLE_PREFIX . "mod_security_users.public AS public
                        FROM 
                            " . CM_TABLE_PREFIX . "mod_security_users
                        WHERE 
                            " . CM_TABLE_PREFIX . "mod_security_users.ID = " . $db_gallery->toSql($actual_uid, "Number"));
if($db_gallery->nextRecord()) {
	if($db_gallery->getField("public", "Number", true) || $allow_edit) {
		$layout_comment["prefix"] = "PC";
		$layout_comment["ID"] = 0;
		$layout_comment["title"] = "Comment";
		$layout_comment["type"] = "COMMENT";
		$layout_comment["location"] = "Content";
		$layout_comment["width"] = $sections["Content"];
		$layout_comment["visible"] = NULL;
		if(check_function("get_layout_settings"))
			$layout_comment["settings"] = get_layout_settings(NULL, "COMMENT");

		if(check_function("process_addon_comment_view"))
			$cm->oPage->addContent('<div class="profile">' . process_addon_comment_view($user_path, $ret_url, "", 0, 0, $actual_uid, false, $layout_comment) . "</div>", null, "Comment"); 
	} else {
		$strError = ffTemplate::_get_word_by_code("user_profile_access_denied");
	}
} else {
	$strError = ffTemplate::_get_word_by_code("user_profile_not_found");
}

if(strlen($strError))
	$cm->oPage->addContent($strError, null, "error");

if(strlen($ret_url) && stripslash($ret_url) != FF_SITE_PATH) {
	$oButton = ffButton::factory($cm->oPage);
	$oButton->id = "back";
	$oButton->action_type = "gotourl";
//	if(strlen($ret_url) && stripslash($ret_url) != FF_SITE_PATH) {
		$oButton->url = $ret_url;
                $oButton->aspect = "link";
		$oButton->label = ffTemplate::_get_word_by_code("back");
//	} else {
//		$oButton->url = FF_SITE_PATH . "/";
//		$oButton->label = ffTemplate::_get_word_by_code("back_home");
//	}
	$oButton->parent_page = array(&$cm->oPage);

	$cm->oPage->addContent($oButton->process(), null, "back");
}

?>