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
function check_bill($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;
    
    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT 
                (SELECT COUNT( * ) AS ID_bill
                    FROM ecommerce_documents_bill
                    WHERE ecommerce_documents_bill.ID NOT
                    IN (
                        SELECT DISTINCT ecommerce_documents_bill.ID
                        FROM ecommerce_documents_bill
                        INNER JOIN ecommerce_documents_bill_detail ON ecommerce_documents_bill_detail.ID_bill = ecommerce_documents_bill.ID
                    )
                ) AS bill_with_no_detail
                , (SELECT COUNT( * ) 
                    FROM 
                    (
                        SELECT SUM( ecommerce_documents_bill_detail.price ) AS dprice
                            , ecommerce_documents_bill.total_bill AS bprice
                            , ecommerce_documents_bill_detail.ID_items
                            , ecommerce_documents_bill.ID AS ID_bill
                        FROM `ecommerce_documents_bill`
                        INNER JOIN ecommerce_documents_bill_detail ON ecommerce_documents_bill_detail.ID_bill = ecommerce_documents_bill.ID
                        WHERE 1
                        GROUP BY ecommerce_documents_bill_detail.ID_bill
                        HAVING dprice > 0
                            AND bprice = 0
                    ) AS tbl_src                    
                ) AS bill_no_sync
                , ( SELECT COUNT( * ) FROM ecommerce_documents_bill WHERE ecommerce_documents_bill.date = '0000-00-00') AS bill_no_date
                , ( SELECT COUNT( * ) FROM ecommerce_documents_bill WHERE ecommerce_documents_bill.total_bill = 0) AS bill_no_value
                , ( SELECT COUNT( * ) FROM ecommerce_documents_bill_detail WHERE ecommerce_documents_bill_detail.description = '' AND ecommerce_documents_bill_detail.ID_items > 0) AS bill_detail_no_description
            ";
    $db->query($sSQL);
    if($db->nextRecord()) {
        if($show_info) {
            $check["info"] .= ffTemplate::_get_word_by_code("bill_with_no_detail") . $db->getField("bill_with_no_detail", "Number", true) . "<br>";
            $check["info"] .= ffTemplate::_get_word_by_code("bill_no_sync") . $db->getField("bill_no_sync", "Number", true) . "<br>";
            $check["info"] .= ffTemplate::_get_word_by_code("bill_no_date") . $db->getField("bill_no_date", "Number", true) . "<br>";
            $check["info"] .= ffTemplate::_get_word_by_code("bill_no_value") . $db->getField("bill_no_value", "Number", true) . "<br>";
            $check["info"] .= ffTemplate::_get_word_by_code("bill_detail_no_description") . $db->getField("bill_detail_no_description", "Number", true) . "<br>";
        }

        if($db->getField("bill_with_no_detail", "Number", true) > 0)
            $check["status"] .= ffTemplate::_get_word_by_code("bill_with_no_detail") . $db->getField("bill_with_no_detail", "Number", true) . "<br>";
        if($db->getField("bill_no_sync", "Number", true) > 0)
            $check["status"] .= ffTemplate::_get_word_by_code("bill_no_sync") . $db->getField("bill_no_sync", "Number", true) . "<br>";
        if($db->getField("bill_detail_no_description", "Number", true) > 0)
            $check["status"] .= ffTemplate::_get_word_by_code("bill_detail_no_description") . $db->getField("bill_detail_no_description", "Number", true) . "<br>";
    }
    return $check;    
}