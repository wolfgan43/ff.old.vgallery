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
function api_get_operation($limit_data = null, $params = null, $sort_field = null, $sort_dir = null, $search = null) {
    $db = ffDB_Sql::factory();
    $sort = null;
        
    $schema = array("add_field" => array(
	    "bill_type" => " ecommerce_documents_type.name AS `bill_type` "
	    , "cost" => " IF(ecommerce_documents_bill.operation = 'reveived'
		                , ecommerce_documents_bill.total_bill
		                , 0
		            ) AS cost"
	    , "revenue" => "IF(ecommerce_documents_bill.operation = 'sent'
	                    , ecommerce_documents_bill.total_bill
	                    , 0
	                ) AS revenue"
	    , "paymentsmethod" => "(GROUP_CONCAT(DISTINCT 
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
	                ) AS paymentsmethod"
    ));
    if(Cms::env("AREA_ECOMMERCE_USE_SHIPPING")) {
		$schema["add_field"]["shippingstate"] = "'' AS shippingstate";
		$schema["add_field"]["ID_shipping"] = "ecommerce_order.ID_ecommerce_shipping AS ID_shipping";
		$schema["add_field"]["shipping_method"] = "ecommerce_order.shipping_method AS shipping_method";
		$schema["add_field"]["shipping_evade"] = "ecommerce_order.shipping_evade AS shipping_evade";
		$schema["add_field"]["shipping_trace"] = "ecommerce_order.shipping_trace AS shipping_trace";
		$schema["add_field"]["tracking_url"] = "ecommerce_shipping.tracking_url AS tracking_url";
		$schema["add_field"]["tracking_name"] = "ecommerce_shipping.name AS tracking_name";
		$schema["add_field"]["shipping_status"] = "(IF(ecommerce_order.ID_ecommerce_shipping > 0 || ecommerce_documents_bill.shipping_price > 0
						                                , IF(ecommerce_order.shipping_evade > 0
						                                    , 'evade'
						                                    , 'noevade'
						                                )
						                                , 'notset'
						                            )
						                        ) AS shipping_status";
    }

    $sSQL_add_field = "";
    $sSQL_add_field_empty = "";
	if(is_array($schema["add_field"]) && count($schema["add_field"])) {
		foreach($schema["add_field"] AS $add_field_key => $add_field_value) {
			$sSQL_add_field .= ", " . $add_field_value;
			$sSQL_add_field_empty .= ", '' AS " . $add_field_key;

			if(is_array($sort_field) && count($sort_field)) {
            	if(array_key_exists($add_field_key, $sort_field)) {
            		if(strlen($sort))
            			$sort .= ", ";

					$sort .= $sort_field[$add_field_key];
            	}
			}
            if(strlen($search)) {
	            if(strlen($sSQL_having))
	                $sSQL_having .= " OR ";
	            
	            $sSQL_having .= " `" . $add_field_key . "` LIKE '%" . $db->toSql(str_replace(" ", "%", $search), "Text", false) . "%' COLLATE utf8_general_ci";
			}
		}
	}

	if($params && array_key_exists("operation", $params)) {
		$managed_params["operation"] = $params["operation"];
		unset($params["operation"]);
	}
    if(strlen($search) || (is_array($sort_field) && count($sort_field)) || (is_array($params) && count($params))) {
        $sSQL_having = "";
        
        $sSQL = "SELECT ecommerce_documents_bill.* 
        			$sSQL_add_field_empty
        		FROM ecommerce_documents_bill LIMIT 1";
        $db->query($sSQL);
        if(is_array($db->fields) && count($db->fields)) {
		    if(is_array($params) && count($params)) {
    			foreach($params AS $param_key => $param_value) {
    				if(array_key_exists($param_key, $db->fields)) {
    					$sSQL_Where_params .= " AND `" . $param_key . "` = " . $db->toSql($param_value);
    				}
    			}
		    } 
        	if(strlen($search) || (is_array($sort_field) && count($sort_field))) {
	            foreach($db->fields AS $field_value) {
					if(is_array($sort_field) && count($sort_field)) {
            			if(array_key_exists($field_value->name, $sort_field)) {
            				if(strlen($sort))
            					$sort .= ", ";

							$sort .= $sort_field[$field_value->name];
            			}
					}
	                if(strlen($search)) {
		                if(strlen($sSQL_having))
		                    $sSQL_having .= " OR ";
		                
		                $sSQL_having .= " `" . $field_value->name . "` LIKE '%" . $db->toSql(str_replace(" ", "%", $search), "Text", false) . "%' COLLATE utf8_general_ci";
					}
	            }
			}
        }
    }

    $sSQL = "SELECT ecommerce_documents_bill.*
                $sSQL_field
                $sSQL_add_field
            FROM ecommerce_documents_bill 
                LEFT JOIN ecommerce_documents_type ON ecommerce_documents_type.ID = ecommerce_documents_bill.ID_type
                LEFT JOIN ecommerce_documents_payments ON ecommerce_documents_payments.ID_bill = ecommerce_documents_bill.ID
                LEFT JOIN ecommerce_mpay ON ecommerce_mpay.ID = ecommerce_documents_payments.ID_ecommerce_mpay
                LEFT JOIN ecommerce_order ON ecommerce_order.ID_bill = ecommerce_documents_bill.ID
                " . (Cms::env("AREA_ECOMMERCE_USE_SHIPPING")
                    ? "
                        LEFT JOIN ecommerce_shipping ON ecommerce_shipping.ID = ecommerce_order.ID_ecommerce_shipping
                    "
                    : ""
                ) . "
            WHERE 1 
            	$sSQL_Where_params
                " . (strlen($managed_params["operation"])
                    ? " AND ecommerce_documents_bill.operation = " . $db->toSql($managed_params["operation"])
                    : ""
                ) . "
		        " . (strlen($limit_data["key"]) && strlen($limit_data["value"]) 
		                ? (strpos($limit_data["value"], ",") === false && !is_numeric($limit_data["value"])
                        	? " AND ecommerce_documents_bill.`" . $limit_data["key"] . "` LIKE " . $db->toSql("%" . $limit_data["value"] . "%")
                        	: " AND ecommerce_documents_bill.`" . $limit_data["key"] . "` IN (" . $db->toSql($limit_data["value"], "Text", false) . ")"
		                )
		                : "" 
		            ) . "  
            GROUP BY ecommerce_documents_bill.ID
			HAVING 1 " . (strlen($sSQL_having)
			                ? " AND (" . $sSQL_having . ")"
			                : "" 
			            ) . "
            ORDER BY " . ($sort === null 
                    ? "" 
	                : (strpos($sort, "`") === false && strpos($sort, ".") !== false ? substr($sort, strpos($sort, ".") + 1) : $sort) .  
                		($sort_dir === null ? "" : " " . $sort_dir) . ", "
                ) . "ecommerce_documents_bill.date DESC";

            
    return array("schema" => $schema
                , "sql" => $sSQL
        );       
}
