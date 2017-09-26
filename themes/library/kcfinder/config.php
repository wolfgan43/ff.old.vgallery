<?php

/** This file is part of KCFinder project
  *
  *      @desc Base configuration file
  *   @package KCFinder
  *   @version 2.2
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

// IMPORTANT!!! Do not remove uncommented settings in this file even if
// you are using session configuration.
// See http://kcfinder.sunhater.com/install for setting descriptions

$host_name = $_SERVER["HTTP_HOST"];
if (strpos(php_uname(), "Windows") !== false)
    $tmp_file = str_replace("\\", "/", __FILE__);
else
    $tmp_file = __FILE__;
    
if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
	$document_root =  $_SERVER["DOCUMENT_ROOT"];
	if (substr($document_root,-1) == "/")
		$document_root = substr($document_root,0,-1);

	$site_path = str_replace($document_root, "", str_replace("/themes/library/kcfinder/config.php", "", $tmp_file));
	$disk_path = $document_root . $site_path;
} elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
	$document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
	if (substr($document_root,-1) == "/")
		$document_root = substr($document_root,0,-1);

	$site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace("/themes/library/kcfinder/config.php", "", $_SERVER["SCRIPT_FILENAME"]));
	$disk_path = $document_root . str_replace($document_root, "", str_replace("/themes/library/kcfinder/config.php", "", $tmp_file));
} else {
	$st_disk_path = str_replace("/themes/library/kcfinder/config.php", "", $tmp_file);
	$st_site_path = str_replace("/themes/library/kcfinder/config.php", "", $_SERVER["SCRIPT_NAME"]);
}

define("DISABLE_CACHE", true);
 
define("SHOWFILES_IS_RUNNING", true);
//define("FF_SKIP_COMPONENTS", true);
define("CM_DONT_RUN", true);
define("FF_ERROR_HANDLER_HIDE", true);

require_once($disk_path . "/cm/main.php");

error_reporting(E_ALL ^ E_NOTICE);

//da cambiare senno esplode l'activecombo
if(!defined("MOD_SECURITY_SESSION_STARTED")) { 
	mod_security_check_session();  

	//session_name("ckf_" . session_name());
/*	if (!mod_security_check_session(false)) {
		mod_security_create_session(MOD_SEC_GUEST_USER_NAME, MOD_SEC_GUEST_USER_ID);
	}
*/
}

$user_permission = get_session("user_permission");
$user_path = urldecode($_REQUEST["path_info"]);

if($user_path == "")
    $user_path = "/";


$_CONFIG = array(

    'disabled' => false,
    'readonly' => false,
    'denyZipDownload' => true,

    'theme' => "oxygen",

    'uploadURL' => ffCommon_dirname(SITE_UPDIR),
    'uploadDir' => ffCommon_dirname(DISK_UPDIR),

    'dirPerms' => 0777,
    'filePerms' => 0777,

    'deniedExts' => "exe com msi bat php cgi pl",

    'types' => array("uploads" => ""),

    'mime_magic' => "", 

    'maxImageWidth' => 0,
    'maxImageHeight' => 0,

    'thumbWidth' => 100,
    'thumbHeight' => 100,

    'thumbsDir' => (CM_SHOWFILES_THUMB_IN_CACHE ? "/cache/" . CM_SHOWFILES_THUMB_PATH : basename(SITE_UPDIR) . "/" . CM_SHOWFILES_THUMB_PATH),
    'hideDir' => array(CM_SHOWFILES_THUMB_PATH),

    'jpegQuality' => 90,

    'cookieDomain' => DOMAIN_INSET,
    'cookiePath' => "/",
    'cookiePrefix' => 'KCFINDER_',

    // THE FOLLOWING SETTINGS CANNOT BE OVERRIDED WITH SESSION CONFIGURATION

    '_check4htaccess' => true,
    //'_tinyMCEPath' => "/tiny_mce",

    '_sessionVar' => &$_SESSION['KCFINDER'],
    //'_sessionLifetime' => 30,
    //'_sessionDir' => "/full/directory/path",

    //'_sessionDomain' => ".mysite.com",
    //'_sessionPath' => "/my/path",
);
?>