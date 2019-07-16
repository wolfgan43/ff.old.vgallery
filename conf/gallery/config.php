<?php
/**
 *   VGallery: CMS based on FormsFramework
Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

//$plgCfg_ActiveComboEX_UseOwnSession = false;

/**
 * Error
 */
error_reporting((E_ALL ^ E_NOTICE ^ E_WARNING ^ E_DEPRECATED) | E_STRICT);
@ini_set("display_errors", true);

/**
 * Timezone
 */
date_default_timezone_set(TIMEZONE);

/**
 * File Handling
 */
@umask(0);

/**
 * Cache Settings
 */
define("FF_TEMPLATE_ENABLE_TPL_JS", true);
define("CM_CSSCACHE_RENDER_THEME_PATH", (!defined("APACHE_MODULE_EXPIRES") || APACHE_MODULE_EXPIRES ? false : true));
define("CM_ENABLE_MEM_CACHING", false);			/* se abiliti CM_ENABLE_MEM_CACHING puoi usare __CLEARCACHE__ nell'url per resettare la cache */


/**
 * Mysql Settings
 */
define("FF_ORM_ENABLE", false);

/**
 * Session Settings
 */
if(defined("SESSION_SAVE_PATH"))
    session_save_path(SESSION_SAVE_PATH);
if(defined("SESSION_NAME"))
    session_name(SESSION_NAME);

/**
 * FF Error Handler
 */
if(DEBUG_MODE !== true)
{
    define("FF_ERROR_HANDLER_HIDE", true);
    define("FF_ERROR_HANDLER_CUSTOM_TPL", "/themes/gallery/contents/error_handler.html");
    define("FF_ERROR_HANDLER_MINIMAL", "/themes/gallery/contents/error_handler.html");
}

/**
 * Utility
 */
define("DS"                                                     , DIRECTORY_SEPARATOR);
/**
 * Vgallery Settings
 */
define("THEME_INSET"			                                , "gallery");
//define("FRONTEND_THEME"			                            , "site");


