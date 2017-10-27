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
  	///	$arrLocale = cache_get_settings("locale");
	if(!$locale_loaded[$target] && !$nocurrent) {
		$globals = ffGlobals::getInstance("gallery");
		if($globals->locale) {
			$locale_loaded[$target] = $globals->locale;
		}
	}
  	
	if(!$locale_loaded[$target]) {
		if(is_file(CM_CACHE_PATH . "/locale" . ($nocurrent ? "-nocurrent" : "") . "." . FF_PHP_EXT)) {
			require(CM_CACHE_PATH . "/locale" . ($nocurrent ? "-nocurrent" : "") . "." . FF_PHP_EXT);

            /** @var include $locale */
            $locale_loaded[$target] = $locale;
		} else {
			$expires = time() + (60 * 60 * 24 * 1);
			
			$locale_loaded[$target] = mod_security_get_locale(LANGUAGE_DEFAULT, $nocurrent);
			$content = "<?php\n";
			$content .= '$locale = ' . var_export($locale_loaded[$target], true) . ";";

			cm_filecache_write(CM_CACHE_PATH, "locale" . ($nocurrent ? "-nocurrent" : "") . "." . FF_PHP_EXT, $content, $expires);
		}
	}
	
	if($type)
		return $locale_loaded[$target][$type];
	else
  		return $locale_loaded[$target];
}


function translate($words, $toLang, $fromLang = LANGUAGE_DEFAULT, $webservice = null) {
    static $t = null;
    
    if(!$t && is_file(CM_CACHE_PATH . "/translation." . FF_PHP_EXT)) {
        require(CM_CACHE_PATH . "/translation." . FF_PHP_EXT);
    }
    
    $fromto = $fromLang . "|" . $toLang;

    if(!$t[$fromto][$words]) {
        if(check_function("get_webservices")) 
            $services_params = get_webservices("google.translate"); 

        switch($webservice) {
            default:
            case "translator":
                $transalted = file_get_contents("http://www.transltr.org/api/translate?text=" . urlencode($words) . "&to=" . strtolower(substr($toLang, 0, 2)) . "&from=" . strtolower(substr($fromLang, 0, 2)) . ($services_params["key"] ? "&key=" . $services_params["key"] : ""));
                if($transalted) {
                    $buffer = json_decode($transalted, true);
                    if($buffer["translationText"]) {
                        $res = $buffer["translationText"];
                        break;
                    }
                }                
            case "translated":
                $transalted = file_get_contents("http://api.mymemory.translated.net/get?q=" . urlencode($words) . "&langpair=" . $fromLang . "|" . $toLang . ($services_params["key"] ? "&key=" . $services_params["key"] : ""));
                //print_r($transalted);

                if($transalted) {
                    $buffer = json_decode($transalted, true);
                    if($buffer["responseStatus"] == 200) {
                        $res = $buffer["responseData"]["translatedText"];
                        break;
                    }
                }	
            case "google":
                $transalted = file_get_contents("https://translation.googleapis.com/language/translate/v2?q=" . urlencode($words) . "&target=" . strtolower(substr($toLang, 0, 2)) . "&source=" . strtolower(substr($fromLang, 0, 2)) . ($services_params["key"] ? "&key=" . $services_params["key"] : ""));
                //$transalted = file_get_contents('https://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=' . urlencode($words) . '&langpair=' . strtolower(substr($fromLang, 0, 2)) . '|' . strtolower(substr($toLang, 0, 2)));
                if($transalted) {
                    $buffer = json_decode($transalted, true);
                    if(!$buffer["error"]) {
                        $res = $buffer["responseData"]["translatedText"];
                        break;
                    }
                }
        }
        
        if($res) {
            $t[$fromto][$words] = $res;
            $expires = time() + (60 * 60 * 24 * 1);

            $content = "<?php\n";
            $content .= '$t = ' . var_export($t, true) . ";";

            cm_filecache_write(CM_CACHE_PATH, "translation." . FF_PHP_EXT, $content, $expires);
        }
    }

	return ($t[$fromto][$words] ? $t[$fromto][$words] : $words);
}
