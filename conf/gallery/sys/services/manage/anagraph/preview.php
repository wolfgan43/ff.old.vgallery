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
$key = $_REQUEST["key"];
$show_bill_reference = $_REQUEST["reference"];

$db = ffDB_Sql::factory();

$sSQL = "SELECT DISTINCT
            anagraph_fields.ID 
            , anagraph_fields.name AS name
            , extended_type.name AS extended_type
            , extended_type.ff_name AS ff_extended_type 
        FROM anagraph_fields
            INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
            INNER JOIN anagraph ON anagraph.ID_type = anagraph_fields.ID_type
            INNER JOIN extended_type on extended_type.ID = anagraph_fields.ID_extended_type
        WHERE NOT(anagraph_fields.hide > 0)
        	AND anagraph.ID = " . $db->tosql($key, "Number")  . "
        ORDER BY anagraph_fields.`order_detail`";
$db->query($sSQL);
if($db->nextRecord()) {
    $arrFormField = array();
    $sSQL_field = "";
    do {
        $key_field = md5($db->getField("name", "Text")->getValue());
        
        if(strlen($arrFormField[$key_field]["ID"]))
            $arrFormField[$key_field]["ID"] .=", ";

        $arrFormField[$key_field]["ID"] .= $db->getField("ID", "Number")->getValue();
        $arrFormField[$key_field]["name"] =  preg_replace('/[^a-zA-Z0-9]/', '', strtolower($db->getField("name", "Text")->getValue()));
        $arrFormField[$key_field]["extended_type"] = $db->getField("extended_type", "Text")->getValue();
        $arrFormField[$key_field]["ff_extended_type"] = $db->getField("ff_extended_type", "Text")->getValue();
    } while($db->nextRecord());

    
    $sSQL_field = "";
    if(is_array($arrFormField) && count($arrFormField)) {
        foreach($arrFormField AS $arrFormField_key => $arrFormField_value) {
            $sSQL_field .= ", (SELECT 
                                GROUP_CONCAT(IF(anagraph_rel_nodes_fields.description_text = ''
                                                , anagraph_rel_nodes_fields.description
                                                , anagraph_rel_nodes_fields.description_text
                                            )
                                            SEPARATOR ''
                                        )
                            FROM
                                anagraph_rel_nodes_fields
                            WHERE
                                anagraph_rel_nodes_fields.ID_nodes = anagraph.ID
                                AND anagraph_rel_nodes_fields.ID_fields IN ( " . $db->tosql($arrFormField_value["ID"], "Text", false) . " )
                            ) AS " . $db->tosql($arrFormField_value["name"]);
        }
    }
}

$user = Auth::get("user");
$sSQL = "SELECT anagraph_categories.ID
                , anagraph_categories.name
                , anagraph_categories.limit_by_groups 
        FROM anagraph_categories
        ORDER BY anagraph_categories.name";
$db->query($sSQL);
if($db->nextRecord()) {
    do {
        $limit_by_groups = $db->getField("limit_by_groups")->getValue();
        if(strlen($limit_by_groups)) {
            $limit_by_groups = explode(",", $limit_by_groups);
            
            if(array_search($user->acl, $limit_by_groups) !== false) {
                if(strlen($allowed_ana_cat)) {
                    $allowed_ana_cat .= ",";
                }
                $allowed_ana_cat .= $db->getField("ID", "Number", true);
            }
        } else {
            if(strlen($allowed_ana_cat)) {
                $allowed_ana_cat .= ",";
            }
            $allowed_ana_cat .= $db->getField("ID", "Number", true);
        }
    
    } while($db->nextRecord());
}

$sSQL = "SELECT anagraph.*
            $sSQL_field
            , IF(anagraph.uid > 0 AND " . CM_TABLE_PREFIX . "mod_security_users.avatar <> ''
                , " . CM_TABLE_PREFIX . "mod_security_users.avatar
                , anagraph.avatar
            ) AS anagraph_avatar
            " . (Cms::env("AREA_ECOMMERCE_SHIPPING_LIMIT_STATE") > 0
                ? ""
                : " , (
                        SELECT
							IFNULL(
								(SELECT " . FF_PREFIX . "international.description
									FROM " . FF_PREFIX . "international
									WHERE " . FF_PREFIX . "international.word_code = " . FF_SUPPORT_PREFIX . "state.name
										AND " . FF_PREFIX . "international.ID_lang = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
										AND " . FF_PREFIX . "international.is_new = 0
                                    ORDER BY " . FF_PREFIX . "international.description
                                    LIMIT 1
								)
								, " . FF_SUPPORT_PREFIX . "state.name
							) AS description
                        FROM
                            " . FF_SUPPORT_PREFIX . "state
                        WHERE " . FF_SUPPORT_PREFIX . "state.ID = anagraph.billstate                                
                        ORDER BY description
                    ) AS billstate_label
                    "
            ) . "
            , IF(anagraph.uid > 0 AND " . CM_TABLE_PREFIX . "mod_security_users.email <> ''
                , " . CM_TABLE_PREFIX . "mod_security_users.email
                , anagraph.email
            ) AS anagraph_email
            , (IF(anagraph.uid > 0
                , IF(anagraph.billreference = ''
                    , IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                    , IF(anagraph.billreference = IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username)
                        , CONCAT(anagraph.name, ' ', anagraph.surname)
                        , CONCAT(anagraph.billreference, ' (', IF(" . CM_TABLE_PREFIX . "mod_security_users.username = '', " . CM_TABLE_PREFIX . "mod_security_users.email, " . CM_TABLE_PREFIX . "mod_security_users.username), ')')
                    )
                )
                , IF(anagraph.billreference = ''
                    , CONCAT(anagraph.name, ' ', anagraph.surname)
                    , anagraph.billreference
                )
            )) AS billreference
            , GROUP_CONCAT(anagraph_categories.name ORDER BY anagraph_categories.name SEPARATOR ' ') AS categories_name
        FROM anagraph 
            LEFT JOIN anagraph_categories ON FIND_IN_SET(anagraph_categories.ID, anagraph.categories)
            LEFT JOIN " . CM_TABLE_PREFIX . "mod_security_users ON " . CM_TABLE_PREFIX . "mod_security_users.ID = anagraph.uid
        WHERE anagraph.ID = " . $db->toSql($key, "Number") . "
            " . (strlen($allowed_ana_cat)
                ? " AND anagraph_categories.ID IN (" . $db->toSql($allowed_ana_cat, "Text", false) . ")"
                : ""
            ) . "
        GROUP BY anagraph.ID
        ORDER BY anagraph.last_update DESC, billreference ";