if(!defined("SHOWFILES_IS_RUNNING"))
{
	define("DOMAIN_INSET"                                       , $_SERVER["HTTP_HOST"]);
	if(strpos(strtolower(DOMAIN_INSET), "www.") === 0) {
		define("DOMAIN_NAME"                                    , substr(DOMAIN_INSET, 4));
	} else {
		define("DOMAIN_NAME"                                    , DOMAIN_INSET);
	}

    define("OLD_VGALLERY", false);


    //define("VG_UI_PATH"											, "/ui");
    define("VG_SYS_PATH"										, "/conf/gallery");
    define("VG_WS_ADMIN"										, "/admin");
    define("VG_WS_RESTRICTED"									, "/restricted");
    define("VG_WS_BUILDER"										, "/builder");
    define("VG_WS_ECOMMERCE"									, "/ecommerce");

    define("VG_RULE_INSTALL"									, "/admin/install"); //fixed
    define("VG_RULE_UPDATER"									, "/admin/updater"); //fixed
    define("VG_RULE_ECOMMERCE"									, "/admin/ecommerce"); //fixed

    define("VG_RULE_UI_UTILITY"									, "utility");

    $_ENV["VG_RULE_UI"]["UTILITY"] 								= "utility";

    $_ENV["VG_RULE"]["INSTALL"] 								= "/admin/install";
    $_ENV["VG_RULE"]["UPDATER"] 								= "/admin/updater";
    $_ENV["VG_RULE"]["ECOMMERCE"] 								= "/admin/ecommerce";

    define("VG_WEBSERVICES" 									, "/admin/services");
    define("VG_WEBSERVICES_PATH" 								, FF_DISK_PATH . VG_SYS_PATH . "/services");
    define("VG_ADDONS" 											, "/admin/addons");
    define("VG_ADDONS_PATH" 									, "/conf/gallery/modules");

    define("HIDE_EXT"               							, true);
    define("GALLERY_PATH"           							, "/gallery");
    define("GALLERY_PATH_SYSTEM"    							, GALLERY_PATH . "/sys");
    define("GALLERY_PATH_ECOMMERCE" 							, GALLERY_PATH . "/ecommerce");
    define("GALLERY_PATH_MANAGE"    							, GALLERY_PATH . "/manage");
	define("GALLERY_PATH_MODULE"    							, GALLERY_PATH . "/modules");
    define("GALLERY_PATH_JOB"    								, GALLERY_PATH . "/job");

    define("GALLERY_PATH_AUTH"      							, GALLERY_PATH . "/auth");

    define("GALLERY_TPL_PATH"       							, "contents");  //_sys
    define("USER_RESTRICTED_PATH"								, "/user");

    if(defined("MEMORY_LIMIT") && ini_get("memory_limit") != MEMORY_LIMIT)
        @ini_set("memory_limit", MEMORY_LIMIT);
    //   die("unable set memory_limit: must be " . MEMORY_LIMIT . "\n");
    /**
     * Check configuration or goto Installer
     */

	if(defined("FF_DISK_PATH")
		&& defined("SESSION_NAME")
		&& defined("FF_DATABASE_NAME")
		&& defined("SUPERADMIN_PASSWORD")
		&& defined("MASTER_SITE")
		&& defined("FTP_PASSWORD")
		&& defined("APPID")
		&& defined("LANGUAGE_DEFAULT")
		&& defined("ADMIN_THEME")
		&& defined("FRAMEWORK_CSS")
		&& defined("FONT_ICON")
	) {
		define("FF_ERROR_HANDLER_LOG", true);
		define("FF_ERROR_HANDLER_LOG_PATH", CM_CACHE_DISK_PATH . "/errors");

		if (basename($_SERVER["PATH_INFO"]) == "install"
			//&& strpos($_SERVER["HTTP_REFERER"], "://" . MASTER_SITE . "/admin/system/domains") !== false
		) {
			//define("BLOCK_INSTALL", true);
			require(FF_DISK_PATH . VG_SYS_PATH . "/install/index.php");
			exit;
		}
		/*if($_SERVER["PATH_INFO"] == "/install") {
			if(isset($_REQUEST["complete"]))
				$complete = "?complete";

			//header("Location: " . FF_SITE_PATH . "/admin/system/install" . $complete);
			//exit;
		}*/
	} else {
        echo "<h1>Add constant in /themes/site/conf/config.*.php</h1>";

	    if(!defined("FF_DISK_PATH"))            echo "Missing: <b>FF_DISK_PATH</b><br />\n";
        if(!defined("SESSION_NAME"))            echo "Missing: <b>SESSION_NAME</b><br />\n";
        if(!defined("FF_DATABASE_NAME"))        echo "Missing: <b>FF_DATABASE_NAME</b><br />\n";
        if(!defined("SUPERADMIN_PASSWORD"))     echo "Missing: <b>SUPERADMIN_PASSWORD</b><br />\n";
        if(!defined("MASTER_SITE"))             echo "Missing: <b>MASTER_SITE</b><br />\n";
        if(!defined("FTP_PASSWORD"))            echo "Missing: <b>FTP_PASSWORD</b><br />\n";
        if(!defined("APPID"))                   echo "Missing: <b>APPID</b><br />\n";
        if(!defined("LANGUAGE_DEFAULT"))        echo "Missing: <b>LANGUAGE_DEFAULT</b><br />\n";
        if(!defined("ADMIN_THEME"))             echo "Missing: <b>ADMIN_THEME</b><br />\n";
        if(!defined("FRAMEWORK_CSS"))           echo "Missing: <b>FRAMEWORK_CSS</b><br />\n";
        if(!defined("FONT_ICON"))               echo "Missing: <b>FONT_ICON</b><br />\n";
        exit;

/*
        $host_name = $_SERVER["HTTP_HOST"];
        if (strpos(php_uname(), "Windows") !== false)
            $tmp_file = str_replace("\\", "/", __FILE__);
        else
            $tmp_file = __FILE__;

        $this_relative_file = "/conf/gallery/config.php";

        if(strpos($tmp_file, $_SERVER["DOCUMENT_ROOT"]) !== false) {
            $document_root =  $_SERVER["DOCUMENT_ROOT"];
            if (substr($document_root,-1) == "/")
                $document_root = substr($document_root,0,-1);

            $site_path = str_replace($document_root, "", str_replace($this_relative_file, "", $tmp_file));
            $disk_path = $document_root . $site_path;
        } elseif(strpos($tmp_file, $_SERVER["PHP_DOCUMENT_ROOT"]) !== false) {
            $document_root =  $_SERVER["PHP_DOCUMENT_ROOT"];
            if (substr($document_root,-1) == "/")
                $document_root = substr($document_root,0,-1);

            $site_path = str_replace($_SERVER["DOCUMENT_ROOT"], "", str_replace($this_relative_file, "", $_SERVER["SCRIPT_FILENAME"]));
            $disk_path = $document_root . str_replace($document_root, "", str_replace($this_relative_file, "", $tmp_file));
        } else {
            $st_disk_path = str_replace($this_relative_file, "", $tmp_file);
            $st_site_path = str_replace($this_relative_file, "", $_SERVER["SCRIPT_NAME"]);
        }

        if(basename($_SERVER["PATH_INFO"]) == "install") {
            define("FF_DISK_PATH", $disk_path);
            define("FF_SITE_PATH", $site_path);
            define("CM_DONT_RUN_LAYOUT", true);
            define("EVENT_DONT_RUN", true);
            define("GALLERY_INSTALLATION_PHASE", true);
        } elseif(basename($_SERVER["PATH_INFO"]) == "setup") {
            header("Location: " . $site_path . VG_SYS_PATH . "/install?setup");
            exit;
        } else {
            header("Location: " . $site_path . VG_SYS_PATH . "/install");
            exit;
        }*/
	}
}
