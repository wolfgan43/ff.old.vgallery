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
function get_file_permission($user_path, $type = "files", $range = null, $is_dir = null, &$arrCheckReturn = null) {
	$db = ffDB_Sql::factory();
	$file_permission = array();
	$res = null;
	//static $cache = array(); 	
	
 	$cache = get_session("cache");
 	$cache["auth"] = array(); 

 	
	if($user_path !== null 
		&& isset($cache["auth"][$type][$user_path]) 
		&& is_array($cache["auth"][$type][$user_path]) 
		&& count($cache["auth"][$type][$user_path])
	) {
		return $cache["auth"][$type][$user_path];
	} 

	$cached_path = check_file_permission($cache, $user_path, $type);
	if($cached_path === null) {
		$user_permission = get_session("user_permission");
        if(is_array($user_permission) && count($user_permission) && is_array($user_permission["groups"]) && count($user_permission["groups"])) {
        	if(strlen($user_permission["primary_gid_default_name"]) && !array_key_exists($user_permission["primary_gid_default_name"], $user_permission["groups"])) {
				$user_permission["groups"][$user_permission["primary_gid_default_name"]] = $user_permission["primary_gid_default"];
        	}
        	if(strlen($user_permission["primary_gid_name"]) && !array_key_exists($user_permission["primary_gid_name"], $user_permission["groups"])) {
				$user_permission["groups"][$user_permission["primary_gid_name"]] = $user_permission["primary_gid"];
        	}        	
		    $user_groups = implode(", ", $user_permission["groups"]);
			if(array_search(MOD_SEC_GUEST_GROUP_ID, $user_permission["groups"]) === false) {
		    	$user_groups .= ", " . MOD_SEC_GUEST_GROUP_ID;
		    	$is_guest = false;
		    } else {
		    	$is_guest = true;
		    }
		    
		    switch($type) {
		    	case "vgallery_nodes":
                    if(OLD_VGALLERY) {
		    		    $sSQL_visible = "SELECT 
									        GROUP_CONCAT(DISTINCT CONCAT(IF(ISNULL(vgallery_rel_nodes_fields.description), 1, vgallery_rel_nodes_fields.description), '-', " . FF_PREFIX . "languages.code)) AS vlang
								        FROM
									        " . FF_PREFIX . "languages
									        INNER JOIN vgallery_rel_nodes_fields ON vgallery_rel_nodes_fields.ID_lang = " . FF_PREFIX . "languages.ID
                    					        AND vgallery_rel_nodes_fields.ID_fields = (
                    						        SELECT vgallery_fields.ID 
                    						        FROM vgallery_fields 
                    							        INNER JOIN	vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                    						        WHERE vgallery_fields.name = 'visible'
                    							        AND vgallery_type.name = 'System'
                    					        )
								         WHERE 
						                    " . FF_PREFIX . "languages.status > 0
						                    AND vgallery_rel_nodes_fields.ID_nodes = ID_item";
                    } else {
                        $sSQL_visible = "SELECT 
                                        GROUP_CONCAT(DISTINCT CONCAT(IF(ISNULL(`" . $type . "_rel_languages`.visible), 1, `" . $type . "_rel_languages`.visible), '-', " . FF_PREFIX . "languages.code)) AS vlang
                                    FROM
                                        " . FF_PREFIX . "languages
                                        INNER JOIN `" . $type . "_rel_languages` ON `" . $type . "_rel_languages`.ID_lang = " . FF_PREFIX . "languages.ID
                                     WHERE 
                                        " . FF_PREFIX . "languages.status > 0
                                        AND `" . $type . "_rel_languages`.ID_nodes = ID_item";
                    }
		    		break;
		    	case "files":
		    		$sSQL_visible = "SELECT 
								    GROUP_CONCAT(DISTINCT CONCAT(IF(ISNULL(`" . $type . "_rel_languages`.visible), 1, `" . $type . "_rel_languages`.visible), '-', " . FF_PREFIX . "languages.code)) AS vlang
							    FROM
								    " . FF_PREFIX . "languages
								    INNER JOIN `" . $type . "_rel_languages` ON `" . $type . "_rel_languages`.ID_languages = " . FF_PREFIX . "languages.ID
							     WHERE 
					                " . FF_PREFIX . "languages.status > 0
					                AND `" . $type . "_rel_languages`.ID_" . $type . " = ID_item";
		    		break;
		    	default:
		    		$sSQL_visible = "''";
		    }
		    
		    
		    $sSQL = "SELECT 
					    `" . $type . "_rel_groups`.*
					    , " . CM_TABLE_PREFIX . "mod_security_groups.name AS group_name
					    , CONCAT(IF(`" . $type . "`.parent = '/', '', `" . $type . "`.parent), '/', `" . $type . "`.name) AS full_path
					    , `" . $type . "`.ID AS ID_item
					    , `" . $type . "`.owner AS item_owner
					    , IFNULL((" . $sSQL_visible . "), 1) AS visible_by_lang
				    FROM
					    `" . $type . "` 
					    INNER JOIN `" . $type . "_rel_groups` ON `" . $type . "`.ID = `" . $type . "_rel_groups`.`ID_" . $type . "`
					    INNER JOIN " . CM_TABLE_PREFIX . "mod_security_groups ON " . CM_TABLE_PREFIX . "mod_security_groups.gid = `" . $type . "_rel_groups`.gid
				    WHERE 
					    `" . $type . "_rel_groups`.gid IN (" . $db->toSql($user_groups, "Text", false) . ")
				    ORDER  BY 
					    LENGTH(full_path) ASC
					    , " . CM_TABLE_PREFIX . "mod_security_groups.level DESC
					    , `" . $type . "_rel_groups`.mod DESC";
		    $db->query($sSQL);

		    $file_permission = array();
		    $file_permission["groups"] = array();
		    
		    if ($db->nextRecord()) {
			    do {
				    $full_path = $db->getField("full_path", "Text", true);
				    if(!strlen($full_path))
					    continue;

			    	$ID_item = $db->getField("ID_item", "Number", true);
					if($db->getField("gid", "Number", true) == MOD_SEC_GUEST_GROUP_ID && !$is_guest) {
						if(!isset($cache["auth"][$type][$full_path]["groups"][$user_permission["primary_gid_default_name"]]))
							$cache["auth"][$type][$full_path]["groups"][$user_permission["primary_gid_default_name"]] = $db->getField("mod")->getValue();
					} else {
						$cache["auth"][$type][$full_path]["groups"][$db->getField("group_name")->getValue()] = $db->getField("mod")->getValue();
					}

				    $cache["auth"][$type][$full_path]["owner"] = $db->getField("item_owner", "Number", true);

				    $visible_by_lang = $db->getField("visible_by_lang", "Text", true);
				    if(strlen($visible_by_lang)) {
					    $arrVisible_by_lang = explode(",", $visible_by_lang);
					    if(is_array($arrVisible_by_lang) && count($arrVisible_by_lang)) {
						    foreach($arrVisible_by_lang AS $vlang_key => $vlang_value) {
							    if(strlen($vlang_value)) {
								    $code_visible = explode("-", $vlang_value); 
								    if(is_array($code_visible) && count($code_visible) == 2) {
									    if($code_visible[0]) 
										    $cache["auth"][$type][$full_path]["visible"][$code_visible[1]] = TRUE;
									    else
										    $cache["auth"][$type][$full_path]["visible"][$code_visible[1]] = FALSE;
								    } else {
									    $cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET] = TRUE;		
								    }
							    }
						    } 
					    }
				    }
				    if(!isset($cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET]))
					    $cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET] = TRUE;

					if(is_array($arrCheckReturn) && array_key_exists($ID_item, $arrCheckReturn)) {
						if(!check_mod($cache["auth"][$type][$full_path], 1, ($arrCheckReturn[$ID_item]["enable_multilang_visible"] ? true : LANGUAGE_DEFAULT), AREA_VGALLERY_SHOW_MODIFY))
							unset($arrCheckReturn[$ID_item]);
						elseif(check_mod($cache["auth"][$type][$full_path], 2, ($arrCheckReturn[$ID_item]["enable_multilang_visible"] ? true : LANGUAGE_DEFAULT), AREA_VGALLERY_SHOW_MODIFY))
							$arrCheckReturn[$ID_item]["is_admin"] = true;
					}
			    } while($db->nextRecord());	
		    }
        }
	}

	$cached_path = check_file_permission($cache, $user_path, $type);
	if($user_path !== null && $cached_path == $user_path) {
		$res = $cache["auth"][$type][$user_path];
	} else {
		//$cache["auth"][$type][$user_path] = $cache["auth"][$type][$cached_path];
        if($is_dir === true) {
            $sSQL_is_dir = " AND `" . $type . "`.is_dir > 0 ";
        } elseif($is_dir === false) {
            $sSQL_is_dir = " AND NOT(`" . $type . "`.is_dir > 0) ";
        } else {
            $sSQL_is_dir = "";
        }
        
	    if($type == "vgallery_nodes") { 
			$sSQL = "
						SELECT `" . $type . "`.owner AS item_owner 
							, CONCAT(IF(`" . $type . "`.parent = '/', '', `" . $type . "`.parent), '/', `" . $type . "`.name) AS full_path
							, `" . $type . "`.ID AS ID_item
							, (" . (OLD_VGALLERY
                                ? "SELECT 
									    GROUP_CONCAT(DISTINCT CONCAT(IF(ISNULL(vgallery_rel_nodes_fields.description), 1, vgallery_rel_nodes_fields.description), '-', " . FF_PREFIX . "languages.code)) AS vlang
								    FROM
									    " . FF_PREFIX . "languages
									    INNER JOIN vgallery_rel_nodes_fields ON vgallery_rel_nodes_fields.ID_lang = " . FF_PREFIX . "languages.ID
                    					    AND vgallery_rel_nodes_fields.ID_fields = (
                    						    SELECT vgallery_fields.ID 
                    						    FROM vgallery_fields 
                    							    INNER JOIN	vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
                    						    WHERE vgallery_fields.name = 'visible'
                    							    AND vgallery_type.name = 'System'
                    					    )
								     WHERE 
					                    " . FF_PREFIX . "languages.status = '1'
					                    AND vgallery_rel_nodes_fields.ID_nodes = ID_item"
                                : "SELECT 
                                        GROUP_CONCAT(DISTINCT CONCAT(IF(ISNULL(`" . $type . "_rel_languages`.visible), 1, `" . $type . "_rel_languages`.visible), '-', " . FF_PREFIX . "languages.code)) AS vlang
                                    FROM
                                        " . FF_PREFIX . "languages
                                        INNER JOIN `" . $type . "_rel_languages` ON `" . $type . "_rel_languages`.ID_lang = " . FF_PREFIX . "languages.ID
                                     WHERE 
                                        " . FF_PREFIX . "languages.status > 0
                                        AND `" . $type . "_rel_languages`.ID_nodes = ID_item"
                            ) . ") AS visible_by_lang
						FROM `" . $type . "` 
						WHERE " . ($user_path === null
							? " 0 "
							: "(`" . $type . "`.parent = " . $db->toSql(ffCommon_dirname($user_path)) . "
							AND `" . $type . "`.name = " . $db->toSql(basename($user_path)) . ") "
						)
						. ($range === null
							? ""
							: (is_array($range)
								? " OR (`" . $type . "`.ID IN (" . $db->toSql(implode(",", $range), "Text", false) . "))"
								: " OR (`" . $type . "`.parent LIKE '" . $db->toSql($user_path, "Text", false) . "%')"
							)
						) . $sSQL_is_dir;
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$ID_item = $db->getField("ID_item", "Number", true);
					$full_path = $db->getField("full_path", "Text", true);
					if(!isset($cache["auth"][$type][$full_path])) {
						$cached_path = check_file_permission($cache, $full_path, $type);
						
						$cache["auth"][$type][$full_path] = $cache["auth"][$type][$cached_path];	
						$cache["auth"][$type][$full_path]["owner"] = $db->getField("item_owner", "Number", true);
						
						$visible_by_lang = $db->getField("visible_by_lang", "Text", true);
						if(strlen($visible_by_lang)) {
							$arrVisible_by_lang = explode(",", $visible_by_lang);
							if(is_array($arrVisible_by_lang) && count($arrVisible_by_lang)) {
								foreach($arrVisible_by_lang AS $vlang_key => $vlang_value) {
									if(strlen($vlang_value)) {
										$code_visible = explode("-", $vlang_value);
										if(is_array($code_visible) && count($code_visible) == 2) {
											if($code_visible[0]) 
												$cache["auth"][$type][$full_path]["visible"][$code_visible[1]] = true;
											else
												$cache["auth"][$type][$full_path]["visible"][$code_visible[1]] = false;
										} else {
											$cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET] = true; 
										}
									}
								} 
							}
						}
						if(!isset($cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET]))
							$cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET] = true;
					}
					
					if(is_array($arrCheckReturn) && array_key_exists($ID_item, $arrCheckReturn)) {
						if(!check_mod($cache["auth"][$type][$full_path], 1, ($arrCheckReturn[$ID_item]["enable_multilang_visible"] ? true : LANGUAGE_DEFAULT), AREA_VGALLERY_SHOW_MODIFY))
							unset($arrCheckReturn[$ID_item]);
						elseif(check_mod($cache["auth"][$type][$full_path], 2, ($arrCheckReturn[$ID_item]["enable_multilang_visible"] ? true : LANGUAGE_DEFAULT), AREA_VGALLERY_SHOW_MODIFY))
							$arrCheckReturn[$ID_item]["is_admin"] = true;
					}							
				} while($db->nextRecord());
			}
	    } else {
			$sSQL = "
						SELECT `" . $type . "`.owner AS item_owner 
							, CONCAT(IF(`" . $type . "`.parent = '/', '', `" . $type . "`.parent), '/', `" . $type . "`.name) AS full_path
							, `" . $type . "`.ID AS ID_item
							, (
								SELECT 
									GROUP_CONCAT(DISTINCT CONCAT(IF(ISNULL(`" . $type . "_rel_languages`.visible), 1, `" . $type . "_rel_languages`.visible), '-', " . FF_PREFIX . "languages.code)) AS vlang
								FROM
									" . FF_PREFIX . "languages
									INNER JOIN `" . $type . "_rel_languages` ON `" . $type . "_rel_languages`.ID_languages = " . FF_PREFIX . "languages.ID
								 WHERE 
					                " . FF_PREFIX . "languages.status = '1'
					                AND `" . $type . "_rel_languages`.ID_" . $type . " = ID_item
							) AS visible_by_lang
						FROM `" . $type . "` 
						WHERE " . ($user_path === null
							? " 0 "
							: "(`" . $type . "`.parent = " . $db->toSql(ffCommon_dirname($user_path)) . "
							AND `" . $type . "`.name = " . $db->toSql(basename($user_path)) . ") "
						)
						. ($range === null
							? ""
							: (is_array($range)
								? " OR (`" . $type . "`.ID IN (" . $db->toSql(implode(",", $range), "Text", false) . "))"
								: " OR (`" . $type . "`.parent LIKE '" . $db->toSql($user_path, "Text", false) . "%')"
							)
						) . $sSQL_is_dir;
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$ID_item = $db->getField("ID_item", "Number", true);
					$full_path = $db->getField("full_path", "Text", true);
					if(!isset($cache["auth"][$type][$full_path])) {
						$cached_path = check_file_permission($cache, $full_path, $type);
						
						$cache["auth"][$type][$full_path] = $cache["auth"][$type][$cached_path];	
						$cache["auth"][$type][$full_path]["owner"] = $db->getField("item_owner", "Number", true);

						$visible_by_lang = $db->getField("visible_by_lang", "Text", true);
						if(strlen($visible_by_lang)) {
							$arrVisible_by_lang = explode(",", $visible_by_lang);
							if(is_array($arrVisible_by_lang) && count($arrVisible_by_lang)) {
								foreach($arrVisible_by_lang AS $vlang_key => $vlang_value) {
									if(strlen($vlang_value)) {
										$code_visible = explode("-", $vlang_value);
										if(is_array($code_visible) && count($code_visible) == 2) {
											if($code_visible[0]) 
												$cache["auth"][$type][$full_path]["visible"][$code_visible[1]] = true;
											else
												$cache["auth"][$type][$full_path]["visible"][$code_visible[1]] = false;
										} else {
											$cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET] = true;
										}
									}
								} 
							}
						}
						if(!isset($cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET]))
							$cache["auth"][$type][$full_path]["visible"][LANGUAGE_INSET] = true;
					}

					if(is_array($arrCheckReturn) && array_key_exists($ID_item, $arrCheckReturn)) {
						if(!check_mod($cache["auth"][$type][$full_path], 1, ($arrCheckReturn[$ID_item]["enable_multilang_visible"] ? true : LANGUAGE_DEFAULT), AREA_VGALLERY_SHOW_MODIFY))
							unset($arrCheckReturn[$ID_item]);
						elseif(check_mod($cache["auth"][$type][$full_path], 2, ($arrCheckReturn[$ID_item]["enable_multilang_visible"] ? true : LANGUAGE_DEFAULT), AREA_VGALLERY_SHOW_MODIFY))
							$arrCheckReturn[$ID_item]["is_admin"] = true;
					}							
				} while($db->nextRecord());
			}
		}		
	}

	if($res === null) {
		if($user_path !== null) {
			if(!isset($cache["auth"][$type][$user_path]) && $type == "vgallery_nodes") {
				$cache["auth"][$type][$user_path] = $cache["auth"][$type][$cached_path];
				$cache["auth"][$type][$user_path][LANGUAGE_INSET] = false;
			} elseif(!isset($cache["auth"][$type][$user_path]) && $type == "files") {
				$cache["auth"][$type][$user_path] = $cache["auth"][$type][$cached_path];
				$cache["auth"][$type][$user_path][LANGUAGE_INSET] = true;
			} elseif(!isset($cache["auth"][$type][$user_path]) && $type == "static_pages") {
				$cache["auth"][$type][$user_path] = null;
				$cache["auth"][$type][$user_path] = $cache["auth"][$type][$cached_path];
				$cache["auth"][$type][$user_path][LANGUAGE_INSET] = true;
			}
			
			$res = $cache["auth"][$type][$user_path];
		}
	}
    set_session("cache", $cache);

	return $res;
}

function check_file_permission(&$cache, $user_path, $type) {
	$cached_path = null;

	if($user_path === null)
		$user_path = "/";

 	do {
	    if(isset($cache["auth"][$type][$user_path])) {
	    	$cached_path = $user_path;
	    	break;
		}
	} while($user_path != "/" && $user_path = ffCommon_dirname($user_path));
	
	return $cached_path;
}
