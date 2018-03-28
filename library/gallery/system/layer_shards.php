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


function system_layer_shards($shards) {
	$response = array();

	system_layer_shards_page_by_referer();

	if(!is_array($shards) && strlen($shards)) {
		$schema = cache_get_settings();
		$arrShard 					= explode("/", ltrim($shards, "/"), 2);
		$service["opt"]["type"]		= $arrShard[0];
		$service["opt"]["path"] 	= $arrShard[1];
		$selective 					= $shards;
		$shards 					= array();

		if($schema["page"]["/" . $service["opt"]["type"]]["group"] == "shard") {
			$shards[$schema["page"]["/" . $service["opt"]["type"]]["name"]][$service["opt"]["path"]] = "/" . $service["opt"]["type"];
		}
	}

	if(is_array($shards) && count($shards)) {
		check_function("system_get_sections");
		$where = array(
			"anagraph" 									=> array()
			, "vgallery" 								=> array()
			, "blocks" 									=> array(
				"key" 									=> array()
				, "name" 								=> array()
			)
		);
		foreach($shards AS $type => $blocks) {
			switch($type) {
				case "applet":
				case "marker":
				case "publish":
				case "menu":
				case "album":
				case "gallery":
				case "tag":
				case "place":
					$blocks_by_type[$type][] = $blocks;
					break;
				case "anagraph":
					$where["anagraph"]					= $where["anagraph"] + $blocks;
					break;
				case "block":
					$where["blocks"]["name"] 			= $where["blocks"]["name"] + $blocks;
					break;
				default:
					$where["vgallery"]					= $where["vgallery"] + $blocks;


			}
		}
		$where["preload"] 								= true;
		$where["xhr"] 									= !$selective;

		$template = system_get_blocks(null, $where);
		if(is_array($template["buffer"]["blocks"]))
			$response = $template["buffer"]["blocks"];

		if(is_array($blocks_by_type) && count($blocks_by_type)) {
			foreach($blocks_by_type AS $type => $blocks) {
				$buffer[$type] = call_user_func_array(
					"system_layer_shards_" . $type
					, array(
						$blocks
					)
				);
			}
			$response["altri"] = $buffer;
		}
	}

	return ($selective
		? $response[$selective]
		: $response
	);
}

function system_layer_shards_page_by_referer() {
	static $referer = null;

	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");

	if(!$referer) {
		$referer 										= parse_url($_SERVER["HTTP_REFERER"]);
		$globals->user_path 	            			= $referer["path"];
		$globals->settings_path							= $cm->path_info;
		$globals->page 			           				= cache_get_page_properties($referer["path"], true);
		$globals->locale 		            			= cache_get_locale($globals->page, DOMAIN_NAME); //pulisce il percorso dalla lingua

		parse_str($referer["query"]						, $_GET);
		rewrite_request($globals->page["strip_path"]);
	}

	$cm->oPage->theme 									= FRONTEND_THEME;
}

/*function system_layer_shards2($shards, $referer = null) {
	$globals = ffGlobals::getInstance("gallery");

	if(!$referer)
		$referer = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_PATH);

	$shards_by_type = array(
		"block"										=> null
		//, "anagraph" 								=> true
		//, "gallery" 								=> true
		//, "publish" 								=> true
		, "marker" 									=> true
		, "menu" 									=> true
		, "album"									=> true
		, "tag"										=> true
		, "place"									=> true

	);
	$params = array(
		"search" 									=> $globals->search
		, "navigation" 								=> $globals->navigation
		, "referer" 								=> $referer
	);

	if(!is_array($shards))
		$shards[] = $shards;

	foreach($shards AS $shard) {
		if($shard) {
			$arrShard = explode("/", ltrim($shards, "/"), 1);
			$blocks_by_type[($shards_by_type[$arrShard[0]]
				? $shards_by_type[$arrShard[0]]
				: "unknown"
			)][] = "/" . implode("/", $arrShard[1]);
		}
	}

	if(is_array($blocks_by_type) && count($blocks_by_type)) {
		foreach($blocks_by_type AS $type => $blocks) {
			$buffer[$type] = call_user_func_array(
				"system_layer_shards_" . $type
				, array(
					"blocks" 						=> $blocks
					, "params" 						=> $params
				)
			);
		}
	}

	return $buffer;
}*/

function system_layer_shards_block_applet($blocks) {
	$globals = ffGlobals::getInstance("gallery");

	$res = array();
	foreach ($blocks AS $settings_path) {
		resolve_include_applet($settings_path);
	}
	return $res;
}

