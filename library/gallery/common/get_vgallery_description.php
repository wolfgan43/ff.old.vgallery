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
function get_vgallery_description($full_path, $return_string = null, $reference = "grid", $custom_data = array()) {
	static $arrFields = array();
	$res = array();
	
	if(!(is_array($arrFields[$full_path]) && count($arrFields[$full_path]))) {
        $db_additional_info = ffDB_Sql::factory(); 
        $sSQL_field = "";

	    switch($reference) {
	        case "menu":
	            $sSQL_reference = " AND vgallery_fields.enable_in_menu > 0 ";
	            $sSQL_order = " vgallery_fields.`order_backoffice`";
	            break;
	        case "mail":
	            $sSQL_reference = " AND vgallery_fields.enable_in_mail > 0 ";
	            $sSQL_order = " vgallery_fields.enable_in_mail, vgallery_fields.`order_backoffice`";
	            break;
	        case "document":
	            $sSQL_reference = " AND vgallery_fields.enable_in_document > 0 ";
	            $sSQL_order = " vgallery_fields.enable_in_document, vgallery_fields.`order_backoffice`";
	            break;
	        case "grid":
	        default:
	            $sSQL_reference = " AND vgallery_fields.enable_in_grid > 0 ";
	            $sSQL_order = " vgallery_fields.enable_in_grid, vgallery_fields.`order_backoffice`";
	        
	    }

        if($reference != "meta_title") {
		    $sSQL = "SELECT DISTINCT
					    vgallery_fields.ID 
					    , vgallery_fields.name AS name
					    , vgallery_fields_data_type.name AS data_type
					    , vgallery_fields.data_source
					    , vgallery_fields.data_limit
					    , extended_type.name AS extended_type
					    , extended_type.ff_name AS ff_extended_type
				        , vgallery_fields.ID_type AS ID_type
				        , vgallery_fields.disable_multilang AS disable_multilang
				        , vgallery.name AS vgallery_name
				    FROM vgallery_fields
					    INNER JOIN vgallery_nodes ON vgallery_nodes.ID_type = vgallery_fields.ID_type
					    INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
					    INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type
					    INNER JOIN vgallery_fields_data_type ON vgallery_fields_data_type.ID = vgallery_fields.ID_data_type
					    INNER JOIN extended_type on extended_type.ID = vgallery_fields.ID_extended_type
				    WHERE
					    CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) = " . $db_additional_info->toSql($full_path) . "
					    AND vgallery_nodes.name <> ''
					    $sSQL_reference
				    ORDER BY " . $sSQL_order;
		    $db_additional_info->query($sSQL);
		    if($db_additional_info->nextRecord()) {
			    $arrFormField = array();
			    do {
    			    $key_field = md5($db_additional_info->getField("name", "Text")->getValue() 
    							    . $db_additional_info->getField("extended_type", "Text")->getValue()
    							    . $db_additional_info->getField("data_type", "Text")->getValue()
    							    . $db_additional_info->getField("data_source", "Text")->getValue()
    							    . $db_additional_info->getField("data_limit", "Text")->getValue()
    						    );
    			    
				    if(!strlen($sort_node_default))
					    $sort_node_default = $db_additional_info->getField("name", "Text")->getValue();

				    if(strlen($arrFormField[$key_field]["ID"]))
        			    $arrFormField[$key_field]["ID"] .=", ";

				    $arrFormField[$key_field]["ID"] .= $db_additional_info->getField("ID", "Number")->getValue();
				    $arrFormField[$key_field]["name"] = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($db_additional_info->getField("name", "Text")->getValue()));
				    $arrFormField[$key_field]["extended_type"] = $db_additional_info->getField("extended_type", "Text")->getValue();
				    $arrFormField[$key_field]["ff_extended_type"] = $db_additional_info->getField("ff_extended_type", "Text")->getValue();
				    $arrFormField[$key_field]["data_type"] = $db_additional_info->getField("data_type", "Text")->getValue();
				    $arrFormField[$key_field]["data_source"] = $db_additional_info->getField("data_source", "Text")->getValue();
				    $arrFormField[$key_field]["data_limit"] = $db_additional_info->getField("data_limit", "Text")->getValue();
				    $arrFormField[$key_field]["disable_multilang"] = $db_additional_info->getField("disable_multilang", "Text")->getValue();
				    $arrFormField[$key_field]["vgallery_name"] = $db_additional_info->getField("vgallery_name", "Text")->getValue();
			    } while($db_additional_info->nextRecord());

			    if(is_array($arrFormField) && count($arrFormField)) {
    			    foreach($arrFormField AS $$arrFormField_key => $arrFormField_value) {
					    if($arrFormField_value["data_type"] == "data" || $arrFormField_value["data_type"] == "selection") {
						    $sSQL_field .= " 
            				    , (SELECT 
							        GROUP_CONCAT(vgallery_rel_nodes_fields.description SEPARATOR '')
							    FROM
							        vgallery_rel_nodes_fields
							    WHERE
							        vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
							        AND vgallery_rel_nodes_fields.ID_fields IN ( " . $db_additional_info->tosql($arrFormField_value["ID"], "Text", false) . " )
							        AND vgallery_rel_nodes_fields.ID_lang = " . $db_additional_info->tosql(($arrFormField_value["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number") . "
							    ) AS " . $db_additional_info->tosql($arrFormField_value["name"], "Text");
					    } elseif($arrFormField_value["data_type"] == "relationship") {
						    $sSQL_field .= " 
            				    , (SELECT 
							        GROUP_CONCAT(vgallery_rel_nodes_fields.description SEPARATOR '') AS description
							    FROM
							        vgallery_rel_nodes_fields
							        , rel_nodes
							    WHERE 
							        (
							            (
							                rel_nodes.ID_node_src = vgallery_rel_nodes_fields.ID_nodes
							                AND rel_nodes.contest_src = " . $db_additional_info->toSql($arrFormField_value["data_source"], "Text") . "
							                AND rel_nodes.contest_dst = " . $db_additional_info->toSql($arrFormField_value["vgallery_name"], "Text") . " 
							                AND rel_nodes.ID_node_dst = vgallery_nodes.ID
							            ) 
							        OR 
							            (
							                rel_nodes.ID_node_dst = vgallery_rel_nodes_fields.ID_nodes 
							                AND rel_nodes.contest_dst = " . $db_additional_info->toSql($arrFormField_value["data_source"], "Text") . "
							                AND rel_nodes.contest_src = " . $db_additional_info->toSql($arrFormField_value["vgallery_name"], "Text") . " 
							                AND rel_nodes.ID_node_src = vgallery_nodes.ID
							            )
							        )
							        AND 
							            IF (
							                (
							                    SELECT 
							                        GROUP_CONCAT(vgallery_rel_nodes_fields.description SEPARATOR '') 
							                    FROM
							                        vgallery_rel_nodes_fields
							                    WHERE
							                        vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
							                        AND vgallery_rel_nodes_fields.ID_fields IN ( " . $db_additional_info->tosql($arrFormField_value["ID"], "Text", false) . " )
							                        AND vgallery_rel_nodes_fields.ID_lang = " . $db_additional_info->tosql(($arrFormField_value["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number") . "
							                ) REGEXP '^[0-9,]+$'
							                , (
							                    rel_nodes.ID IN 
							                    (
							                        SELECT 
							                            vgallery_rel_nodes_fields.description
							                        FROM
							                            vgallery_rel_nodes_fields
							                        WHERE
							                            vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
							                            AND vgallery_rel_nodes_fields.ID_fields IN ( " . $db_additional_info->tosql($arrFormField_value["ID"], "Text", false) . " )
							                            AND vgallery_rel_nodes_fields.ID_lang = " . $db_additional_info->tosql(($arrFormField_value["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number") . "
							                    )
							                 )
							                , 1
							             )
							        AND vgallery_rel_nodes_fields.ID_fields IN (" . $db_additional_info->tosql($arrFormField_value["data_limit"], "Text", false) . ")
							        AND vgallery_rel_nodes_fields.ID_lang = " . $db_additional_info->tosql(($arrFormField_value["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number") . "
							    ) AS " . $db_additional_info->tosql($arrFormField_value["name"], "Text");
					    }
				    } reset($arrFormField);
			    }
		    }
        }
		$sSQL = "SELECT vgallery_nodes.* 
					, CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
                    , " . (OLD_VGALLERY
                        ? " vgallery_rel_nodes_fields.description"
                        : (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
                            ? " vgallery_nodes.meta_title"
                            : " vgallery_nodes_rel_languages.meta_title"
                        )
                    ) . " AS meta_title
					$sSQL_field
				FROM vgallery_nodes
                    " . (OLD_VGALLERY
                        ? " LEFT JOIN vgallery_rel_nodes_fields
                            ON (
                                vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID
                                AND vgallery_rel_nodes_fields.ID_fields = ( SELECT ID FROM vgallery_fields WHERE vgallery_fields.name = 'meta_title' )
                                AND vgallery_rel_nodes_fields.ID_lang = " . $db_additional_info->tosql(($arrFormField_value["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number") . "
                            )"
                        : (LANGUAGE_INSET_ID == LANGUAGE_DEFAULT_ID
                            ? ""
                            : " INNER JOIN vgallery_nodes_rel_languages ON vgallery_nodes_rel_languages.ID_nodes = vgallery_nodes.ID
                                    AND vgallery_nodes_rel_languages.ID_lang = " . $db_additional_info->tosql(($arrFormField_value["disable_multilang"] ? LANGUAGE_DEFAULT_ID : LANGUAGE_INSET_ID), "Number")
                        )
                    ) . "
				WHERE 
					CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) = " . $db_additional_info->toSql($full_path) . "
					AND vgallery_nodes.name <> ''";
		$db_additional_info->query($sSQL);
		if($db_additional_info->nextRecord()) {
			$arrFields[$full_path] = array();
			do {
				//$full_path = $db_additional_info->toSql("full_path", "Text", true);
				if(is_array($arrFormField) && count($arrFormField)) {
					foreach($arrFormField AS $field_key => $field_value) {
						if($arrFormField_value["data_type"] == "data" 
							|| $arrFormField_value["data_type"] == "selection"
							|| $arrFormField_value["data_type"] == "relationship"
						) {
							$field_name = $field_value["name"];

							if($field_value["extended_type"] == "Image"
	    						|| $field_value["extended_type"] == "Upload"
	    						|| $field_value["extended_type"] == "UploadImage"
							) {
								$image_path = $db_additional_info->getField($field_name, "Text", true);
								if((!(strlen($image_path) && is_file(realpath(DISK_UPDIR . $image_path))))) { 
									$tmp_img = glob(DISK_UPDIR . $db_additional_info->getField("full_path")->getValue()  . "/*");
									if(is_array($tmp_img) && count($tmp_img)) {
										sort($tmp_img);
										foreach($tmp_img AS $tmp_img_key => $tmp_img_value) {
											if(is_file($tmp_img_value)) {
												$mime = ffMimeType(DISK_UPDIR . $db_additional_info->getField("full_path")->getValue()  . "/" . basename($tmp_img_value));
						                        if(strpos($mime, "image") !== false) {
													$image_path = $db_additional_info->getField("full_path")->getValue()  . "/". basename($tmp_img_value);
										            break;
												}
											}
										}
									}
								}

								if($image_path) {
									$arrFields[$full_path][$field_name] = "[CUSTOM_DATA_PRE]" . '<img src="' . str_replace(" ", "%20", cm_showfiles_get_abs_url('/thumb' . $image_path)) . '" />' . "[CUSTOM_DATA_POST]";
								} else {
									$arrFields[$full_path][$field_name] = "[CUSTOM_DATA_PRE]" . "[CUSTOM_DATA_POST]";
								}
							} else {
								$arrFields[$full_path][$field_name] = "[CUSTOM_DATA_PRE]" . $db_additional_info->getField($field_name, "Text", true) . "[CUSTOM_DATA_POST]";
							}
							
							if($return_string == "[TAG]")
								$arrFields[$full_path][$field_name]	= '<span class="' . $field_name . '">' . $arrFields[$full_path][$field_name] . '</span>';
						}
					} reset($arrFormField);
				}
			} while($db_additional_info->nextRecord());
			if(!count($arrFields[$full_path]))
				$arrFields[$full_path]["meta_title"] = "[CUSTOM_DATA_PRE]" . $db_additional_info->getField("meta_title", "Text", true) . "[CUSTOM_DATA_POST]"; 
		}
	}

	if(is_array($arrFields[$full_path]) && count($arrFields[$full_path])) {
		$res = $arrFields[$full_path];
		foreach($res AS $key => $value) {
			$res[$key] = str_replace("[CUSTOM_DATA_PRE]", $custom_data["pre"], $res[$key]);
			$res[$key] = str_replace("[CUSTOM_DATA_POST]", $custom_data["post"], $res[$key]);
		}
	}

	if($return_string === null) {
		return $res;
	} elseif($return_string == "[TAG]") {
		return implode("", $res);
	} else {
		return implode($return_string, $res);
	}
}