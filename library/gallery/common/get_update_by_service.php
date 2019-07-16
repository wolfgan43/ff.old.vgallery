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
  function get_update_by_service($service_name, $service_params = null, $out = "html", $action_url = null) {
  	static $updater = array();

  	if(strlen($service_name)) {
 		if(!array_key_exists($service_name, $updater)) {
			$str_params = "";
			if(is_array($updater_service_params) && count($updater_service_params)) {
				foreach($updater_service_params AS $params_key => $params_value) {
					if(strlen($str_params))
						$str_params .= "&";

					$str_params .= "params[" . $params_key . "]=" . $params_value;
				}

				$str_params = "?" . $str_params;
			} 		
			$res = @file_get_contents("http://" . DOMAIN_INSET . "/srv/updater/" . ffCommon_url_rewrite($service_name) . $str_params);
			if(strlen($res)) {
				$arrRes = json_decode($res, true);
				if(is_array($arrRes) && count($arrRes)) {
					foreach($arrRes AS $arrRes_key => $arrRes_value) {
						$updater[$service_name][] = array(
							"name" => $arrRes_value["name"]
							, "public_cover" => $arrRes_value["public_cover"]
							, "public_description" => $arrRes_value["public_description"]
							, "public_link_doc" => ($arrRes_value["public_link_doc"] 
					 							? '<a href="' . $arrRes_value["public_link_doc"] . '" target="_blank">' . ffTemplate::_get_word_by_code("public_doc_more") . '</a>'
					 							: ""
					 						)
						);
					}
				}
			}
		}	
		if(strlen($out))
			return call_user_func_array("get_update_by_service_" . $out, array($updater[$service_name], $action_url));
		else
			return $updater[$service_name];
	}  
  }
  
  
  
function get_update_by_service_html($updater_data, $action_url = null) {
    $html_content = "";
	if(!strlen($action_url))
		$action_url = "javascript:void(0);";

  	if(is_array($updater_data) && count($updater_data)) {
  		foreach($updater_data AS $key => $value) {
			$html_content .= '
				<div class="panel ' . Cms::getInstance("frameworkcss")->get(array(6,6,4,3), "col") . ' ' . Cms::getInstance("frameworkcss")->get("align-center", "util") . '">
					<a href="javascript:void(0);" onclick="' . $action_url . '" rel="' . $value["name"] . '" title="' . $value["public_description"] . '">
						<img src="' . (basename($value["public_cover"]) == "spacer.gif"
							? CM_SHOWFILES . "/100x100/" . THEME_INSET . "/images/noimage-service.png"
							: $value["public_cover"]
						) . '" />
						<h3 class="' . Cms::getInstance("frameworkcss")->get("text-nowrap", "util") . '">' . ucwords(str_replace("-", " ", $value["name"])) . '</h3> 
					</a>
					' . ($value["public_link_doc"] 
						? '<a href="' . $value["public_link_doc"] . '" target="_blank">' . ffTemplate::_get_word_by_code("public_doc_more") . '</a>'
						: ""
					) . '
				</div>';
  		}
  	}

	$html_content .= '<div class="panel ' . Cms::getInstance("frameworkcss")->get(array(6,6,4,3), "col") . ' ' . Cms::getInstance("frameworkcss")->get("align-center", "util") . '">
  						<a href="javascript:void(0);" onclick="' . $action_url . '" rel="" title="' . ffTemplate::_get_word_by_code("public_create_new") . '">
  							<img src="' . CM_SHOWFILES . "/100x100/" . THEME_INSET . "/images/create-new.png" . '" />
							<h3 class="' . Cms::getInstance("frameworkcss")->get("text-nowrap", "util") . '">' . ffTemplate::_get_word_by_code("public_create_new") . '</h3> 
  						</a>
  					</div>';
    
    //<a class="icon ico-5x ico-plus" href="javascript:void(0);" onclick="' . $action_url . '" rel=""></a>
    $html_content = '<div class="row updater-service">' . $html_content . '</div>';  	 

  	return $html_content;
}
  
