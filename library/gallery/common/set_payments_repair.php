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
function set_payments_repair() {
    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT ecommerce_documents_payments.ID AS ID_payment
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
            )";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $ID_to_delete = array();
        do {
            $ID_to_delete[] = $db->getField("ID_payment", "Number", true);
        } while($db->nextRecord());
        
        if(count($ID_to_delete)) {
            $sSQL = "DELETE FROM ecommerce_documents_payments WHERE ecommerce_documents_payments.ID IN ( " . implode(",", $ID_to_delete) . " )";
            $db->execute($sSQL);
        }
    }
    
    $sSQL = "SELECT 
                IF(ISNULL(SUM(`ecommerce_documents_payments`.value)) 
                    , 0
                    , SUM(`ecommerce_documents_payments`.value)
                ) AS pvalue
                , ecommerce_documents_bill.total_bill AS bprice
                , `ecommerce_documents_bill`.ID
            FROM `ecommerce_documents_bill`
            LEFT JOIN ecommerce_documents_payments ON ecommerce_documents_payments.ID_bill = ecommerce_documents_bill.ID
            WHERE 1
            GROUP BY ecommerce_documents_payments.ID_bill
            HAVING pvalue <> bprice";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $sSQL = "SELECT * FROM ecommerce_mpay WHERE name = " . $db->toSql("mpay_not_set");
        $db->query($sSQL);
        if($db->nextRecord()) {
            $ID_mpay = $db->getField("ID", "Number")->getValue();
        } else {
            $sSQL = "INSERT INTO `ecommerce_mpay` 
                    (
                        `ID`
                        , `name`
                        , `days`
                        , `path`
                        , `status`
                        , `ecommerce`
                    )
                    VALUES 
                    (
                        NULL 
                        , " . $db->toSql("mpay_not_set") . "
                        , " . $db->toSql("0", "Number") . "
                        , " . $db->toSql("") . "
                        , " . $db->toSql("1") . "
                        , " . $db->toSql("0") . "
                    )";
            $db->execute($sSQL);
            $ID_mpay = $db->getInsertID(true);
        }

        do {
            $total_bill = $db->getField("bprice", "Number", true); 
            $payment_value = $db->getField("pvalue", "Number", true); 
            $ID_bill = $db->getField("ID", "Number", true); 

            if($total_bill > 0) {
                if($total_bill > $payment_value) {
                    $payments_total = $total_bill - $payment_value;
                    $payments_note = "";
                    $sSQL = "SELECT ID
                                FROM ecommerce_documents_payments
                                WHERE ecommerce_documents_payments.ID_bill = " . $db->toSql($ID_bill, "Number") . "
                                AND ecommerce_documents_payments.ID_ecommerce_mpay = " . $db->toSql($ID_mpay, "Number");
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        $sSQL = "UPDATE ecommerce_documents_payments 
                                SET ecommerce_documents_payments.value = " . $db->toSql($total_bill, "Number") . " 
                                    , ecommerce_documents_payments.date = (SELECT ecommerce_documents_bill.date FROM ecommerce_documents_bill WHERE ecommerce_documents_bill.ID = " . $db->toSql($ID_bill, "Number")  . ")
                                WHERE ecommerce_documents_payments.ID_bill = " . $db->toSql($ID_bill, "Number") . "
                                    AND ecommerce_documents_payments.ID_ecommerce_mpay = " . $db->toSql($ID_mpay, "Number");
                        $db->execute($sSQL);
                    } else {
                        $sSQL = "INSERT INTO `ecommerce_documents_payments` 
                                (
                                    `ID`
                                    , SID
                                    , `ID_bill`
                                    , `ID_ecommerce_mpay`
                                    , `operation`
                                    , `description`
                                    , `value`
                                    , `payed_value`
                                    , `status`
                                    , `owner`
                                    , `date`
                                    , `last_update`
                                    , `tax_price`
                                )
                                VALUES 
                                (
                                    NULL 
                                    , ''
                                    , " . $db->toSql($ID_bill, "Number") . " 
                                    , " . $db->toSql($ID_mpay, "Number") . " 
                                    , (SELECT IF(ecommerce_documents_bill.operation = 'sent', 'received', 'sent') FROM ecommerce_documents_bill WHERE ecommerce_documents_bill.ID = " . $db->toSql($ID_bill, "Number")  . ")
                                    , " . $db->toSql($payments_note) . " 
                                    , " . $db->toSql($payments_total, "Number") . " 
                                    , " . $db->toSql(0, "Number") . " 
                                    , " . $db->toSql("1") . " 
                                    , " . $db->toSql("-1", "Number") . "
                                    , (SELECT ecommerce_documents_bill.date FROM ecommerce_documents_bill WHERE ecommerce_documents_bill.ID = " . $db->toSql($ID_bill, "Number")  . ")
                                    , " . $db->toSql(time()) . "
                                    , (SELECT ecommerce_mpay.price FROM ecommerce_mpay WHERE ecommerce_mpay.ID = " . $db->toSql($ID_mpay, "Number") . ")
                                     
                                )";
                        $db->execute($sSQL);
                    }
                } elseif($total_bill < $payment_value) {
                    $payments_total_diff = $payment_value - $total_bill;
                    $payments_editable = array();
                    $sSQL = "SELECT ecommerce_documents_payments.* FROM ecommerce_documents_payments WHERE ecommerce_documents_payments.ID_bill = " . $db->toSql($ID_bill, "Number") . " AND ecommerce_documents_payments.ID_ecommerce_mpay = " . $db->toSql($ID_mpay, "Number");
                    $db->query($sSQL);
                    if($db->nextRecord()) {
                        do {
                            $payments_editable[$db->getField("ID", "Number", true)] = $db->getField("value", "Number", true);
                        } while($db->nextRecord());
                    }
                    
                    if(count($payments_editable)) {
                        foreach($payments_editable AS $payments_editable_key => $payments_editable_value) {
                            if($payments_editable_value > $payments_total_diff) {
                                $sSQL = "UPDATE ecommerce_documents_payments SET ecommerce_documents_payments.value = " . $db->toSql(($payments_editable_value - $payments_total_diff), "Number") . " WHERE ecommerce_documents_payments.ID = " . $db->toSql($payments_editable_key, "Number");
                                $db->execute($sSQL);
                                break;
                            } elseif($payments_editable_value == $payments_total_diff) {
                                $sSQL = "DELETE FROM ecommerce_documents_payments WHERE ecommerce_documents_payments.ID = " . $db->toSql($payments_editable_key, "Number");
                                $db->execute($sSQL);
                                break;
                            } elseif($payments_editable_value < $payments_total_diff) {    
                                $sSQL = "DELETE FROM ecommerce_documents_payments WHERE ecommerce_documents_payments.ID = " . $db->toSql($payments_editable_key, "Number");
                                $db->execute($sSQL);
                                $payments_total_diff = $payments_total_diff - $payments_editable_value;
                            }
                        }
                    }
                }
            } else {
                $sSQL = "DELETE FROM ecommerce_documents_payments WHERE ecommerce_documents_payments.ID_bill = " . $db->toSql($ID_bill, "Number") . " AND ecommerce_documents_payments.ID_ecommerce_mpay = " . $db->toSql($ID_mpay, "Number");
                $db->query($sSQL);
            }
        } while($db->nextRecord());
    }
    
    return $strError;
}