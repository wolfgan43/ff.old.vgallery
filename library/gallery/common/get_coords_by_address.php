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
function get_coords_by_address($address, $out = "coord", $key = null) {
	$vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");

	if(!$key && check_function("get_webservices")) {
		$services_params = get_webservices("google.maps");
		if($services_params["enable"] && strlen($services_params["key"])) {
			$key = $services_params["key"];
		}
	}

	if(is_array($address)) {
		$params = "latlng=" . $address["lat"] . "," . $address["lng"];
	} else {
		$params = "address=" . str_replace(" ", "+", $address);
	}

	if($key)
		$params .= "&key=" . $key;

	if(check_function("get_locale")) {
		$lang = get_locale("lang");
		$params .= "&language=" . $lang[LANGUAGE_DEFAULT]["tiny_code"];
	}			
		
 	$request_url = "https://maps.google.com/maps/api/geocode/json?" . $params;

    // get the json response
    $resp_json = file_get_contents($request_url);
    // decode the json
    $resp = json_decode($resp_json, true);
    // response status will be 'OK', if able to geocode given address 
    if($resp['status']=='OK') {
    	$res["coord"]["title"] = $resp['results'][0]['formatted_address'];
    	$res["coord"]["lat"] = $resp['results'][0]['geometry']['location']['lat'];
    	$res["coord"]["lng"] = $resp['results'][0]['geometry']['location']['lng'];
    	$res["coord"]["zoom"] = 10;
    	
    	if(is_array($resp['results'][0]["address_components"]) && count($resp['results'][0]["address_components"])) {
    		foreach($resp['results'][0]["address_components"] AS $address_component) {
    			switch($address_component["types"][0]) {
    				case "street_number":
						$res["place"]["address"]["num"] = $address_component["long_name"];
    					break;
    				case "route":
    					$res["place"]["address"]["name"] = $address_component["long_name"];
    					break;
    				case "postal_code":
    					$res["place"]["address"]["cap"] = $address_component["long_name"];
    					break;
    				case "administrative_area_level_3":
    				case "locality":
    				case "colloquial_area":
    					$res["place"]["city"]["name"] = str_replace("-", " ", $address_component["long_name"]);
    					break;
    				case "administrative_area_level_2":
    					$province = str_replace(array("Provincia di ", "Province of "), "", $address_component["long_name"]);
    					$res["place"]["prov"]["name"] = str_replace("-", " ", $province);
    					$res["place"]["prov"]["sigle"] = $address_component["short_name"];
    					break;
    				case "administrative_area_level_1":
    					$res["place"]["region"]["name"] = str_replace("-", " ", $address_component["long_name"]);
    					break;
    				case "county":
    				case "country":
    					$res["place"]["state"]["name"] = $address_component["long_name"];
    					$res["place"]["state"]["sigle"] = $address_component["short_name"];
    					break;
    			}
    		}

    		if(!$res["place"]["city"]["name"]) {
    			foreach($resp['results'][0]["address_components"] AS $address_component) {
					if($address_component["types"][2] == "sublocality_level_1") {
						$res["place"]["city"]["name"] = str_replace("-", " ", $address_component["long_name"]);
						break;
					}
				}
    		}

			if($res["place"]["city"]["name"]) {
				$res["place"]["city"]["coord"] = $res["coord"];
			}
    		if(!$res["place"]["prov"]["name"]) {
    			if($res["place"]["city"]["name"])
    				$res["place"]["prov"]["name"] = $res["place"]["city"]["name"];
    			else
    				$res["place"]["prov"]["name"] = $res["place"]["region"]["name"];
    				
				if(!$arrPlace["prov"]["sigle"]) {
					if(strpos($res["prov"]["name"], " ") !== false) {
						$arrSigle = explode(" ", $res["prov"]["name"]);
						$arrPlace["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $arrSigle[0]), 0, 1) . substr(str_replace($vowels, "", $arrSigle[1]), 0, 1));
					}

					if(strlen($arrPlace["prov"]["sigle"]) != 2)
						$arrPlace["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $res["prov"]["name"]), 0, 2 ));
				}
			}

			if($arrPlace["prov"]["sigle"])
				$arrPlace["prov"]["sigle"] = strtoupper(ffCommon_url_rewrite($arrPlace["prov"]["sigle"]));
			
    		if(!$res["place"]["city"]["name"])
    			$res["place"]["city"]["name"] = $res["place"]["prov"]["name"];
    		if(!$res["place"]["city"]["name"])
    			$res["place"]["city"]["name"] = $res["place"]["region"]["name"];
    		if(!$res["place"]["city"]["name"])
    			$res["place"]["city"]["name"] = "unknow";

    	}
    	
    	if(!$out)
    		return $res;
    	else
    		return $res[$out];
    		    	
	} elseif($resp['status']=='OVER_QUERY_LIMIT') {
		return false;
	}
}

function get_coords_by_province($string) {
	$arrCoords = array();
	
	$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.coord_lat
					, " . FF_SUPPORT_PREFIX . "province.coord_lng
				FROM " . FF_SUPPORT_PREFIX . "province
				WHERE " . FF_SUPPORT_PREFIX . "province.smart_url = " . $db->toSql(ffCommon_url_rewrite($string));
	$db->query($sSQL);
	if($db->nextRecord()) {
		$arrCoords["lat"] = $db->getField("coord_lat", "Text", true);
		$arrCoords["lng"] = $db->getField("coord_lng", "Text", true);
		$arrCoords["zoom"] = 10;
	}
	return $arrCoords;
}

function get_google_address_info($adress, $use_db = false, $add_info_to_db = false, $keys = null) {
	//$vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");
	if(!$keys && check_function("get_webservices")) {
		$service = get_webservices("google.maps");
		if($service["enable"])
			$keys = ($service["key.server"] ? $service["key.server"] : $service["key"]);
	}
	
	if(is_array($keys)) {
		foreach($keys AS $key) {
			$res = get_coords_by_address($adress, null, $key);
			if($res)
				break;
		}
	} else {
		$res = get_coords_by_address($adress, null, $keys);
	}
	$address = $res["coord"];
	
	/*
	$arrString = explode(",", $adress); 

	$arrAddress = explode(" ", trim($arrString[0]));
	if(is_numeric($arrAddress[0]))
	{
		$res["address"]["num"] 																											= $arrAddress[0];
		unset($arrAddress[0]);
		$res["address"]["name"] 																										= implode(" " , $arrAddress);

		switch(count($arrString)) {
			case 3:
				$res["address"]["num"] 																									= "";
				$res["state"]["name"] 																									= $arrString[2];
				$arrProvince																											= explode(" ", trim($arrString[1]));
				break;
			case 4:
			default:
				$res["address"]["num"] 																									= $arrString[1];
				$res["state"]["name"] 																									= $arrString[3];
				$arrProvince																											= explode(" ", trim($arrString[2]));
		}
		
		$res["state"]["name"] 																											= $arrString[2];
		
		$arrProvince																													= explode(" ", trim($arrString[1]));
		
	} else {
		$res["address"]["name"] 																										= $arrString[0];
		switch(count($arrString)) {
			case 3:
				$res["address"]["num"] 																									= "";
				$res["state"]["name"] 																									= $arrString[2];
				$arrProvince																											= explode(" ", trim($arrString[1]));
				break;
			case 4:
			default:
				$res["address"]["num"] 																									= $arrString[1];
				$res["state"]["name"] 																									= $arrString[3];
				$arrProvince																											= explode(" ", trim($arrString[2]));
		}
		
		if(strlen($arrProvince[count($arrProvince) - 1]) == 2) {
			$res["prov"]["sigle"] 																										= $arrProvince[count($arrProvince) - 1];
			unset($arrProvince[count($arrProvince) - 1]);
		}

		$res["address"]["cap"] 																											= $arrProvince[0];
		unset($arrProvince[0]);
		
		$res["city"]["name"] 																											= implode(" ", $arrProvince);
		
		if(!$res["prov"])
			$res["prov"]["sigle"] 																										= strtoupper(substr(str_replace($vowels, "", $res["city"]["name"]), 0, 2));
	}*/
		
	if($res && $use_db)
		$res = resolve_tbl_loc_by_address_info($res["place"], $add_info_to_db);
	
	if($address)
		$res["address"] = $address;	
				
	return $res;
}

function resolve_tbl_loc_by_address_info($arrPlace, $add_info_to_db = false) 
{
	$db = ffDB_Sql::factory();
	$vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");

	if($arrPlace["state"]["name"]) {
		$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
					, " . FF_SUPPORT_PREFIX . "state.name
					, " . FF_SUPPORT_PREFIX . "state.sigle
					, " . FF_SUPPORT_PREFIX . "state.permalink
					, " . FF_SUPPORT_PREFIX . "state.coord_title
					, " . FF_SUPPORT_PREFIX . "state.coord_lat
					, " . FF_SUPPORT_PREFIX . "state.coord_lng
					, " . FF_SUPPORT_PREFIX . "state.coord_zoom
				FROM " . FF_SUPPORT_PREFIX . "state
				WHERE " . FF_SUPPORT_PREFIX . "state.smart_url = " . $db->toSql(ffCommon_url_rewrite($arrPlace["state"]["name"]));
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrPlace["state"]["ID"] 																								= $db->getField("ID", "Number", true);
			$arrPlace["state"]["name"] 																								= $db->getField("name", "Text", true);
			$arrPlace["state"]["sigle"] 																							= $db->getField("sigle", "Text", true);
			$arrPlace["state"]["permalink"] 																						= $db->getField("permalink", "Text", true);
			$arrPlace["state"]["coord"] 																							= array(
																																		"title" 	=> $db->getField("coord_title", "Text", true)
																																		, "lat" 	=> $db->getField("coord_lat", "Text", true)
																																		, "lng" 	=> $db->getField("coord_lng", "Text", true)
																																		, "zoom" 	=> $db->getField("coord_zoom", "Text", true)
																																	); 
		}
	}

	if($arrPlace["prov"]["sigle"] || $arrPlace["prov"]["name"]) {
		$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.ID
					, " . FF_SUPPORT_PREFIX . "province.ID_region
					, " . FF_SUPPORT_PREFIX . "province.ID_state
					, " . FF_SUPPORT_PREFIX . "province.name
					, " . FF_SUPPORT_PREFIX . "province.sigle
					, " . FF_SUPPORT_PREFIX . "province.permalink
					, " . FF_SUPPORT_PREFIX . "province.coord_title
					, " . FF_SUPPORT_PREFIX . "province.coord_lat
					, " . FF_SUPPORT_PREFIX . "province.coord_lng
					, " . FF_SUPPORT_PREFIX . "province.coord_zoom
				FROM " . FF_SUPPORT_PREFIX . "province
				WHERE " . ($arrPlace["prov"]["name"]
						? FF_SUPPORT_PREFIX . "province.smart_url = " . $db->toSql(ffCommon_url_rewrite($arrPlace["prov"]["name"])) 
						: FF_SUPPORT_PREFIX . "province.sigle = " . $db->toSql($arrPlace["prov"]["sigle"]) 
							. ($arrPlace["state"]["ID"] 
								? " AND " . FF_SUPPORT_PREFIX . "province.ID_state = " . $db->toSql($arrPlace["state"]["ID"], "Number")
								: ""
							)
					);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrPlace["prov"]["ID"] 																								= $db->getField("ID", "Number", true);
			$arrPlace["prov"]["name"] 																								= $db->getField("name", "Text", true);
			$arrPlace["prov"]["sigle"] 																								= $db->getField("sigle", "Text", true);
			$arrPlace["prov"]["permalink"] 																							= $db->getField("permalink", "Text", true);
			$arrPlace["prov"]["coord"] 																								= array(
																																		"title" 	=> $db->getField("coord_title", "Text", true)
																																		, "lat" 	=> $db->getField("coord_lat", "Text", true)
																																		, "lng" 	=> $db->getField("coord_lng", "Text", true)
																																		, "zoom" 	=> $db->getField("coord_zoom", "Text", true)
																																	); 
			if(!$arrPlace["region"]["ID"] && $db->getField("ID_region", "Number", true))
				$arrPlace["region"]["ID"] 																							= $db->getField("ID_region", "Number", true);
			if(!$arrPlace["state"]["ID"] && $db->getField("ID_state", "Number", true))
				$arrPlace["state"]["ID"] 																							= $db->getField("ID_state", "Number", true);
		}
	}	

	if($arrPlace["city"]["name"]) {
		$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "city.ID
					, " . FF_SUPPORT_PREFIX . "city.ID_region
					, " . FF_SUPPORT_PREFIX . "city.ID_province
					, " . FF_SUPPORT_PREFIX . "city.ID_state
					, " . FF_SUPPORT_PREFIX . "city.name
					, " . FF_SUPPORT_PREFIX . "city.permalink
					, " . FF_SUPPORT_PREFIX . "city.coord_title
					, " . FF_SUPPORT_PREFIX . "city.coord_lat
					, " . FF_SUPPORT_PREFIX . "city.coord_lng
					, " . FF_SUPPORT_PREFIX . "city.coord_zoom
				FROM " . FF_SUPPORT_PREFIX . "city
				WHERE " . FF_SUPPORT_PREFIX . "city.smart_url = " . $db->toSql(ffCommon_url_rewrite($arrPlace["city"]["name"])) 
					. (0 && $arrPlace["state"]["ID"] 
						? " AND " . FF_SUPPORT_PREFIX . "city.ID_state = " . $db->toSql($arrPlace["state"]["ID"], "Number")
						: ""
					);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrPlace["city"]["ID"] 																								= $db->getField("ID", "Number", true);
			$arrPlace["city"]["name"] 																								= $db->getField("name", "Text", true);
			$arrPlace["city"]["permalink"] 																							= $db->getField("permalink", "Text", true);
			$arrPlace["city"]["coord"] 																								= array(
																																		"title" 	=> $db->getField("coord_title", "Text", true)
																																		, "lat" 	=> $db->getField("coord_lat", "Text", true)
																																		, "lng" 	=> $db->getField("coord_lng", "Text", true)
																																		, "zoom" 	=> $db->getField("coord_zoom", "Text", true)
																																	);
			if(!$arrPlace["region"]["ID"] && $db->getField("ID_region", "Number", true))
				$arrPlace["region"]["ID"] 																							= $db->getField("ID_region", "Number", true);
				
			if(!$arrPlace["state"]["ID"] && $db->getField("ID_state", "Number", true))
				$arrPlace["state"]["ID"] 																							= $db->getField("ID_state", "Number", true);

			if(!$arrPlace["prov"]["ID"]) {
				$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.ID
					, " . FF_SUPPORT_PREFIX . "province.ID_region
					, " . FF_SUPPORT_PREFIX . "province.ID_state
					, " . FF_SUPPORT_PREFIX . "province.name
					, " . FF_SUPPORT_PREFIX . "province.permalink
					, " . FF_SUPPORT_PREFIX . "province.coord_title
					, " . FF_SUPPORT_PREFIX . "province.coord_lat
					, " . FF_SUPPORT_PREFIX . "province.coord_lng
					, " . FF_SUPPORT_PREFIX . "province.coord_zoom
				FROM " . FF_SUPPORT_PREFIX . "province
				WHERE " . FF_SUPPORT_PREFIX . "province.ID = " . $db->toSql($db->getField("ID_province"));
				$db->query($sSQL);
				if($db->nextRecord()) {
					$arrPlace["prov"]["ID"] 																						= $db->getField("ID", "Number", true);
					$arrPlace["prov"]["name"] 																						= $db->getField("name", "Text", true);
					$arrPlace["prov"]["permalink"] 																					= $db->getField("permalink", "Text", true);
					$arrPlace["prov"]["coord"] 																						= array(
																																		"title" 	=> $db->getField("coord_title", "Text", true)
																																		, "lat" 	=> $db->getField("coord_lat", "Text", true)
																																		, "lng" 	=> $db->getField("coord_lng", "Text", true)
																																		, "zoom" 	=> $db->getField("coord_zoom", "Text", true)
																																	);
					if(!$arrPlace["region"]["ID"] && $db->getField("ID_region", "Number", true))
						$arrPlace["region"]["ID"] 																					= $db->getField("ID_region", "Number", true);
					if(!$arrPlace["state"]["ID"] && $db->getField("ID_state", "Number", true))
						$arrPlace["state"]["ID"] 																					= $db->getField("ID_state", "Number", true);
				}		
			}
		}
	}	
	
	if($arrPlace["region"]["ID"] || $arrPlace["region"]["name"]) {
		$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "region.ID
					, " . FF_SUPPORT_PREFIX . "region.ID_state
					, " . FF_SUPPORT_PREFIX . "region.name
					, " . FF_SUPPORT_PREFIX . "region.permalink
					, " . FF_SUPPORT_PREFIX . "region.coord_title
					, " . FF_SUPPORT_PREFIX . "region.coord_lat
					, " . FF_SUPPORT_PREFIX . "region.coord_lng
					, " . FF_SUPPORT_PREFIX . "region.coord_zoom
				FROM " . FF_SUPPORT_PREFIX . "region
				WHERE " . ($arrPlace["region"]["ID"] 
					? FF_SUPPORT_PREFIX . "region.ID = " . $db->toSql($arrPlace["region"]["ID"], "Number")
					: FF_SUPPORT_PREFIX . "region.smart_url = " . $db->toSql(ffCommon_url_rewrite($arrPlace["region"]["name"]))
						. (0 && $arrPlace["state"]["ID"] 
							? " AND " . FF_SUPPORT_PREFIX . "region.ID_state = " . $db->toSql($arrPlace["state"]["ID"], "Number")
							: ""
						)
				);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrPlace["region"]["ID"] 																								= $db->getField("ID", "Number", true);
			$arrPlace["region"]["name"] 																							= $db->getField("name", "Text", true);
			$arrPlace["region"]["permalink"] 																						= $db->getField("permalink", "Text", true);
			$arrPlace["region"]["coord"] 																							= array(
																																		"title" 	=> $db->getField("coord_title", "Text", true)
																																		, "lat" 	=> $db->getField("coord_lat", "Text", true)
																																		, "lng" 	=> $db->getField("coord_lng", "Text", true)
																																		, "zoom" 	=> $db->getField("coord_zoom", "Text", true)
																																	);
			if(!$arrPlace["state"]["ID"]) {
				$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
							, " . FF_SUPPORT_PREFIX . "state.name
							, " . FF_SUPPORT_PREFIX . "state.sigle
							, " . FF_SUPPORT_PREFIX . "state.permalink
							, " . FF_SUPPORT_PREFIX . "state.coord_title
							, " . FF_SUPPORT_PREFIX . "state.coord_lat
							, " . FF_SUPPORT_PREFIX . "state.coord_lng
							, " . FF_SUPPORT_PREFIX . "state.coord_zoom
						FROM " . FF_SUPPORT_PREFIX . "state
						WHERE " . FF_SUPPORT_PREFIX . "state.ID = " . $db->toSql($db->getField("ID_state"));
				$db->query($sSQL);
				if($db->nextRecord()) {
					$arrPlace["state"]["ID"] 																						= $db->getField("ID", "Number", true);
					$arrPlace["state"]["name"] 																						= $db->getField("name", "Text", true);
					$arrPlace["state"]["sigle"] 																					= $db->getField("sigle", "Text", true);
					$arrPlace["state"]["permalink"] 																				= $db->getField("permalink", "Text", true);
					$arrPlace["state"]["coord"] 																					=  array(
																																		"title" 	=> $db->getField("coord_title", "Text", true)
																																		, "lat" 	=> $db->getField("coord_lat", "Text", true)
																																		, "lng" 	=> $db->getField("coord_lng", "Text", true)
																																		, "zoom" 	=> $db->getField("coord_zoom", "Text", true)
																																	);
				}			
			}
		}	
	}
	
	if($arrPlace["state"]["ID"] && !$arrPlace["state"]["name"]) {
		$sSQL = "SELECT " . FF_SUPPORT_PREFIX . "state.ID
					, " . FF_SUPPORT_PREFIX . "state.name
					, " . FF_SUPPORT_PREFIX . "state.sigle
					, " . FF_SUPPORT_PREFIX . "state.permalink
					, " . FF_SUPPORT_PREFIX . "state.coord_title
					, " . FF_SUPPORT_PREFIX . "state.coord_lat
					, " . FF_SUPPORT_PREFIX . "state.coord_lng
					, " . FF_SUPPORT_PREFIX . "state.coord_zoom
				FROM " . FF_SUPPORT_PREFIX . "state
				WHERE " . FF_SUPPORT_PREFIX . "state.ID = " . $db->toSql($arrPlace["state"]["ID"], "Number");
		$db->query($sSQL);
		if($db->nextRecord()) {
			$arrPlace["state"]["ID"] 																								= $db->getField("ID", "Number", true);
			$arrPlace["state"]["name"] 																								= $db->getField("name", "Text", true);
			$arrPlace["state"]["sigle"] 																							= $db->getField("sigle", "Text", true);
			$arrPlace["state"]["permalink"] 																						= $db->getField("permalink", "Text", true);
			$arrPlace["state"]["coord"] 																							= array(
																																		"title" 	=> $db->getField("coord_title", "Text", true)
																																		, "lat" 	=> $db->getField("coord_lat", "Text", true)
																																		, "lng" 	=> $db->getField("coord_lng", "Text", true)
																																		, "zoom" 	=> $db->getField("coord_zoom", "Text", true)
																																	);
		}
	}
	if($add_info_to_db) {
		if(!$arrPlace["state"]["ID"] && $arrPlace["state"]["name"]) {
			$sSQL = "INSERT INTO " . FF_SUPPORT_PREFIX . "state
					(
						ID
						, name
						, sigle
						, smart_url
						, permalink
					) VALUES (
						null
						, " . $db->toSql(ucwords(str_replace("-", " ", $arrPlace["state"]["name"]))) . "
						, " . $db->toSql($arrPlace["state"]["sigle"]) . "
						, " . $db->toSql(ffCommon_url_rewrite($arrPlace["state"]["name"])) . "
						, " . $db->toSql($arrPlace["state"]["permalink"]) . "
					)";
			$db->execute($sSQL);
			$arrPlace["state"]["ID"] = $db->getInsertID(true);
		}
		
		if(!$arrPlace["region"]["ID"] && $arrPlace["region"]["name"]) {
			$sSQL = "INSERT INTO " . FF_SUPPORT_PREFIX . "region
					(
						ID
						, name
						, smart_url
						, ID_state
						, permalink
					) VALUES (
						null
						, " . $db->toSql(ucwords(str_replace("-", " ", $arrPlace["region"]["name"]))) . "
						, " . $db->toSql(ffCommon_url_rewrite($arrPlace["region"]["name"])) . "
						, " . $db->toSql($arrPlace["state"]["ID"], "Number") . "
						, " . $db->toSql($arrPlace["region"]["permalink"]) . "
					)";
			$db->execute($sSQL);
			$arrPlace["region"]["ID"] = $db->getInsertID(true);
		}

		if(!$arrPlace["prov"]["ID"]) {
			$arrPlace["prov"]["name"] = ($arrPlace["prov"]["name"] ? $arrPlace["prov"]["name"] : $arrPlace["city"]["name"]);
			if(!$arrPlace["prov"]["sigle"]) {
				if(strpos($arrPlace["prov"]["name"], " ") !== false) {
					$arrSigle = explode(" ", $arrPlace["prov"]["name"]);
					$arrPlace["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $arrSigle[0]), 0, 1) . substr(str_replace($vowels, "", $arrSigle[1]), 0, 1));
				}

				if(strlen($arrPlace["prov"]["sigle"]) != 2)
					$arrPlace["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $arrPlace["prov"]["name"]), 0, 2 ));
			}

			if($arrPlace["prov"]["sigle"])
				$arrPlace["prov"]["sigle"] = strtoupper(ffCommon_url_rewrite($arrPlace["prov"]["sigle"]));

			if($arrPlace["prov"]["name"] && $arrPlace["prov"]["sigle"]) {
				$sSQL = "INSERT INTO " . FF_SUPPORT_PREFIX . "province
						(
							ID
							, name
							, sigle
							, smart_url
							, ID_region
							, ID_state
							, permalink
						) VALUES (
							null
							, " . $db->toSql(ucwords(str_replace("-", " ", $arrPlace["prov"]["name"]))) . "
							, " . $db->toSql($arrPlace["prov"]["sigle"]). "
							, " . $db->toSql(ffCommon_url_rewrite($arrPlace["prov"]["name"])) . "
							, " . $db->toSql($arrPlace["region"]["ID"], "Number") . "
							, " . $db->toSql($arrPlace["state"]["ID"], "Number") . "
							, " . $db->toSql($arrPlace["prov"]["permalink"]) . "
						)";
				$db->execute($sSQL);
				$arrPlace["prov"]["ID"] = $db->getInsertID(true);
			}
		}

		if(!$arrPlace["city"]["ID"] && $arrPlace["city"]["name"]) {
			$arrPlace["prov"]["name"] = ($arrPlace["prov"]["name"] ? $arrPlace["prov"]["name"] : $arrPlace["city"]["name"]);
			if(!$arrPlace["prov"]["sigle"]) {
				if(strpos($arrPlace["prov"]["name"], " ") !== false) {
					$arrSigle = explode(" ", $arrPlace["prov"]["name"]);
					$arrPlace["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $arrSigle[0]), 0, 1) . substr(str_replace($vowels, "", $arrSigle[1]), 0, 1));
				}

				if(strlen($arrPlace["prov"]["sigle"]) != 2)
					$arrPlace["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $arrPlace["prov"]["name"]), 0, 2 ));
			}

			if($arrPlace["prov"]["sigle"])
				$arrPlace["prov"]["sigle"] = strtoupper(ffCommon_url_rewrite($arrPlace["prov"]["sigle"]));
			
			$sSQL = "INSERT INTO " . FF_SUPPORT_PREFIX . "city
					(
						ID
						, name
						, smart_url
						, ID_province
						, ID_region
						, ID_state
						, cap
						, province_name
						, province_sigle
						, permalink
					) VALUES (
						null
						, " . $db->toSql(ucwords(str_replace("-", " ", $arrPlace["city"]["name"]))) . "
						, " . $db->toSql(ffCommon_url_rewrite($arrPlace["city"]["name"])) . "
						, " . $db->toSql($arrPlace["prov"]["ID"], "Number") . "
						, " . $db->toSql($arrPlace["region"]["ID"], "Number") . "
						, " . $db->toSql($arrPlace["state"]["ID"], "Number") . "
						, " . $db->toSql($arrPlace["address"]["cap"]) . "
						, " . $db->toSql($arrPlace["prov"]["name"]) . "
						, " . $db->toSql($arrPlace["prov"]["sigle"]) . "
						, " . $db->toSql($arrPlace["city"]["permalink"]) . "
					)";
			$db->execute($sSQL);
			$arrPlace["city"]["ID"] = $db->getInsertID(true);
		}
	}
	
	return $arrPlace;
}




function set_city_by_address_info($address, $ID_city = null, $ID_province = null) 
{
    $db = ffDB_Sql::factory();
    $vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");

    $place = get_google_address_info($address, true, true);

    if(!$place["city"]["ID"] && $place["city"]["name"]) {
        $place["city"]["ID"] = $ID_city;

        $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "city SET
                    name = " . $db->toSql($place["city"]["name"]) . "
                    , smart_url = " . $db->toSql(ffCommon_url_rewrite($place["city"]["name"])) . "
                WHERE " . FF_SUPPORT_PREFIX . "city.ID = " . $db->toSql($place["city"]["ID"], "Number");
        $db->execute($sSQL);
    }

    if($place["city"]["ID"]) {
        $arrUpdate = array();

        if($place["state"]["ID"])
            $arrUpdate["state"] = "ID_state = " . $db->toSql($place["state"]["ID"], "Number");
        if($place["region"]["ID"])
            $arrUpdate["region"] = "ID_region = " . $db->toSql($place["region"]["ID"], "Number");
        if($place["prov"]["ID"])
            $arrUpdate["province"] = "ID_province = " . $db->toSql($place["prov"]["ID"], "Number");

        if(is_array($arrUpdate) && count($arrUpdate)) {
            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "city SET
                        " . implode(", ", $arrUpdate) . "
                    WHERE " . FF_SUPPORT_PREFIX . "city.ID = " . $db->toSql($ID_city && $ID_city != $place["city"]["ID"]
                            ? $ID_city
                            : $place["city"]["ID"]
                        , "Number");
            $db->execute($sSQL);
        }		
    }				

    if($place["prov"]["ID"]) {
        $arrUpdate = array();

        if($place["state"]["ID"])
            $arrUpdate["state"] = "ID_state = " . $db->toSql($place["state"]["ID"], "Number");
        if($place["region"]["ID"])
            $arrUpdate["region"] = "ID_region = " . $db->toSql($place["region"]["ID"], "Number");

        if(!$place["prov"]["sigle"]) {
            if(strpos($place["prov"]["name"], " ") !== false) {
                $arrSigle = explode(" ", $place["prov"]["name"]);
                $place["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $arrSigle[0]), 0, 1) . substr(str_replace($vowels, "", $arrSigle[1]), 0, 1));
            }

            if(strlen($place["prov"]["sigle"]) != 2)
                $place["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $place["prov"]["name"]), 0, 2 ));
        }

        if($place["prov"]["sigle"]) 
            $arrUpdate["sigle"] = "sigle = IF(sigle = '', " . $db->toSql(strtoupper(ffCommon_url_rewrite($place["prov"]["sigle"]))) . ", sigle)";

        if(is_array($arrUpdate) && count($arrUpdate)) {				
            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "province SET
                        " . implode(", ", $arrUpdate) . "
                    WHERE " . FF_SUPPORT_PREFIX . "province.ID = " . $db->toSql($ID_province && $ID_province != $place["prov"]["ID"]
                            ? $ID_province
                            : $place["prov"]["ID"]
                        , "Number");
            $db->execute($sSQL);									
        }
    }				

    if($place["region"]["ID"]) {
        $arrUpdate = array();

        if($place["state"]["ID"])
            $arrUpdate["state"] = "ID_state = " . $db->toSql($place["state"]["ID"], "Number");

        if(is_array($arrUpdate) && count($arrUpdate)) {				
            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "region SET
                        " . implode(", ", $arrUpdate) . "
                    WHERE " . FF_SUPPORT_PREFIX . "region.ID = " . $db->toSql($place["region"]["ID"], "Number");
            $db->execute($sSQL);									
        }
    }				

    $province = ($place["prov"]["ID"]
        ? $place["prov"]["ID"]
        : $ID_province
    );

    if($province) {			
        $sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.*
                FROM " . FF_SUPPORT_PREFIX . "province
                WHERE " . FF_SUPPORT_PREFIX . "province.ID = " . $db->toSql($province, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $province_name = $db->getField("name", "Text", true);
            $province_smart_url = $db->getField("smart_url", "Text", true);
            $province_sigle = $db->getField("sigle", "Text", true);

            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "city SET
                        province_name = " . $db->toSql($province_name) . "
                        , province_smart_url = " . $db->toSql($province_smart_url) . "
                        , province_sigle = " . $db->toSql($province_sigle) . "
                    WHERE " . FF_SUPPORT_PREFIX . "city.ID = " . $db->toSql($ID_city, "Number");
            $db->execute($sSQL);
        }
    }    
}

function set_province_by_address_info($address, $ID_province = null) 
{
    $db = ffDB_Sql::factory();
    $vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");

    $place = get_google_address_info($address, true, true);

    if($place["prov"]["ID"]) {
        $arrUpdate = array();

        if($place["state"]["ID"])
            $arrUpdate["state"] = "ID_state = " . $db->toSql($place["state"]["ID"], "Number");
        if($place["region"]["ID"])
            $arrUpdate["region"] = "ID_region = " . $db->toSql($place["region"]["ID"], "Number");

        if(!$place["prov"]["sigle"]) {
            if(strpos($place["prov"]["name"], " ") !== false) {
                $arrSigle = explode(" ", $place["prov"]["name"]);
                $place["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $arrSigle[0]), 0, 1) . substr(str_replace($vowels, "", $arrSigle[1]), 0, 1));
            }

            if(strlen($place["prov"]["sigle"]) != 2)
                $place["prov"]["sigle"] = strtoupper(substr(str_replace($vowels, "", $place["prov"]["name"]), 0, 2 ));
        }

        if($place["prov"]["sigle"]) 
            $arrUpdate["sigle"] = "sigle = IF(sigle = '', " . $db->toSql(strtoupper(ffCommon_url_rewrite($place["prov"]["sigle"]))) . ", sigle)";

        if(is_array($arrUpdate) && count($arrUpdate)) {				
            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "province SET
                        " . implode(", ", $arrUpdate) . "
                    WHERE " . FF_SUPPORT_PREFIX . "province.ID = " . $db->toSql($place["prov"]["ID"], "Number");
            $db->execute($sSQL);									
        }
    }				

    if($place["region"]["ID"]) {
        $arrUpdate = array();

        if($place["state"]["ID"])
            $arrUpdate["state"] = "ID_state = " . $db->toSql($place["state"]["ID"], "Number");

        if(is_array($arrUpdate) && count($arrUpdate)) {				
            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "region SET
                        " . implode(", ", $arrUpdate) . "
                    WHERE " . FF_SUPPORT_PREFIX . "region.ID = " . $db->toSql($place["region"]["ID"], "Number");
            $db->execute($sSQL);									
        }
    }				

    $province = ($place["prov"]["ID"]
        ? $place["prov"]["ID"]
        : $ID_province
    );

    if($province) {			
        $sSQL = "SELECT " . FF_SUPPORT_PREFIX . "province.*
                FROM " . FF_SUPPORT_PREFIX . "province
                WHERE " . FF_SUPPORT_PREFIX . "province.ID = " . $db->toSql($province, "Number");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $ID_province 				= $db->getField("ID", "Number", true);
            $province_name 				= $db->getField("name", "Text", true);
            $province_smart_url 		= $db->getField("smart_url", "Text", true);
            $province_sigle 			= $db->getField("sigle", "Text", true);

            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "city SET
                        province_name = " . $db->toSql($province_name) . "
                        , province_smart_url = " . $db->toSql($province_smart_url) . "
                        , province_sigle = " . $db->toSql($province_sigle) . "
                    WHERE " . FF_SUPPORT_PREFIX . "city.ID_province = " . $db->toSql($ID_province, "Number");
            $db->execute($sSQL);
        }
    }    
    
}

function set_region_by_address_info($address) 
{
    $db = ffDB_Sql::factory();
    $vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");

    $place = get_google_address_info($address, true, true);

    if($place["region"]["ID"]) {
        $arrUpdate = array();

        if($place["state"]["ID"])
            $arrUpdate["state"] = "ID_state = " . $db->toSql($place["state"]["ID"], "Number");

        if(is_array($arrUpdate) && count($arrUpdate)) {				
            $sSQL = "UPDATE " . FF_SUPPORT_PREFIX . "region SET
                        " . implode(", ", $arrUpdate) . "
                    WHERE " . FF_SUPPORT_PREFIX . "region.ID = " . $db->toSql($place["region"]["ID"], "Number");
            $db->execute($sSQL);									
        }
    }				
}

function set_state_by_address_info($address) 
{
    $db = ffDB_Sql::factory();
    $vowels = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");

    $place = get_google_address_info($address, true, true);

}