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
  function system_lib_mailchimp() {
  	$globals = ffGlobals::getInstance("gallery");
  	
	if(check_function("get_webservices")) {
		$services_params = get_webservices("mailchimp");
		
		if($services_params["enable"] && strlen($services_params["key"])) {
			if(check_function("class.mailchimp")) {
				$globals->services["mailchimp"]["obj"] = new Mailchimp($services_params["key"]);
			}
		} else {
			$strError = ffTemplate::_get_word_by_code("mailchimp_missing_app_key");
		}		
	}  	

	return $strError;  
  }
  
 //46b45bb54ba63a5d57fb7ddbfd44dfdd-us11 
function mailchimp_api($type, $method, $filter = array(), $data) {
	$globals = ffGlobals::getInstance("gallery");
	
	$strError = system_lib_mailchimp();
	if(!$strError) 
	{
		$mc = $globals->services["mailchimp"]["obj"];
		switch($method) {
			case "set":
				break;
			case "del":
				break;				
			case "get":
			default:
				$mc->request("/" . $type . "");
				break;
		
		}
	}
}

function mailchimp_set_subscribe($list_name, $member_data) {



}


function mailchimp_get_delta($list_name, $since_timestamp, $to_timestamp) {



}