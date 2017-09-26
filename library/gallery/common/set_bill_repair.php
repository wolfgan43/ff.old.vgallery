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
function set_bill_repair() {
    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT ecommerce_documents_bill.ID AS ID_bill
            FROM ecommerce_documents_bill
            WHERE ecommerce_documents_bill.ID NOT
            IN (
                SELECT DISTINCT ecommerce_documents_bill.ID
                FROM ecommerce_documents_bill
                INNER JOIN ecommerce_documents_bill_detail ON ecommerce_documents_bill_detail.ID_bill = ecommerce_documents_bill.ID
            )";
    $db->query($sSQL);        
    if($db->nextRecord()) {
        $ID_to_delete = array();
        do {
            $ID_to_delete[] = $db->getField("ID_bill", "Number", true);
        } while($db->nextRecord());
        
        if(count($ID_to_delete)) {
            $sSQL = "DELETE FROM ecommerce_documents_bill WHERE ecommerce_documents_bill.ID IN ( " . implode(",", $ID_to_delete) . " )";
            $db->execute($sSQL);
        }
    }

    $sSQL = "SELECT SUM( ecommerce_documents_bill_detail.price ) AS dprice
                , ecommerce_documents_bill.total_bill AS bprice
                , ecommerce_documents_bill_detail.ID_items
                , ecommerce_documents_bill.ID AS ID_bill
            FROM `ecommerce_documents_bill`
            INNER JOIN ecommerce_documents_bill_detail ON ecommerce_documents_bill_detail.ID_bill = ecommerce_documents_bill.ID
            WHERE 1
            GROUP BY ecommerce_documents_bill_detail.ID_bill
            HAVING dprice > 0
                AND bprice = 0";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            if(check_function("ecommerce_recalc_documents_bill"))
                ecommerce_recalc_documents_bill($db->getField("ID_bill", "Number", true));
            
        } while($db->nextRecord());
    }
    
    
    $sSQL = "SELECT SUM( `ecommerce_documents_payments`.value ) AS pvalue
                , ecommerce_documents_bill.total_bill AS bprice
                , `ecommerce_documents_payments`.ID
            FROM `ecommerce_documents_payments`
                INNER JOIN ecommerce_documents_bill ON ecommerce_documents_payments.ID_bill = ecommerce_documents_bill.ID
            WHERE 1
            GROUP BY ecommerce_documents_payments.ID_bill
            HAVING pvalue = 0
            AND bprice >0";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $sSQL = "UPDATE ecommerce_documents_payments 
                    SET ecommerce_documents_payments.value = " . $db->getField("bprice", "Number", true) . "
                        , ecommerce_documents_payments.payed_value = " . $db->getField("bprice", "Number", true) . " 
                    WHERE ecommerce_documents_payments.ID = " . $db->getField("ID", "Number", true);
            $db->execute($sSQL);
        } while($db->nextRecord());
    }
    
    
    $sSQL = "SELECT DISTINCT ecommerce_documents_bill_detail.ID_items, ecommerce_documents_bill_detail.tbl_src
            FROM ecommerce_documents_bill_detail
                INNER JOIN ecommerce_documents_bill ON ecommerce_documents_bill.ID = ecommerce_documents_bill_detail.ID_bill
            WHERE ecommerce_documents_bill_detail.ID_items > 0 
                AND ecommerce_documents_bill_detail.tbl_src <> ''";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            if(check_function("ecommerce_recalc_relation_bill_by_criteria"))
                $arrRelation_bill = ecommerce_recalc_relation_bill_by_criteria
                                    (
                                        array(
                                            "tbl_src" => $db->getField("tbl_src", "Text", true)
                                            , "ID_items" => $db->getField("ID_items", "Number", true)
                                        )
                                        , true
                                    );
        } while($db->nextRecord());
    }
    
    $sSQL = "SELECT count( *  ) AS count_relation
                , ecommerce_documents_bill_detail.description
                , ecommerce_documents_bill.date
                , ecommerce_documents_bill.ID_type
                , REPLACE(REPLACE(ecommerce_documents_bill_detail.SID, 'sent-', ''), 'received-', '') AS SID
            FROM ecommerce_documents_bill_detail
                INNER JOIN ecommerce_documents_bill ON ecommerce_documents_bill.ID = ecommerce_documents_bill_detail.ID_bill
            WHERE 1
                AND ecommerce_documents_bill_detail.ID_items =0
                AND ecommerce_documents_bill_detail.tbl_src = ''
            GROUP BY 
                REPLACE(REPLACE(ecommerce_documents_bill_detail.SID, 'sent-', ''), 'received-', '')
                , ecommerce_documents_bill.ID_type
            HAVING count_relation >= 2
            ORDER BY count_relation DESC";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            if(check_function("ecommerce_recalc_relation_bill_by_criteria"))
                $arrRelation_bill = ecommerce_recalc_relation_bill_by_criteria
                                    (
                                        array(
                                            "tbl_src" => ""
                                            , "ID_items" => "0"
                                            , "REPLACE(REPLACE(ecommerce_documents_bill_detail.SID, 'sent-', ''), 'received-', '')" => $db->getField("SID", "Text", true)
                                            , "ecommerce_documents_bill.ID_type" => $db->getField("ID_type", "Number", true)
                                        )
                                        , true
                                    );
        } while($db->nextRecord());
    }
    
    $sSQL = "SELECT ecommerce_documents_bill_detail.ID AS ID_detail
                , ecommerce_documents_bill_detail.ID_items AS ID_items
                ,  ecommerce_documents_bill_detail.tbl_src AS tbl_src
                , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
            FROM ecommerce_documents_bill_detail 
                INNER JOIN vgallery_nodes ON ecommerce_documents_bill_detail.ID_items = vgallery_nodes.ID
            WHERE ecommerce_documents_bill_detail.ID_items > 0
                AND ecommerce_documents_bill_detail.tbl_src = 'vgallery_nodes'
                AND ecommerce_documents_bill_detail.description = ''";
    $db->query($sSQL);
    if($db->nextRecord()) {
         do {
            $ID_detail = $db->getField("ID_detail", "Number", true);
            $ID_items = $db->getField("ID_items", "Number", true);
            $full_path = $db->getField("full_path", "Text", true);
            $tbl_src = $db->getField("tbl_src", "Text", true);
            
            $arrItem[$tbl_src][$ID_items] = $ID_detail;
        } while ($db->nextRecord());
        
        if(is_array($arrItem["vgallery_nodes"]) && count($arrItem["vgallery_nodes"])) {
        	$vgallery_data =  get_vgallery_card_by_id(array_keys($arrItem["vgallery_nodes"]));
			foreach($vgallery_data AS $ID_item => $item_data) {
		        $sSQL = "UPDATE ecommerce_documents_bill_detail 
		                SET ecommerce_documents_bill_detail.description = " . $db->toSql($item_data["card"]) . " 
		                WHERE ecommerce_documents_bill_detail.ID = " . $db->toSql($arrItem["vgallery_nodes"][$ID_item], "Number");
		        $db->execute($sSQL); 			
			}
        }
        /* nn puo avverarsi
        if(is_array($arrItem["files"]) && count($arrItem["files"])) {
			$sSQL = "SELECT files.ID
						, files.alias 
						, files.name 
					FROM files
						INNER JOIN files_rel_languages ON files_rel_languages.ID_files = files.ID 
							AND files_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number") . "
					WHERE files.ID IN(" . $db->toSql(implode(", ", array_keys($arrItem["files"])), "Text", false) . ")";
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$arrDescription[$arrItem["files"][$db->getField("ID", "Number", true)]] = ($db->getField("alias", "Text", true) ? $db->getField("alias", "Text", true) : $db->getField("name", "Text", true));
				} while($db->nextRecord());
				
				if(is_array($arrDescription) && count($arrDescription)) {
					foreach($arrDescription AS $ID_detail => $description) {
				        $sSQL = "UPDATE ecommerce_documents_bill_detail 
				                SET ecommerce_documents_bill_detail.description = " . $db->toSql($description) . " 
				                WHERE ecommerce_documents_bill_detail.ID = " . $db->toSql($ID_detail, "Number");
				        $db->execute($sSQL); 			
					}
				}
			}        
		}*/
    }
    
    return $strError;
}