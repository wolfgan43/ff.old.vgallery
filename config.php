<?php
// activecomboex
$plgCfg_ActiveComboEX_UseOwnSession = false;	/* set to true to bypass session check.
													NB: ActiveComboEX require a session. If you disable session
														check, ActiveComboEX do a session_start() by itself. */

/* DEFAULT FORMS SETTINGS
	this is a default array used by Forms classes to set user defined global default settings.
	the format is:
		$ff_global_setting[class_name][parameter_name] = value;
 */

/**
*  ERROR HANDLING
*/
define("FF_ERRORS_HANDLED", E_USER_ERROR | E_USER_WARNING | E_USER_NOTICE);
if(!defined("DISABLE_CACHE"))
{
    define("FF_ERROR_HANDLER_HIDE", true);
    define("FF_ERROR_HANDLER_CUSTOM_TPL", "/themes/gallery/contents/error_handler.html");
    define("FF_ERROR_HANDLER_MINIMAL", "/themes/gallery/contents/error_handler.html");
}

define("FF_ERRORS_MAXRECURSION", NULL);
define("FF_URLREWRITE_REMOVEHYPENS", true);

define("FF_PREFIX", "ff_");

/**
*  INTERNAZIONALIZATION*
*/
define("FF_SYSTEM_LOCALE", "ISO9075"); /* Default Locale */
define("FF_DEFAULT_CHARSET", "UTF-8");  /* Charset Default */

require(ffCommon_dirname(__FILE__) . "/conf/gallery/init.php");