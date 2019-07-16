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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
  function get_locale($type = null, $nocurrent = false) {
  	static $locale_loaded = null;
	$target = ($nocurrent ? "nocurrent" : "current");
  	//if(!$arrLocale)
  	///	$arrLocale = Cms::getSchema("locale");
	/*if(!$locale_loaded[$target] && !$nocurrent) {
		$globals = ffGlobals::getInstance("gallery");
		if($globals->locale) {
			$locale_loaded[$target] = $globals->locale;
		}
	}*/
  	
	if(!$locale_loaded[$target]) {
		if(is_file(CM_CACHE_DISK_PATH . "/locale" . ($nocurrent ? "-nocurrent" : "") . "." . FF_PHP_EXT)) {
			require(CM_CACHE_DISK_PATH . "/locale" . ($nocurrent ? "-nocurrent" : "") . "." . FF_PHP_EXT);
            /** @var include $locale */
            $locale_loaded[$target] = $locale;
		} else {
			$expires = time() + (60 * 60 * 24 * 1);
			
			$locale_loaded[$target] = mod_auth_get_locale(LANGUAGE_DEFAULT, $nocurrent);
			$content = "<?php\n";
			$content .= '$locale = ' . var_export($locale_loaded[$target], true) . ";";

			cm_filecache_write(CM_CACHE_DISK_PATH, "locale" . ($nocurrent ? "-nocurrent" : "") . "." . FF_PHP_EXT, $content, $expires);
		}
	}
	
	if($type)
		return $locale_loaded[$target][$type];
	else
  		return $locale_loaded[$target];
}
