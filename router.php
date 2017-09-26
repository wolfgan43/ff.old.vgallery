<?php
define("FF_SKIP_COMPONENTS", true);

//require_once(__DIR__ . "/conf/gallery/config/path.php");
require_once(__DIR__ . "/library/gallery/system/cache.php");
require_once(__DIR__ . "/ff/main.php");
require_once(__DIR__ . "/conf/gallery/init.php");
require_once(__DIR__ . "/library/gallery/system/gallery_redirect.php");

$path_info = $_SERVER["PATH_INFO"];
if($path_info == "/index")
    $path_info = "";

if($path_info) {
	system_gallery_redirect($path_info);
}
exit;
    
