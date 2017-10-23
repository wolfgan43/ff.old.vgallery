<?php
define("__CMS_DIR__"			                                    , __DIR__);
define("FRONTEND_THEME"			                                    , "site");

if($_SERVER["SERVER_ADDR"] == $_SERVER["REMOTE_ADDR"])
	require_once("themes/" . FRONTEND_THEME . "/conf/config.local.php");
else
	require_once("themes/" . FRONTEND_THEME . "/conf/config.remote.php");

define("__TOP_DIR__"												, FF_DISK_PATH);
/**********************************************************************************************************************
 * Config Framework and VGallery
 **********************************************************************************************************************/
require_once("conf/gallery/config.php");