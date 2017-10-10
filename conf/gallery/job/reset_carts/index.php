<?php
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
  
?>
