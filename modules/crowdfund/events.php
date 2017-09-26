<?php
  function mod_crowdfund_check_payment($event_name, $params, $custom_params) {
	$db = ffDB_Sql::factory();


	switch ($event_name) {
		case "cart":
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.* 
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($custom_params["ID_backer"], "Number");
			$db->query($sSQL);
			if(!$db->nextRecord()) {
				$sSQL = "DELETE FROM ecommerce_order_detail 
						WHERE (SELECT is_cart FROM ecommerce_order WHERE ecommerce_order.ID = ecommerce_order_detail.ID_order) > 0 
							AND ecommerce_order_detail.ID_order = " . $db->toSql($params["db"]->getField("ID_order", "Number"), "Number") . "
							AND ecommerce_order_detail.ID = " . $db->toSql($params["db"]->getField("ID", "Number"));
				$db->execute($sSQL);	
				if($db->affectedRows()) {
					$params["count_detail"] = $params["count_detail"] - 1;
				}				
				
				$sSQL = "SELECT ecommerce_order_detail.* 
						FROM ecommerce_order_detail
						WHERE ecommerce_order_detail.ID_order = " . $db->toSql($params["db"]->getField("ID_order", "Number"), "Number");
				$db->query($sSQL);
				if(!$db->nextRecord()) {
					$sSQL = "DELETE FROM ecommerce_order
							WHERE ecommerce_order.ID = " . $db->toSql($params["db"]->getField("ID_order", "Number"), "Number") . "
								AND ecommerce_order.is_cart > 0";
					$db->execute($sSQL);
					
					unset_session("cart_data");
				}

				return array("count_detail" => $params["count_detail"]);
			}
			break;
		case "cart_confirm":
			break;
		case "delete_order":
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.* 
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($custom_params["ID_backer"], "Number");
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_idea = $db->getField("ID_idea", "Number", true);
				
				$sSQL = "DELETE FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($custom_params["ID_backer"], "Number");
				$db->query($sSQL);
				
				mod_crowdfund_update_goal($ID_idea, true);
			}
			break;
		case "recalc_bill":
			$sSQL = "SELECT SUM(ecommerce_documents_payments.payed_value + ecommerce_documents_payments.rebate) AS total_payed_value
					FROM ecommerce_documents_payments
					WHERE ecommerce_documents_payments.ID_bill = " . $db->toSql($params["ID_bill"], "Number");
			$db->query($sSQL);
			if($db->nextRecord()) {
				$params["payments_done"] = $db->getField("total_payed_value", "Number", true);
			}
		case "mpay_payed":
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.* 
					FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers
					WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($custom_params["ID_backer"], "Number");
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_idea = $db->getField("ID_idea", "Number", true);

				$sSQL = "UPDATE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers SET 
							ID_bill = (SELECT ID_bill FROM ecommerce_order WHERE ecommerce_order.ID = " . $db->toSql($params["db"]->getField("ID_order", "Number"), "Number") . ")
							, confirmed_price = " . $db->toSql($params["payments_done"], "Number") . "
						WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_backers.ID = " . $db->toSql($custom_params["ID_backer"], "Number"); 
				$db->execute($sSQL);

				mod_crowdfund_update_goal($ID_idea, true);
			}
			break;
		default:
	}	
	return null;
}
?>
