<?php
	global $ff_global_setting;

	$ff_global_setting["ffRecord"]["buttons_options"]["print"]["display"] = true;
    $ff_global_setting["ffRecord_dialog"]["buttons_options"]["print"]["display"] = true;

    if (!defined("MOD_PHONECALL_GROUP_ADMIN")) define ("MOD_PHONECALL_GROUP_ADMIN", "phonecall");
    if (!defined("MOD_PHONECALL_GROUP_USER")) define ("MOD_PHONECALL_GROUP_USER", "phonecalluser");

    if (!defined("MOD_PHONECALL_THEME")) define ("MOD_PHONECALL_THEME", "phonecall");
    if (!defined("MOD_PHONECALL_JQUERYUI_THEME")) define ("MOD_PHONECALL_JQUERYUI_THEME", "phonecall");
    
    if(!defined("MOD_PHONECALL_PATH")) define("MOD_PHONECALL_PATH", "/restricted/phonecall");
?>
