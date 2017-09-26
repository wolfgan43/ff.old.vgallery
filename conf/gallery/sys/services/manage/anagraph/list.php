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
$php_array = array();

$page = (isset($_REQUEST["page"])
            ? $_REQUEST["page"]
            : 0);

$rows = (isset($_REQUEST["rows"])
            ? $_REQUEST["rows"]
            : 10);

$category = (isset($_REQUEST["cat"])
            ? $_REQUEST["cat"]
            : null);

$real_page = $page * $rows;
$real_next_page = ($page + 1)  * $rows;
            
$db = ffDb_Sql::factory();
$db_operation = ffDB_Sql::factory();

if(strlen($category)) {
    $sSQL = "SELECT * FROM anagraph_categories";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            if(ffCommon_url_rewrite($db->getField("name")->getValue()) == $category) {
                $ID_category = $db->getField("ID")->getValue();
                break;
            }
        } while($db->nextRecord());
    }
}

$sSQL = "SELECT DISTINCT
            anagraph_fields.ID 
            , anagraph_fields.name AS name
            , extended_type.name AS extended_type
            , extended_type.ff_name AS ff_extended_type 
        FROM anagraph_fields
            INNER JOIN anagraph_type ON anagraph_type.ID = anagraph_fields.ID_type
            INNER JOIN anagraph ON anagraph.ID_type = anagraph_fields.ID_type
            INNER JOIN extended_type on extended_type.ID = anagraph_fields.ID_extended_type
        WHERE anagraph_fields.enable_in_grid = '1' 
            " . (strlen($category)
                ? ($category == "nocategory"
                    ? " AND anagraph.categories = '' "
                    : " AND FIND_IN_SET(" . $db->tosql($ID_category, "Number") . ", anagraph.categories) "
                )
                : ""
            ) . "
        ORDER BY anagraph_fields.`order_thumb`";
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
        foreach($arrFormField AS $$arrFormField_key => $arrFormField_value) {
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

$user_permission = get_session("user_permission");
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
            
            if(count(array_intersect($user_permission["groups"], $limit_by_groups))) {
                if(strlen($allowed_ana_cat))
                    $allowed_ana_cat .= ",";

                $allowed_ana_cat .= $db->getField("ID", "Number", true);
            }
        } else {
            if(strlen($allowed_ana_cat))
                $allowed_ana_cat .= ",";

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
            " . (AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0
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
                    ) AS billstate_label"
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
        WHERE 1   
            " . (strlen($allowed_ana_cat) && $category != "nocategory"
                ? " AND anagraph_categories.ID IN (" . $db->toSql($allowed_ana_cat, "Text", false) . ")"
                : ""
            ) . "
            " . (strlen($category)
                ? ($category == "nocategory"
                    ? " AND anagraph.categories = '' "
                    : " AND FIND_IN_SET(" . $db->tosql($ID_category, "Number")  . ", anagraph.categories) "
                )
                : "" 
            ) . "
        GROUP BY anagraph.ID
        ORDER BY anagraph.last_update DESC, billreference 
        LIMIT $real_page, $real_next_page";
