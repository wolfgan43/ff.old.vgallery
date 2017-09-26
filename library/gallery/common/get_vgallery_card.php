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
 function get_vgallery_card($title, $cover = null, $description = null, $permalink = null, $params = null) {
  	check_function("normalize_url");

  	$framework_css = array(
  		"img" => array(
  			"col" => array(
				"xs" => 4
				, "sm" => 4
				, "md" => 3
				, "lg" => 2
			)
  			, "util" => array(
  				"align-right"
  			)
  		)
  		, "imgqrcode" => array(
  			"col" => array(
				"xs" => 4
				, "sm" => 4
				, "md" => 5
				, "lg" => 4
			)
  			, "util" => array(
  				"align-right"
  			)
  		)
  		, "desc" => array(
  			"col" => array(
				"xs" => 8
				, "sm" => 8
				, "md" => 7
				, "lg" => 8
			)
  		)
  		, "descqrcode" => array(
  			"col" => array(
				"xs" => 8
				, "sm" => 8
				, "md" => 9
				, "lg" => 10
			)
  		)
  		, "desconlyqrcode" => array(
  			"col" => array(
				"xs" => 12
				, "sm" => 12
				, "md" => 10
				, "lg" => 10
			)
  		)
  		, "desconly" => array(
  			"col" => null
  		)
  		, "qrcode" => array(
  			"col" => array(
				"xs" => 0
				, "sm" => 0
				, "md" => 2
				, "lg" => 2
			)
  		)
  	);
  	
  	if($cover)
  	{
  		if(strpos($cover, "://") === false) {
  			if(is_file(DISK_UPDIR . $cover)) {
  				$img = $cover;
			} elseif(is_dir(DISK_UPDIR . $cover)) {
				$res = glob(DISK_UPDIR . $cover . "/*.{jpg,jpeg,gif,png}", GLOB_BRACE);
				if(is_array($res) && count($res)) {
					foreach($res AS $file_path) {
						$img = $cover . "/" . basename($file_path);
						break;
					}
				}
			}

  			if(!$params["thumb"])
  				$params["thumb"] = CM_SHOWFILES . "/thumb";
		} else {
		  	$img = $cover;
		}

		if(!$title)
			$title = ucwords(str_replace("-", " ", basename($cover)));
  	}

  	$title = htmlspecialchars($title, ENT_QUOTES);

  	if($img === null && is_file(FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/images/noimg.png")) {
  		$img = "/" . FRONTEND_THEME . "/images/noimg.png";
  		if(!$params["thumb"])
  				$params["thumb"] = CM_SHOWFILES . "/thumb";
  	}  
  	
  	if(!$params["nocat"] && $permalink) {
  		$arrPermalink = explode("/", trim(ffCommon_dirname($permalink), "/"));
  		unset($arrPermalink[0]);
  		if(is_array($arrPermalink) && count($arrPermalink)) {
			foreach($arrPermalink AS $arrPermalink_value) {
				$strPermalink .= "/" . $arrPermalink_value;
				if($cat)
					$cat .= "<span> > </span>";

				$cat .= '<a href="' . normalize_url_by_current_lang($strPermalink) . '" target="_blank">' . ucwords(str_replace("-", " ", $arrPermalink_value)) . '</a>';
			}  		
  		}
  		
  		$cat = "<h6>" . $cat . "</h6>";
  	}
  	
  	if(is_array($description)) {
  		foreach($description AS $label => $value) {
  			if($label) {
  				$arrDesc[] = '<strong>' . $label . ': </strong>' . get_vgallery_card_format($value, $label);
  			} else {
  				$desc .= '<p>' . get_vgallery_card_format($value) . '</p>';
  			}
  		}
  		if(is_array($arrDesc)) {
  			$desc_col = array_fill(0,3, (int) floor(12 / count($arrDesc)));
 			$desc .= '<div class="' . cm_getClassByFrameworkCss($desc_col, "col") . '">' . implode('</div><div class="' . cm_getClassByFrameworkCss($desc_col, "col") . '">', $arrDesc) . '</div>';
		}
  		
  	} elseif(strlen($description)) {
  		$desc .= '<p>'. get_vgallery_card_format($description) . '</p>';
  	}

  	$name = '	<h4 title="'  . $title . '">' . ($permalink
  							? ($params["link"]
  								? '<a href="' . normalize_url_by_current_lang($permalink) . '" target="_blank" title="'  . $title . '">' . $title . '</a>'
  								: $title . '<a class="' . cm_getClassByFrameworkCss("right", "util") . '" href="' . normalize_url_by_current_lang($permalink) . '" target="_blank" title="'  . $title . '">' . cm_getClassByFrameworkCss("external-link", "icon-tag") . '</a>'
  							)
  							: $title
  						)
  	 			. '</h4>';
  	$image = ($img
  		 		? ($permalink && $params["link"]
  					? '<a href="' . normalize_url_by_current_lang($permalink) . '" target="_blank" title="'  . $title . '">' . '<img src="' . $params["thumb"] . $img . '" alt="' . $title . '" />' . '</a>'
  					: '<img src="' . $params["thumb"] . $img . '" alt="' . $title . '" />'
  				)
  				: ""
  		 );

	$isbn = (is_array($description["ISBN"]) && $description["ISBN"]
  				? '<img class="qrcode" src="https://chart.googleapis.com/chart?chs=80x80&cht=qr&chl=' . $description["ISBN"] . '&choe=UTF-8" alt="' . $title . '" />'
  				: ''
  			);
  	
  	if(is_array($params["icons"]) && count($params["icons"])) {
  		foreach($params["icons"] AS $icon_name => $icon_params) {
  			$icons .= '<a href="javascript:void(0);" class="' . $icon_name . '">' . cm_getClassByFrameworkCss($icon_name, "icon-tag", $icon_params) . '</a>';
  		
  		}
  	}
  		 
  	switch($params["type"]) {
		case "marker":
  			$buffer = $name . $cat
  						. ($img !== false
  							? '<div class="' . cm_getClassByDef($framework_css["img" . ($params["noqrcode"] ? "qrcode" : "")]) 
  									. ($img === null
  										? " " . cm_getClassByFrameworkCss("noimg", "icon", "4x")
  										: ""
  									) . '">'
  									. $image . '
  								</div>
  								<div class="' . cm_getClassByDef($framework_css["desc" . ($params["noqrcode"] ? "" : "qrcode")]) . '">'
  							: '<div class="' . cm_getClassByDef($framework_css["desconly" . ($params["noqrcode"] ? "" : "qrcode")]) . '">'
  						) . '
  							' . str_replace(array(" - "), array("<br />"), $desc) . $isbn . '
  						</div>'
  						. ($icons
  							? '<span class="vg-card-icons">' . $icons . '</span>'
  							: ""
  						);
			break;
		default:
  			$buffer = '<div class="vg-card">
  						' . ($img !== false
  							? '<div class="' . cm_getClassByDef($framework_css["img" . ($params["noqrcode"] ? "qrcode" : "")]) 
  									. ($img === null
  										? " " . cm_getClassByFrameworkCss("noimg", "icon", "4x")
  										: ""
  									) . '">'
  									. $image . '
  								</div>
  								<div class="' . cm_getClassByDef($framework_css["desc" . ($params["noqrcode"] ? "" : "qrcode")]) . '">'
  							: '<div class="' . cm_getClassByDef($framework_css["desconly" . ($params["noqrcode"] ? "" : "qrcode")]) . '">'
  						) . '
  							' . $name . $cat . $desc . '
  						</div>
  						' . ($isbn
  							? '<div class="' . cm_getClassByDef($framework_css["qrcode"]) . '">'
  									. $isbn
  								. '</div>'
  							: ''
  						)
  					. ($icons
  						? '<span class="vg-card-icons">' . $icons . '</span>'
  						: ""
  					)
  					. "</div>";
  	}
  	
  	
  	return $buffer;
  }
  
  
  function get_vgallery_card_by_id($nodes, $params = null) {
  	$db = ffDB_Sql::factory();
  	
  	if(is_array($nodes))
  		$ID_nodes = implode(",", $nodes);
  	else 
  		$ID_nodes = $nodes;
  	
  	$sSQL = "SELECT vgallery_nodes.ID
				, vgallery_nodes.parent AS parent
				, vgallery_nodes.name AS name
				" . (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
						? ", vgallery_nodes.permalink AS permalink
							, vgallery_nodes.meta_title AS meta_title
							, vgallery_nodes.meta_title_alt AS meta_title_alt
						"
						: ", vgallery_nodes_rel_languages.permalink AS permalink
							, vgallery_nodes_rel_languages.meta_title AS meta_title
							, vgallery_nodes_rel_languages.meta_title_alt AS meta_title_alt
						"
					) . "  	
  			FROM vgallery_nodes
  				" .  (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
		        	? ""
		        	: " LEFT JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID AND vgallery_nodes_rel_languages.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number")
			    ) . "
  			WHERE vgallery_nodes.ID IN(" . $db->toSql($ID_nodes, "Number", false) . ")";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$ID = $db->getField("ID", "Number", true);
			$buffer[$ID]["title"] = $db->getField("meta_title_alt", "Text", true);
			if(!$buffer[$ID]["title"])
				$buffer[$ID]["title"] = $db->getField("meta_title", "Text", true);

			$buffer[$ID]["permalink"] = $db->getField("permalink", "Text", true);		
			$buffer[$ID]["full_path"] = stripslash($db->getField("parent", "Text", true)) . "/" . $db->getField("name", "Text", true);
			$buffer[$ID]["params"] = $params;

			$buffer[$ID]["card"] = get_vgallery_card($buffer[$ID]["title"], $buffer[$ID]["full_path"], $buffer[$ID]["description"], $buffer[$ID]["permalink"], $buffer[$ID]["params"]);
		} while($db->nextRecord());
	} 
	
	if(is_array($nodes))
  		return $buffer;
  	else 
  		return $buffer[$nodes];
  }
  
  function get_vgallery_card_format($text, $type = null) {
  	$res = array();

  	$arrText = explode("---", $text);
  	if(is_array($arrText) && count($arrText)) {
  		foreach($arrText AS $value) {
  			switch(strtoupper($type)) {
  				case "ID":
  				case "ISBN":
  					$res[] = $value;
  					break;
  				default;
  					if(is_numeric(str_replace(array("+", "-", " ", "."), "", $value))) {
  						$res[] = '<a href="tel:' . $value . '" target="_blank">' . cm_getClassByFrameworkCss("phone", "icon-tag") . " ". $value . '</a>';
					} elseif(preg_match('/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $value) > 0) {
	  					$res[] = '<a href="mailto:' . $value . '" target="_blank">' . cm_getClassByFrameworkCss("envelope", "icon-tag") . " ". $value . '</a>';
					} else {
						$res[] = $value;
					}
  			}
		}
	}
  	return implode("<hr />", $res);
  }