function set_interface_for_copy_by_service($updater_service_name, $updater_service_id, $updater_service_params = null) {
	$cm = cm::getInstance();
	
	if(strlen($updater_service_name) && strlen($updater_service_name)) {
        
        
		if(isset($_REQUEST[$updater_service_id . "_copy_by_service"]) && $_REQUEST["frmAction"] == $updater_service_id . "_insert") {
			if(strlen($_REQUEST[$updater_service_id . "_copy_by_service"])) {
				if(check_function("clone_by_schema")) {
					$res = file_get_contents("http://" . DOMAIN_INSET . "/srv/updater/" . ffCommon_url_rewrite($updater_service_name) . "/" . $_REQUEST[$updater_service_id . "_copy_by_service"]);
					if(strlen($res)) {
						$arrRes = json_decode($res, true);

						clone_by_schema($updater_service_name, $arrRes, "updater");
					}
				}
				if($_REQUEST["XHR_DIALOG_ID"]) {
					die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => true, "refresh" => true, "resources" => array($updater_service_id)), true));
				} else {
					die(ffCommon_jsonenc(array(/*"url" => $_REQUEST["ret_url"],*/ "close" => true, "refresh" => true, "resources" => array($updater_service_id)), true));
					//ffRedirect($_REQUEST["ret_url"]);
				}
			} else {
				if($_REQUEST["XHR_DIALOG_ID"]) {
					die(ffCommon_jsonenc(array("url" => $cm->oPage->getRequestUri() . "&createnew", "close" => false, "refresh" => true), true));
				} else {
					die(ffCommon_jsonenc(array("url" => $cm->oPage->getRequestUri() . "&createnew", "close" => false, "refresh" => true), true));
					//ffRedirect($_REQUEST["ret_url"]);
				}	
			}
		}

		$arrUpdaterService = get_update_by_service($updater_service_name, $updater_service_params);

		$clone_by_updater_service = (isset($_REQUEST["createnew"])
				? false
				: count($arrUpdaterService) //TODO: da finire i servizi con le FIX operation
			);
			
		if(!$_REQUEST["keys"]["ID"] && $clone_by_updater_service) {
			$oRecord = ffRecord::factory($cm->oPage);
			$oRecord->id = $updater_service_id;
			$oRecord->resources[] = $oRecord->id;
			$oRecord->title = ffTemplate::_get_word_by_code($updater_service_name . "_modify_title");
			$oRecord->src_table = $updater_service_name;

			$oField = ffField::factory($cm->oPage);
			$oField->id = "ID";
			$oField->base_type = "Number";
			$oRecord->addKeyField($oField);
		
			$oField_clone_by_updater_service = ffField::factory($cm->oPage);
			$oField_clone_by_updater_service->id = "copy_by_service";
			$oField_clone_by_updater_service->display_label = false;
			$oField_clone_by_updater_service->label = ffTemplate::_get_word_by_code($updater_service_name . "_copy");
			$oField_clone_by_updater_service->control_type = "hidden";
			$oField_clone_by_updater_service->fixed_pre_content = get_update_by_service($updater_service_name, $updater_service_params, "html", "jQuery('#" . $updater_service_id . "_copy_by_service').val(jQuery(this).prop('rel')); jQuery('#" . $updater_service_id . "_ActionButtonInsert').click();");
			$oField_clone_by_updater_service->encode_entities = false;
			$oField_clone_by_updater_service->required = true;
			$oField_clone_by_updater_service->store_in_db = false;	
			$oRecord->addContent($oField_clone_by_updater_service);
			
			$cm->oPage->addContent($oRecord);
			
			return true;
		}
	}
}

function interface_set_public_field($oField, $db) {
	$res = $db->getField("name", "Text", true);

	if($db->getField("public", "Number", true)) {
		if(strlen($db->getField("public_cover", "Text", true))) {
			if(strpos($db->getField("public_cover", "Text", true), "://") === false) {
				$showfile_url = CM_SHOWFILES . "/32x32";
			} else {
				$showfile_url = "";
			}
			$public_image = '<img src="' . $showfile_url . $db->getField("public_cover", "Text", true) . '" />';
		}
		if(strlen($db->getField("public_description", "Text", true))) {
			$public_description = '<p>' . $db->getField("public_description", "Text", true) . '</p>';
		}
		if(strlen($db->getField("public_link_doc", "Text", true))) {
			$public_link = '<a href="' . $db->getField("public_link_doc", "Text", true) . '">' . ffTemplate::_get_word_by_code("public_link_doc") . '</a>';
		}

		if(strlen($public_image) || strlen($public_description) || strlen($public_link)) {
			if(strlen($db->getField("name", "Text", true))) {
				$name = '<h3>' . $db->getField("name", "Text", true) . "</h3>";
			}

			$description = $name . $public_description . $public_link;
			if(strlen($public_image)) {
				$description = '<div class="public-description">' . $description . '</div>';
			}
			
			$res = '<div class="public">' . $public_image . $description . '</div>';
		}
	} else {
		if(strlen($db->getField("public_cover", "Text", true))) {
			if(strpos($db->getField("public_cover", "Text", true), "://") === false) {
				$showfile_url = CM_SHOWFILES . "/32x32";
			} else {
				$showfile_url = "";
			}
			$public_image = '<img src="' . $showfile_url . str_replace("/100x100/", "/32x32/", $db->getField("public_cover", "Text", true)) . '" />';
		}

		$res = $public_image . $db->getField("name", "Text", true);
	}
	return $res;
}