function system_layer_shards_marker($blocks) {
	$globals = ffGlobals::getInstance("gallery");
	$db = ffDB_Sql::factory();

	$res = array();

	check_function("process_vgallery_thumb");
	check_function("get_layout_settings");

	foreach ($blocks AS $settings_path) {
		$arrMap = explode("_", basename($settings_path));
		if (strlen($arrMap[0])) {
			$sSQL = "SELECT module_maps.description_limit
									, module_maps.contest
								FROM module_maps
								WHERE name = " . $db->toSql($arrMap[0]);
			$db->query($sSQL);
			if ($db->nextRecord()) {
				$data_limit = $db->getField("description_limit", "Text", true);
				/**
				 * all
				 * selected vgallery
				 * anagraph
				 */
				$contest = $db->getField("contest", "Text", true);

			}

			$sSQL = "SELECT module_maps_marker.ID_node
									, vgallery.name AS vgallery_name
								FROM module_maps_marker
									INNER JOIN vgallery_nodes ON vgallery_nodes.ID = module_maps_marker.ID_node
									INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
								WHERE module_maps_marker.smart_url = " . $db->toSql($arrMap[1]);
			$db->query($sSQL);
			if ($db->nextRecord()) {
				do {
					$vgallery_name = $db->getField("vgallery_name", "Text", true);
					$markers[$vgallery_name]["nodes"][] = $db->getField("ID_node", "Number", true);
					if ($data_limit) {
						$markers[$vgallery_name]["fields"] = explode(",", $data_limit);
					}
				} while ($db->nextRecord());

				if (is_array($markers) && count($markers)) {
					foreach ($markers AS $vgallery_name => $marker) {
						$layout = get_layout_by_block("vgallery", "/" . $vgallery_name);
						$res[$settings_path] .= process_vgallery_thumb(
							"/" . $vgallery_name
							, "vgallery"
							, array(
								"limit" 						=> $marker
								, "output" 						=> "content"
								, "vgallery_name" 				=> $vgallery_name
								, "search" 						=> $globals->search
								, "navigation" 					=> $globals->navigation
								, "template_skip_hide"			=> true
							)
							, $layout
						);
					}
				}
			}
		}
	}

	return $res;
}

function system_layer_shards_publish($blocks) {
	$res = array();

	check_function("process_vgallery_thumb");
	check_function("get_layout_settings");

	$layout = array(
		"ID" => 0
		, "prefix" => "menu"
	);

	return array();
	foreach ($blocks AS $settings_path) {
		$publishing = array();
		/*$publishing["ID"] = $publish[1];
		$publishing["src"] = $publish[0];*/
		$res[$settings_path] = process_vgallery_thumb(
			null
			, "publishing"
			, array(
				"publishing" 				=> $publishing
				, "allow_insert" 			=> false
				, "output" 					=> "content"
				, "template_skip_hide" 		=> true
			)
			, $layout
		);
	}

	return $res;
}
function system_layer_shards_menu($blocks) {
	$res = array();
	$layout = array(
		"ID" => 0
	, "prefix" => "menu"
	);

	check_function("process_vgallery_menu_child");

	foreach ($blocks AS $settings_path) {
		$res[$settings_path] = process_vgallery_menu_child(null, $settings_path, null, $layout);
	}

	return $res;
}
function system_layer_shards_album($blocks) {
	$res = array();

	foreach ($blocks AS $settings_path) {
		$layout = array(
			"ID" => 0
		, "prefix" => "menu"
		);

		if (check_function("process_gallery_menu_child"))
			$res[$settings_path] 								= process_gallery_menu_child($settings_path, null, null, $layout);
	}

	return $res;
}
function system_layer_shards_gallery($blocks) {
	$globals = ffGlobals::getInstance("gallery");
	$res = array();

	check_function("process_vgallery_thumb");
	check_function("get_layout_settings");
	foreach ($blocks AS $settings_path) {
		$layout = get_layout_by_block("files", $settings_path);
		$res[$settings_path] = process_vgallery_thumb(
			$settings_path
			, "files"
			, array(
				"vgallery_name" 				=> "files"
			, "output" 						=> "content"
			, "search" 						=> $globals->search
			, "navigation" 					=> $globals->navigation
			, "template_skip_hide" 			=> true
			)
			, $layout
		);
	}

	return $res;
}
function system_layer_shards_tag($blocks) {
	$res = array();

	check_function("process_landing_page");

	foreach ($blocks AS $settings_path) {
		if(ffCommon_dirname($settings_path) == "/") {
			$landing_path 										= $settings_path;
			$landing_group 										= null;
		} else {
			$landing_path 										= ffCommon_dirname($settings_path);
			$landing_group 										= basename($settings_path);
		}

		$res[$settings_path] 									= process_landing_tag_content_by_type($landing_path, $landing_group);
	}

	return $res;
}
function system_layer_shards_place($blocks) {
	$res = array();

	foreach ($blocks AS $settings_path) {

	}
	return $res;
}


function system_layer_shards_unknown($blocks, $params) {

}

