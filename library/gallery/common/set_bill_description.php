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
function set_bill_description() {
    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT DISTINCT ecommerce_documents_bill_detail.ID AS ID_detail
                , ecommerce_documents_bill_detail.ID_items AS ID_items
                ,  ecommerce_documents_bill_detail.tbl_src AS tbl_src
                , CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) AS full_path
                , vgallery_nodes.name AS name
            FROM ecommerce_documents_bill_detail 
                INNER JOIN vgallery_nodes ON ecommerce_documents_bill_detail.ID_items = vgallery_nodes.ID
            WHERE ecommerce_documents_bill_detail.ID_items > 0
                AND ecommerce_documents_bill_detail.tbl_src = 'vgallery_nodes'
                AND ecommerce_documents_bill_detail.description NOT LIKE '%<span%>%'";
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