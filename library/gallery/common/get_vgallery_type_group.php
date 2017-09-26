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
function get_vgallery_type_group($ID_type, $type = null, $skip_update = false, $group_name = null) 
{
	$db = ffDB_Sql::factory();
    
	$page_group = array();
	$default_group = array();
	$arrGroupOptional = array();
	$res = array();
	$arrAllGroup = array();
        
  	$sSQL = "SELECT vgallery_type.*
				FROM vgallery_type 
				WHERE vgallery_type.ID = " . $db->toSql($ID_type, "Number");
	$db->query($sSQL);
	if($db->nextRecord()) {
		$adv_group = $db->getField("advanced_group", "Number", true);
	}
	
	if($adv_group) 
	{
		$sSQL = "SELECT vgallery_type_group.*
        			FROM vgallery_type_group 
        			WHERE vgallery_type_group.ID_type = " . $db->toSql($ID_type, "Number") . "
        				" . (strlen($type)
        					? " AND vgallery_type_group.`type` = " . $db->toSql($type)
        					: ""
        				) . "
        				" . (strlen($group_name)
        					? (is_numeric($group_name)
        						? " AND vgallery_type_group.ID = " . $db->toSql($group_name, "Number")
        						: " AND vgallery_type_group.name = " . $db->toSql($group_name)
        					)
        					: ""
        				) . "
        				
        			ORDER BY vgallery_type_group.`order`, vgallery_type_group.`ID`"; 
	    $db->query($sSQL);
	    if($db->nextRecord()) {
	    	$count_group_optional = 0;
			do {
				//$is_visible = $db->getField("visible", "Number", true);
				$group_type = $db->getField("type", "Text", true);
				$group_name = $db->getField("name", "Text", true);
				$group_smart_url = ffCommon_url_rewrite($group_name);
				$arrAllGroup[$group_type][$group_name] = 0;
				//if($is_visible)
				//{
					$group_optional = $db->getField("optional", "Number", true);
					if($group_optional) {
						$arrGroupOptional[] = array(new ffData(ffCommon_url_rewrite($group_name)), new ffData($group_name));
					}

					$page_group[$group_type][$group_smart_url] = array(
						"ID" => $db->getField("ID", "Number", true)
						, "name" => $group_name
						, "grid" => array(
							"xs" => $db->getField("grid_xs", "Number", true)
							, "sm" => $db->getField("grid_sm", "Number", true)
							, "md" => $db->getField("grid_md", "Number", true)
							, "default" => $db->getField("default_grid", "Number", true)
						)            	
						, "class" => $db->getField("class", "Text", true)
						, "optional" => $db->getField("optional", "Number", true)
						, "visible" => $db->getField("visible", "Number", true)
						, "tab" => null
					);
					$page_group[$group_type][$group_smart_url]["column"] = cm_getClassByFrameworkCss(array($page_group[$group_type][$group_smart_url]["grid"]["xs"],$page_group[$group_type][$group_smart_url]["grid"]["sm"],$page_group[$group_type][$group_smart_url]["grid"]["md"],$page_group[$group_type][$group_smart_url]["grid"]["default"]), "col");
				//}
			} while($db->nextRecord());
		}  
		
	    if($type) 
		{
			if(array_key_exists($type, $page_group))
				$res = $page_group[$type];

			switch($type) {
				case "thumb":
					break;
				case "detail":
					break;
  				case "backoffice":
  				default:
  					$default_group[LANGUAGE_DEFAULT] = array(
						"ID" => ""
				        , "name" => "general"
				        , "column" => cm_getClassByFrameworkCss(array(12,12,7), "col")
				        , "grid" => array(
		        			"default" => 7
		        			, "md" => 7 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
				        , "class" => ""
				        , "optional" => false
				        , "order" => -6
				        , "visible" => true
				        , "is_system" => false
					);
					$default_group["lang"] = array(
						"ID" => ""
				        , "name" => "lang"
				        , "column" => cm_getClassByFrameworkCss(array(12,12,5), "col")
				        , "grid" => array(
		        			"default" => 5
		        			, "md" => 5 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
				        , "class" => ""
				        , "optional" => false
				        , "order" => -5
				        , "visible" => true
				        , "is_system" => true
					);
					$default_group["publishing"] = array(
						"ID" => ""
					    , "name" => "publishing"
					    , "column" => cm_getClassByFrameworkCss(array(12,12,5), "col")
				        , "grid" => array(
		        			"default" => 5
		        			, "md" => 5 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
					    , "class" => ""
				        , "optional" => false
				        , "order" => -4
				        , "visible" => true
				        , "is_system" => true
					);
					$default_group["optional"] = array(
						"ID" => ""
					    , "name" => "optional"
					    , "column" => cm_getClassByFrameworkCss(array(12,12,5), "col")
				        , "grid" => array(
		        			"default" => 5
		        			, "md" => 5 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
					    , "class" => ""
				        , "optional" => false
				        , "order" => -3
				        , "visible" => true
				        , "is_system" => true
					);				
					$default_group["setting"] = array(
						"ID" => ""
					    , "name" => "setting"
					    , "column" => cm_getClassByFrameworkCss(array(12,12,5), "col")
				        , "grid" => array(
		        			"default" => 5
		        			, "md" => 5 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
					    , "class" => ""
				        , "optional" => false
				        , "order" => -2
				        , "visible" => true
				        , "is_system" => true
					);
                    $default_group["highlight"] = array(
                        "ID" => ""
                        , "name" => "highlight"
                        , "column" => cm_getClassByFrameworkCss(array(12,12,5), "col")
                        , "grid" => array(
                            "default" => 5
                            , "md" => 5 
                            , "sm" => 12
                            , "xs" => 12
                        )
                        , "class" => ""
                        , "optional" => false
                        , "order" => -1
                        , "visible" => true
                        , "is_system" => true
                    );
					$default_group["tags"] = array(
						"ID" => ""
					    , "name" => "tags"
					    , "column" => cm_getClassByFrameworkCss(array(12,12,5), "col")
				        , "grid" => array(
		        			"default" => 5
		        			, "md" => 5 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
					    , "class" => ""
				        , "optional" => false
				        , "order" => 0
				        , "visible" => true
				        , "is_system" => true
					);				
					$default_group["seo"] = array(
						"ID" => ""
					    , "name" => "seo"
					    , "column" => cm_getClassByFrameworkCss(array(12), "col")
				        , "grid" => array(
		        			"default" => 12
		        			, "md" => 12 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
					    , "class" => ""
				        , "optional" => false
				        , "order" => 98
				        , "visible" => false
				        , "is_system" => true
					);
					$default_group["permission"] = array(
						"ID" => ""
					    , "name" => "permission"
					    , "column" => cm_getClassByFrameworkCss(array(12), "col")
				        , "grid" => array(
		        			"default" => 12
		        			, "md" => 12 
		        			, "sm" => 12
		        			, "xs" => 12
				        )
					    , "class" => ""
				        , "optional" => false
				        , "order" => 99
				        , "visible" => false
				        , "is_system" => true
					);  
  			}  
			
			if(is_array($default_group) && count($default_group))
			{
				foreach($default_group AS $group_key => $group_value) 
				{
					if(!array_key_exists($group_key, $arrAllGroup[$type])) 
					{
						$res[$group_key] = $group_value;
						if(!$skip_update && !(is_array($page_group[$type]) && array_key_exists($group_key, $page_group[$type]))) 
						{
							$sSQL = "INSERT INTO vgallery_type_group
									(
										`ID`
										, `name`
										, `optional`
										, `order`
										, `default_grid`
										, `class`
										, `grid_md`
										, `grid_sm`
										, `grid_xs`
										, `type`
										, `ID_type`
										, `visible`
									)
									VALUES
									(
										null
										, " . $db->toSql($group_value["name"]) . "
										, " . $db->toSql($group_value["optional"], "Number") . "
										, " . $db->toSql($group_value["order"], "Number") . "
										, " . $db->toSql($group_value["grid"]["default"], "Number") . "
										, " . $db->toSql($group_value["class"]) . "
										, " . $db->toSql($group_value["grid"]["md"], "Number") . "
										, " . $db->toSql($group_value["grid"]["sm"], "Number") . "
										, " . $db->toSql($group_value["grid"]["xs"], "Number") . "
										, " . $db->toSql($type) . "
										, " . $db->toSql($ID_type, "Number") . "
										, " . $db->toSql($group_value["visible"], "Number") . "
									)";
							$db->execute($sSQL);    				
						}
					}
					$res[$group_key]["is_system"] = $default_group[$group_key]["is_system"];
				}
			}
		} else {
    		$res = $page_group;
	    }

	    if(array_key_exists("optional", $res) && is_array($arrGroupOptional) && count($arrGroupOptional)) {
			$res["optional"]["multi_pairs"] = array_merge(array(array(new ffData("none"), new ffData(ffTemplate::_get_word_by_code("vgallery_group_optional_none")))), $arrGroupOptional);
	    } else {
	    	$res["optional"]["visible"] = false;
		}
	}
	return $res;
}
