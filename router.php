<?php
/**
 * Config Project
 */

define("FF_SKIP_COMPONENTS", true);

require_once("config.php");

require_once(__CMS_DIR__ . "/library/gallery/system/cache.php");

require_once("ff/main.php");

require_once(__CMS_DIR__ . "/library/gallery/system/gallery_redirect.php");

$path_info = $_SERVER["PATH_INFO"];
if($path_info == "/index")
	$path_info = "";

if($path_info) {
	system_gallery_redirect($path_info);
}
exit;
    