$db->query($sSQL);
if ($db->nextRecord())
{
    $i = 0;
    do
    {
        $php_array[$i]["id"] = $db->getField("ID", "Text", true);
        $php_array[$i]["billreference"] = $db->getField("billreference", "Text", true); 
        $php_array[$i]["billcf"] = $db->getField("billcf", "Text", true);
        $php_array[$i]["billpiva"] = $db->getField("billpiva", "Text", true);
        $php_array[$i]["billaddress"] = $db->getField("billaddress", "Text", true);
        $php_array[$i]["billcap"] = $db->getField("billcap", "Text", true);
        $php_array[$i]["billtown"] = $db->getField("billtown", "Text", true);
        $php_array[$i]["billprovince"] = $db->getField("billprovince", "Text", true);
        if(!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0))
            $php_array[$i]["billstate"] = $db->getField("billstate_label", "Text", true);
            
        $php_array[$i]["shippingreference"] = $db->getField("shippingreference", "Text", true);
        $php_array[$i]["shippingaddress"] = $db->getField("shippingaddress", "Text", true);
        $php_array[$i]["shippingcap"] = $db->getField("shippingcap", "Text", true);
        $php_array[$i]["shippingtown"] = $db->getField("shippingtown", "Text", true);
        $php_array[$i]["shippingprovince"] = $db->getField("shippingprovince", "Text", true);
        if(!(AREA_ECOMMERCE_SHIPPING_LIMIT_STATE > 0))
            $php_array[$i]["shippingstate"] = $db->getField("shippingstate", "Text", true);

        $php_array[$i]["email"] = $db->getField("anagraph_email", "Text", true);
        if(check_function("get_user_avatar")) {
			$php_array[$i]["avatar"] = get_user_avatar($db->getField("anagraph_avatar", "Text", true), false, $php_array[$i]["email"], cm_showfiles_get_abs_url("/avatar"));
        }

        if(strlen($db->getField("categories", "Text", true)))
            $php_array[$i]["categories"] = explode(",", $db->getField("categories", "Text", true));
        else
            $php_array[$i]["categories"] = array();

        $php_array[$i]["custom"] = array();
        $php_array[$i]["custom"]["name"] = $db->getField("name", "Text", true);
        $php_array[$i]["custom"]["surname"] = $db->getField("surname", "Text", true);
        if(is_array($arrFormField) && count($arrFormField)) {
            foreach($arrFormField AS $field_key => $field_value) {
                $field_name = $field_value["name"];
                if(strlen($db->getField($field_name, "Text", true)))
                    $php_array[$i]["custom"][$field_name] = $db->getField($field_name, "Text", true);

            } reset($arrFormField);
        } 
        $php_array[$i]["last_update"] = $db->getField("last_update", "Text", true);
        
        
        $sSQL = "SELECT ecommerce_documents_bill.*
        			, ecommerce_documents_type.name AS `bill_type`
                    , IF(ecommerce_documents_bill.operation = 'reveived'
                        , ecommerce_documents_bill.total_bill
                        , 0
                    ) AS cost
                    , IF(ecommerce_documents_bill.operation = 'sent'
                        , ecommerce_documents_bill.total_bill
                        , 0
                    ) AS revenue
                    " . (AREA_ECOMMERCE_USE_SHIPPING
                        ? "
                            , '' AS shippingstate
                            , ecommerce_order.ID_ecommerce_shipping AS ID_shipping
                            , ecommerce_order.shipping_method AS shipping_method
                            , ecommerce_order.shipping_evade AS shipping_evade
                            , ecommerce_order.shipping_trace AS shipping_trace
                            , ecommerce_shipping.tracking_url AS tracking_url
                            , ecommerce_shipping.name AS tracking_name
                        "
                        : ""
                    ) . "
                    , (GROUP_CONCAT(DISTINCT 
                            CONCAT(
                                '*'
                                , ecommerce_documents_payments.date
                                , '|' 
                                , ecommerce_mpay.name
                                , '|'
                                , ecommerce_documents_payments.value + IF(ecommerce_mpay.enable_vat_calc
                                        	, IF(ecommerce_mpay.decumulation = 'scorporo'
                                        		, ecommerce_documents_payments.tax_price
                                        		, ecommerce_documents_payments.tax_price + ROUND(ecommerce_documents_payments.tax_price * ecommerce_documents_payments.tax_vat_rate / 100, 2)
                                        	)
                                        	, ecommerce_documents_payments.tax_price
                                        )	
                                , '|-'
                                , ecommerce_documents_payments.payed_value + ecommerce_documents_payments.rebate
                                , '|+'
                                , ecommerce_documents_payments.rebate
                            )
                         ORDER BY ecommerce_documents_payments.date DESC) 
                    ) AS paymentsmethod
                FROM ecommerce_documents_bill 
                	LEFT JOIN ecommerce_documents_type ON ecommerce_documents_type.ID = ecommerce_documents_bill.ID_type
                    LEFT JOIN ecommerce_documents_payments ON ecommerce_documents_payments.ID_bill = ecommerce_documents_bill.ID
                    LEFT JOIN ecommerce_mpay ON ecommerce_mpay.ID = ecommerce_documents_payments.ID_ecommerce_mpay
					LEFT JOIN ecommerce_order ON ecommerce_order.ID_bill = ecommerce_documents_bill.ID
                    " . (AREA_ECOMMERCE_USE_SHIPPING
                        ? "
                            LEFT JOIN ecommerce_shipping ON ecommerce_shipping.ID = ecommerce_order.ID_ecommerce_shipping
                        "
                        : ""
                    ) . "
                WHERE ecommerce_documents_bill.ID_anagraph = " . $db_operation->toSql($php_array[$i]["id"], "Number") . "
                GROUP BY ecommerce_documents_bill.ID
                ";
        $db_operation->query($sSQL);
        if($db_operation->nextRecord()) {
        	$n = 0;
			do {
				$mpay = explode(",", $db_operation->getField("paymentsmethod", "Text", true));
	            if(is_array($mpay) && count($mpay)) {
	                foreach($mpay AS $mpay_value) {
	                    if(strlen($mpay_value)) {
	                        $mpay_data = explode("|", $mpay_value);
	                        if(is_array($mpay_data) && count($mpay_data)) {
	                            $p = 0;
	                            foreach($mpay_data AS $mpay_data_value) {
    								if(is_numeric($mpay_data_value)
    									|| substr($mpay_data_value, 0,1) == "-"
    									|| substr($mpay_data_value, 0,1) == "+"
    								) {
	                                    if(substr($mpay_data_value, 0,1) == "-") {
	                                            $payment_payed = new ffData(substr($mpay_data_value, 1), "Number");

		                                        $php_array[$i]["operation"][$n]["paymentsmethod"][$p]["payed"] = $payment_payed->getValue("Currency", FF_LOCALE);
	                                            
	                                            $total_payment_payed = $total_payment_payed + $payment_payed->getValue();
	                                    } elseif(substr($mpay_data_value, 0,1) == "+") {
	                                            $payment_rebate = new ffData(substr($mpay_data_value, 1), "Number");
	                                            
	                                            $php_array[$i]["operation"][$n]["paymentsmethod"][$p]["rebate"] = $payment_rebate->getValue("Currency", FF_LOCALE);

	                                            $total_payment_rebate = $total_payment_rebate + $payment_rebate->getValue();
	                                    } else {
	                                        $payment_value = new ffData($mpay_data_value, "Number");

	                                        $php_array[$i]["operation"][$n]["paymentsmethod"][$p]["value"] = $payment_value->getValue("Currency", FF_LOCALE);
	                                        
	                                        $total_payment_set = $total_payment_set + $payment_value->getValue();
	                                    }
	                                } else {
	                                    if(substr($mpay_data_value, 0,1) == "*") {
	                                        $payment_date = new ffData(substr($mpay_data_value, 1), "Date", FF_SYSTEM_LOCALE);
	                                        
	                                        $php_array[$i]["operation"][$n]["paymentsmethod"][$p]["date"] = $payment_date->getValue("Date", FF_LOCALE);
	                                    } else {
	                                        $php_array[$i]["operation"][$n]["paymentsmethod"][$p]["description"] = ffTemplate::_get_word_by_code($mpay_data_value);
	                                    }
	                                }
									$p++;
	                            }

								if(!($payment_payed instanceof ffData)) {
									$payment_payed = new ffData("0", "Number");
								}
	                        }
	                    }
	                }
	            }

				$php_array[$i]["operation"][$n]["date"] = $db_operation->getField("date", "Date")->getValue("Date", FF_LOCALE);
				$php_array[$i]["operation"][$n]["type"] = $db_operation->getField("bill_type", "Text", true);
				$php_array[$i]["operation"][$n]["cost"] = $db_operation->getField("cost", "Number")->getValue("Currency", FF_LOCALE);
				$php_array[$i]["operation"][$n]["revenue"] = $db_operation->getField("revenue", "Number")->getValue("Currency", FF_LOCALE);
				
				$n++;
			} while($db_operation->nextRecord());
        }
        
        $i++;
    }
    while ($db->nextRecord());
}
//print_r($php_array);
//die();
//die("test");
echo json_encode(array(
		"data" => $php_array
		, "count" => $db->numRows()
	));
die();
