<?php
define("__CMS_DIR__"			                                    , __DIR__);
define("FRONTEND_THEME"			                                    , "site");

if(substr($_SERVER["HTTP_HOST"], -6) == ".local")
	require_once(realpath("themes/" . FRONTEND_THEME . "/conf/config.local.php"));
else
	require_once(__DIR__ . "/themes/" . FRONTEND_THEME . "/conf/config.remote.php");

if(!defined("__TOP_DIR__"))
	define("__TOP_DIR__"												, FF_DISK_PATH);
if(!defined("CM_CACHE_PATH"))
	define("CM_CACHE_PATH"												, FF_DISK_PATH . "/cache");
/**********************************************************************************************************************
 * Config Framework and VGallery
 **********************************************************************************************************************/
require_once("conf/gallery/config.php");