$db->query($sSQL);
if ($db->nextRecord())
{
    $tpl = ffTemplate::factory(__DIR__);
    $tpl->load_file("preview.html", "main");

    $tpl->set_var("site_path", FF_SITE_PATH);
    $tpl->set_var("ret_url", urlencode($_SERVER["REQUEST_URI"]));

	
    //$tpl->set_var("anagraph_id", $db->getField("ID", "Text", true));
    if($show_bill_reference && strlen($db->getField("billreference", "Text", true))) {
        $tpl->set_var("anagraph_billreference", $db->getField("billreference", "Text", true)); 
        $tpl->parse("SezAnagraphBillReference", false);
    } else {
        $tpl->set_var("SezAnagraphBillReference", "");
    }
    if(strlen($db->getField("billcf", "Text", true))) {
        $tpl->set_var("anagraph_billcf", $db->getField("billcf", "Text", true));
        $tpl->parse("SezAnagraphBillCF", false);
    } else {
        $tpl->set_var("SezAnagraphBillCF", "");
    }
    if(strlen($db->getField("billpiva", "Text", true))) {
        $tpl->set_var("anagraph_billpiva", $db->getField("billpiva", "Text", true));
        $tpl->parse("SezAnagraphBillPiva", false);
    } else {
        $tpl->set_var("SezAnagraphBillPiva", "");
    }
    if(strlen($db->getField("billaddress", "Text", true))) {
        $tpl->set_var("anagraph_billaddress", $db->getField("billaddress", "Text", true));
        $tpl->parse("SezAnagraphBillAddress", false);
    } else {
        $tpl->set_var("SezAnagraphBillAddress", "");
    }
    if(strlen($db->getField("billcap", "Text", true))) {
        $tpl->set_var("anagraph_billcap", $db->getField("billcap", "Text", true));
        $tpl->parse("SezAnagraphBillCap", false);
    } else {
        $tpl->set_var("SezAnagraphBillCap", "");
    }
    if(strlen($db->getField("billtown", "Text", true))) {
        $tpl->set_var("anagraph_billtown", $db->getField("billtown", "Text", true));
        $tpl->parse("SezAnagraphBillTown", false);
    } else {
        $tpl->set_var("SezAnagraphBillTown", "");
    }
    if(strlen($db->getField("billprovince", "Text", true))) {
        $tpl->set_var("anagraph_billprovince", $db->getField("billprovince", "Text", true));
        $tpl->parse("SezAnagraphBillProvince", false);
    } else {
        $tpl->set_var("SezAnagraphBillProvince", "");
    }

    if(!(Cms::env("AREA_ECOMMERCE_SHIPPING_LIMIT_STATE") > 0) && strlen($db->getField("billstate_label", "Text", true))) {
        $tpl->set_var("anagraph_billstate", $db->getField("billstate_label", "Text", true));
        $tpl->parse("SezAnagraphBillState", false);
	} else {
        $tpl->set_var("SezAnagraphBillState", "");
    }

    $tpl->set_var("anagraph_mail", $db->getField("anagraph_email", "Text", true));
    $tpl->set_var("anagraph_avatar", Auth::getUserAvatar(null, $db->getField("anagraph_avatar", "Text", true)));
    $tpl->parse("SezAnagraphAvatar", false);


    if(strlen($db->getField("categories", "Text", true))) {
        $arrCategory = explode(",", $db->getField("categories", "Text", true));
	}
        

    if(is_array($arrFormField) && count($arrFormField)) {
        foreach($arrFormField AS $field_key => $field_value) {
            $field_name = $field_value["name"];
            if(strlen($db->getField($field_name, "Text", true))) {
                $tpl->set_var("anagraph_custom_label", ffTemplate::_get_word_by_code(preg_replace('/[^a-zA-Z0-9]/', '', $field_name)));
                $tpl->set_var("anagraph_custom", $db->getField($field_name, "Text", true));
                $tpl->parse("SezAnagraphCustom", true);
			}
        } reset($arrFormField);
    }
	
	//$anagraph_last_update = $db->getField("last_update", "Timestamp");
    //$tpl->set_var("anagraph_last_update", $anagraph_last_update->getValue("Date", FF_LOCALE));
    
    die($tpl->rpparse("main", false));
}
