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
 * @subpackage cronjob
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
	$db = ffDB_Sql::factory();
	
	$sSQL = "SELECT ecommerce_order.*
			FROM ecommerce_order
                LEFT JOIN ecommerce_documents_bill ON ecommerce_documents_bill.ID = ecommerce_order.ID_bill  
			WHERE ecommerce_order.created > 0
                AND ecommerce_order.timeout > 0
                AND " . $db->toSql(time(), "Number") . " > (ecommerce_order.created + ecommerce_order.timeout)
                AND IFNULL( 
                        (
                            SELECT SUM(IF(ecommerce_mpay.disable_service > 0
                                            , ecommerce_documents_payments.value
                                            , ecommerce_documents_payments.payed_value
                                        )
                                    ) 
                            FROM ecommerce_documents_payments
                                INNER JOIN ecommerce_mpay ON ecommerce_documents_payments.ID_ecommerce_mpay = ecommerce_mpay.ID
                            WHERE ecommerce_documents_payments.ID_bill = ecommerce_documents_bill.ID
                        )
                        , 0
                ) = 0";
    $db->query($sSQL);
    if($db->nextRecord()) {
        if(check_function("ecommerce_delete_documents_bill_by_order")) {
            do {
                ecommerce_delete_documents_bill_by_order($db->getField("ID", "Number", true));
                
                $key_order[] = $db->getField("ID", "Number", true);
            } while($db->nextRecord());

            $sSQL = "DELETE FROM ecommerce_order WHERE ecommerce_order.ID IN(" . $db->toSql(implode(",", $key_order), "Text", false). ")";
            $db->execute($sSQL);
            
            unset_session("cart_data");
        }
    }
  
