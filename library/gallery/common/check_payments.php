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
function check_payments($show_info = true) {
    $check["info"] = "";
    $check["status"] = false;

    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT 
                (
                    SELECT COUNT( * )
                    FROM ecommerce_documents_payments
                    WHERE ecommerce_documents_payments.ID NOT
                    IN (
                            SELECT DISTINCT ecommerce_documents_payments.ID
                            FROM ecommerce_documents_payments
                                INNER JOIN ecommerce_documents_bill ON ecommerce_documents_payments.ID_bill = ecommerce_documents_bill.ID
                        UNION 
                            SELECT ecommerce_documents_payments.ID
                            FROM ecommerce_documents_payments
                            WHERE ecommerce_documents_payments.ID_bill = 0
                    )
                ) AS payments_with_no_bill
                , (SELECT COUNT( * ) 
                    FROM 
                    (
                        SELECT 
                            IF(ISNULL(SUM(`ecommerce_documents_payments`.value)) 
                                , 0
                                , SUM(`ecommerce_documents_payments`.value)
                            ) AS pvalue
                            , ecommerce_documents_bill.total_bill AS bprice
                            , `ecommerce_documents_payments`.ID
                        FROM `ecommerce_documents_bill`
                        LEFT JOIN ecommerce_documents_payments ON ecommerce_documents_payments.ID_bill = ecommerce_documents_bill.ID
                        WHERE 1
                        GROUP BY ecommerce_documents_payments.ID_bill
                        HAVING pvalue <> bprice
                    ) AS tbl_src                    
                ) AS payments_no_sync
            ";
    $db->query($sSQL);
    if($db->nextRecord()) {
        if($show_info) {
            $check["info"] .= ffTemplate::_get_word_by_code("payments_with_no_bill") . $db->getField("payments_with_no_bill", "Number", true) . "<br>";
            $check["info"] .= ffTemplate::_get_word_by_code("payments_no_sync") . $db->getField("payments_no_sync", "Number", true) . "<br>";
        }

        if($db->getField("payments_with_no_bill", "Number", true) > 0)
            $check["status"] .= ffTemplate::_get_word_by_code("payments_with_no_bill") . $db->getField("payments_with_no_bill", "Number", true) . "<br>";
        if($db->getField("payments_no_sync", "Number", true) > 0)
            $check["status"] .= ffTemplate::_get_word_by_code("payments_no_sync") . $db->getField("payments_no_sync", "Number", true) . "<br>";
    }    
    return $check;    
}