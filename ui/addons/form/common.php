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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

function MD_form_on_check_after($component, $action) {            
    $db_check = ffDB_Sql::factory();

    switch($action) {
        case "insert":
            foreach($component->form_fields as $form_key => $form_value) {
                if($component->form_fields[$form_key]->user_vars["unic_value"]) {
                    $sSQL = "SELECT 
                                    module_form_rel_nodes_fields.value
                                FROM module_form_rel_nodes_fields 
                                    INNER JOIN module_form_fields ON module_form_fields.ID = module_form_rel_nodes_fields.ID_form_fields
                                WHERE 
                                    module_form_fields.name = " . $db_check->toSql($form_key, "Text") . "
                                    AND module_form_fields.ID_module = " . $db_check->toSql($component->key_fields["form-ID"]->default_value, "Number") . "
                                    AND module_form_rel_nodes_fields.value = " . $db_check->toSql($form_value->value);
                    $db_check->query($sSQL);
                    if($db_check->nextRecord()) {
                        reset($component->form_fields);
                        return ffTemplate::_get_word_by_code($form_key . "_not_unic_value");
                    } 
                }
            }
            reset($component->form_fields);
            break;
        case "update":
            foreach($component->form_fields as $form_key => $form_value) {
                if($component->form_fields[$form_key]->user_vars["unic_value"]) {
                    $sSQL = "SELECT 
                                    module_form_rel_nodes_fields.value
                                FROM module_form_rel_nodes_fields 
                                    INNER JOIN module_form_fields ON module_form_fields.ID = module_form_rel_nodes_fields.ID_form_fields
                                WHERE 
                                    module_form_fields.name = " . $db_check->toSql($form_key, "Text") . "
                                    AND module_form_fields.ID_module = " . $db_check->toSql($component->key_fields["form-ID"]->default_value, "Number") . "
                                    AND module_form_rel_nodes_fields.value = " . $db_check->toSql($form_value->value);
                    $db_check->query($sSQL);
                    if($db_check->nextRecord()) {
                        reset($component->form_fields);
                        return ffTemplate::_get_word_by_code($form_key . "_not_unic_value");
                    } 
                }
            }
            reset($component->form_fields);
            break;

            default:
    }

    return NULL;
}

function MD_form_on_do_action($component, $action) {
	$db = ffDB_Sql::factory();

	switch ($action) {
	    case "insert":	  
	        if(isset($component->form_fields["privacy_check"]) && $component->form_fields["privacy_check"]->getValue() == 0) {
        		$component->tplDisplayError(ffTemplate::_get_word_by_code("privacy_check_error"));
        		return true;
			}

			if(is_array($component->form_fields) && count($component->form_fields)) {
				foreach($component->form_fields AS $key => $value) {
					if($value->user_vars["extended_type"] == "Number") {
						if($value->user_vars["min"] > 0 && $value->getValue() < $value->user_vars["min"]) {
							$component->tplDisplayError($value->label . " " . ffTemplate::_get_word_by_code("form_min_val") . " " . $value->user_vars["min"]);
							return true;
						}
						if($value->user_vars["max"] > 0 && $value->getValue() > $value->user_vars["max"]) {
							$component->tplDisplayError($value->label . " " . ffTemplate::_get_word_by_code("form_max_val") . " " . $value->user_vars["max"]);
							return true;
						}
						if($value->user_vars["step"] > 1 && (!is_int($value->getValue() / $value->user_vars["step"]) || ($value->getValue() / $value->user_vars["step"]) == 0 )) {
							$component->tplDisplayError($value->label . " " . ffTemplate::_get_word_by_code("form_step_val") . " " . $value->user_vars["step"]);
							return true;
						}
					
					}
				}
			}
			
			$sSQL = "SELECT max(ID) AS max_id FROM module_form_nodes";
			$db->query($sSQL);
			if($db->nextRecord()) {
				$ID_new_node = $db->getField("max_id", "Number", true) + 1;
			} else {
				$ID_new_node = 1;
			}

			$component->key_fields["form-ID"]->setValue($ID_new_node);
			$component->user_vars["new_node"] = $ID_new_node;
			break;
		default:
	}
}

function MD_form_on_done_action($component, $action) {
	$cm = cm::getInstance();
    $db = ffDB_Sql::factory();
   
    $user_permission = get_session("user_permission");
    $uid = $user_permission["ID"];

    switch ($action) {
        case "insert":
            $last_update = time();
            $UserNID = get_session("UserNID");
			$UserID = get_session("UserID");
            
            if(AREA_SHOW_ECOMMERCE && $component->user_vars["enable_ecommerce"]) {
            	$ID_node = set_form_node($component, $uid, $component->user_vars["ID_form_node"], false);
				
				$cart_item = resolve_ecommerce_detail($component, $ID_node);

				if(check_function("ecommerce_cart_addtocart")) {
                    $tmp_user = $UserNID;
                    $tmp_username = $UserID;

                    $force_data = null;
                    if($UserID == MOD_SEC_GUEST_USER_NAME) {
                        foreach($component->form_fields as $form_key => $form_value) {
                            if($form_value->user_vars["name"] == "name") 
                                $force_data["name"] = $component->form_fields[$form_key]->getValue();
                            if($form_value->user_vars["name"] == "surname") 
                                $force_data["surname"] = $component->form_fields[$form_key]->getValue();
                            if($form_value->user_vars["name"] == "email") 
                                $force_data["email"] = $component->form_fields[$form_key]->getValue();
                            if($form_value->user_vars["name"] == "tel") 
                                $force_data["tel"] = $component->form_fields[$form_key]->getValue();
                        }

                        $tmp_username = $force_data["name"] . ($force_data["name"] && $force_data["surname"] ? " " : "") . $force_data["surname"];
                        if(strlen($tmp_username) && strlen($force_data["email"])) {
                            $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_users.* 
                                    FROM " . CM_TABLE_PREFIX . "mod_security_users 
                                    WHERE " . CM_TABLE_PREFIX . "mod_security_users.username = " . $db->toSql($tmp_username) . "
                                        AND " . CM_TABLE_PREFIX . "mod_security_users.email = " . $db->toSql($force_data["email"]);
                            $db->query($sSQL);      
                            if($db->nextRecord()) {
                                $tmp_user = $db->getField("ID", "Number", true);
                            } else {
                                $tmp_user = $UserNID;
                                $tmp_username = $UserID;
                            }
                        }
                    }

                    if(check_function("ecommerce_set_anagraph_unic_by_user"))
                        $ID_anagraph = ecommerce_set_anagraph_unic_by_user($tmp_user);

                    $cart_res = ecommerce_cart_addtocart($cart_item, "", 0, $tmp_username, $tmp_user, $component->user_vars["reset_cart"]);
                    
                    $sSQL = "UPDATE ecommerce_order SET
                                ID_anagraph = " . $db->toSql($ID_anagraph, "Number") . "
                                , `date` = CURDATE()
                                WHERE ecommerce_order.ID = " . $db->toSql($cart_res["ID_order"], "Number");
                    $db->execute($sSQL); 

				}
            	
				$cart_ret_url = $component->parent[0]->getRequestUri();
				$cart_response = array();

				if(!$component->user_vars["MD_chk"]["ajax"]) 
				{
					$cart_response["doredirects"] = true;
					if(strlen($component->user_vars["MD_chk"]["ret_url"]))
						$cart_ret_url = $component->user_vars["MD_chk"]["ret_url"];
				} else {
					if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
						$cart_XHR = "&component=" . $component->id;
					}
				}
				
				if($component->user_vars["skip_form_cart"]) {
					$component->redirect(FF_SITE_PATH . VG_SITE_CART . "/additionaldata?ret_url=" . urlencode($cart_ret_url) . $cart_XHR, $cart_response);
				} else {
					$component->redirect(FF_SITE_PATH . VG_SITE_CART . "?ret_url=" . urlencode($cart_ret_url) . $cart_XHR, $cart_response);
				}
			} else {
				$check_mail_function = check_function("process_mail");
				$res = set_form_node($component, $uid, $component->user_vars["ID_form_node"], $check_mail_function);
				if(is_array($res) && count($res)) {
					$fields = $res["mail"]["fields"];
					$to = $res["mail"]["to"];
				
	                if($component->user_vars["send_mail"]) {
                		$owner_data = null;
                        
                        $from = null;
                        if($component->user_vars["force_from_with_domclass"]) {
                            if(isset($_REQUEST[$component->id . "_ffm"]) && strlen($_REQUEST[$component->id . "_ffm"])) {
                                if($check_mail_function) {
                                    if(verifyMailbox($_REQUEST[$component->id . "_ffm"])) {
                                        $from["name"] = $_REQUEST[$component->id . "_ffm"];
                                        $from["mail"] = $_REQUEST[$component->id . "_ffm"];
                                    }
                                }
                            }
                        }

                		if($component->user_vars["force_to_with_user"]) {
                			$user_email = get_session("UserEmail");
                			if(strlen($user_email)) {
                				$to[] = $user_email;
                				
                				$owner_data = array(
                					"username" => $UserID
                					, "name" => $user_permission["name"]
                					, "surname" => $user_permission["surname"]
                					, "tel" => $user_permission["tel"]
                					, "email" => $user_email
                				);
							}
						}
	                    if($component->user_vars["ID_email"]) {
	                    	if($check_mail_function) {
				                $rc .= process_mail(
				                            $component->user_vars["ID_email"]
				                            , $to
				                            , ($component->user_vars["form_title"]
												? $component->user_vars["form_title"]	
												: ffTemplate::_get_word_by_code("form_" . $component->user_vars["form_name"])
											)
				                            , NULL
				                            , $fields
				                            , $from
				                            , NULL
				                            , NULL
				                            , false
				                            , null
				                            , true
				                            , $owner_data
											, null
											, array()
											, ""
				                        );
			                    if($component->user_vars["send_copy_to_guest"] && count($to)) {
				                    $rc .= process_mail(
				                                $component->user_vars["ID_email"]
				                                , $to
				                                , ($component->user_vars["form_title"]
													? $component->user_vars["form_title"]	
													:ffTemplate::_get_word_by_code("form_" . $component->user_vars["form_name"])
												)
				                                , NULL
				                                , $fields
				                                , $from
				                                , NULL
				                                , NULL
				                                , false
				                                , null
				                                , false
				                                , $owner_data
												, null
												, array()
												, ""
				                            );
								}
							}
	                    } else {
	                        $rc = ffTemplate::_get_word_by_code("mail_undefine");
	                    }
	                }

	                if($component->user_vars["uid"] > 0) {
                            $sSQL = "SELECT ID
                                        FROM users_rel_module_form
                                        WHERE users_rel_module_form.uid = " . $db->toSql($component->user_vars["uid"], "Number") . "
	                                AND users_rel_module_form.ID_module = " . $db->toSql($component->key_fields["form-ID"]->default_value, "Number");
                            $db->query($sSQL);
                            if($db->nextRecord()) {
                                $sSQL = "UPDATE 
                                            users_rel_module_form 
                                        SET 
                                            users_rel_module_form.ID_form_node = " . $db->toSql($ID_nodes, "Number") . " 
                                        WHERE
                                            users_rel_module_form.uid = " . $db->toSql($component->user_vars["uid"], "Number") . "
                                            AND users_rel_module_form.ID_module = " . $db->toSql($component->key_fields["form-ID"]->default_value, "Number");
                                $db->execute($sSQL);
                            } else {
	                    	if(check_function("check_user_form_request"))
	                        	check_user_form_request(array("ID" => $uid));
                            }
	                } 

					$res = $cm->doEvent("vg_on_form_done", array($component));
					
            		if(!$_REQUEST["XHR_CTX_ID"]) {
						if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
				            if($component->ret_url) {
				                $component->json_result["url"] = $component->ret_url;
				            } else {
			            		if($component->user_vars["report"]) {
			                		$component->json_result["url"] = FF_SITE_PATH . VG_SITE_NOTIFY . "/form/end" . "?keys[ID]=" . $last_update . (($component->user_vars["send_mail"] && strlen($rc)) ? "&mc=" . urlencode($rc) : "");
								} else {
                                    if(strpos($_SERVER["REQUEST_URI"], "success") === false) {
                                        if(strpos($_SERVER["REQUEST_URI"], "?") === false) {
									        $redirect = $_SERVER["REQUEST_URI"] . "?" . "success"; 
                                        } else {
                                            $redirect = $_SERVER["REQUEST_URI"] . "&" . "success";
                                        }
                                    } else {
                                        $redirect = $_SERVER["REQUEST_URI"];
                                    }
                                    $component->json_result["url"] = $redirect . (($component->user_vars["send_mail"] && strlen($rc)) ? "&mc=" . urlencode($rc) : "") . "&XHR_COMPONENT=" . $component->id;
								}
				            }
							$component->json_result["close"] = false;
							die(ffCommon_jsonenc($component->json_result, true));
						} else {
			                if($component->ret_url)
			                     $component->redirect($component->ret_url);
			                else {
		                		if($component->user_vars["report"]) {
		                    		$component->redirect(FF_SITE_PATH . VG_SITE_NOTIFY . "/form/end" . "?keys[ID]=" . $last_update . (($component->user_vars["send_mail"] && strlen($rc)) ? "&mc=" . urlencode($rc) : ""));
								} else {
                                    $arrUrl = explode("?", $_SERVER["REQUEST_URI"]);
                                    $arrQuery = explode("&", $arrUrl[1]);
                                    $strQuery = "";
                                    foreach($arrQuery AS $arrQuery_value) {
										if(strpos($arrQuery_value, $component->id) === false
											&& strpos($arrQuery_value, "frmAction") === false
										) {
											if(strlen($strQuery))
												$strQuery .= "&";

											$strQuery .= $arrQuery_value;
										}
                                    }

                                    if(strpos($_SERVER["REQUEST_URI"], "success") === false) {
                                        if(strpos($_SERVER["REQUEST_URI"], "?") === false) {
                                            $redirect = $_SERVER["REQUEST_URI"] . "?" . "success"; 
                                        } else {
											if(strlen($strQuery)) {
												$strQuery = "?" . $strQuery . "&" . "success";
											} else {
												$strQuery  = "?" . "success";
											}
											
                                            $redirect = $arrUrl[0] . $strQuery;
                                        }
                                    } else {
										if(strlen($strQuery))
											$strQuery = "?" . $strQuery;

                                        $redirect = $arrUrl[0] . $strQuery;
                                    }
									$component->redirect($redirect . (($component->user_vars["send_mail"] && strlen($rc)) ? "&mc=" . urlencode($rc) : ""));
								}
							}
						}
					}
	            }
			}
            break;
        case "update": 
        	if(isset($component->form_fields["privacy_check"]) && $component->form_fields["privacy_check"]->getValue() == 0) {
        		$component->tplDisplayError(ffTemplate::_get_word_by_code("privacy_check_error"));
        		return false;
			}

			$ID_node = $component->user_vars["ID_form_node"];

			$res = set_form_node($component, $uid, $ID_node);
			if(is_array($res) && count($res)) {
				$fields = $res["mail"]["fields"];
				$to = $res["mail"]["to"];
			}	

            if(AREA_SHOW_ECOMMERCE && $component->user_vars["enable_ecommerce"]) {
				$cart_item = resolve_ecommerce_detail($component, $ID_node);
				if(is_array($cart_item) && count($cart_item)) {
					foreach($cart_item AS $order_detail) {
						$sSQL = "UPDATE ecommerce_order_detail SET 
									discount = " . $db->toSql($order_detail["custom"]["discount"], "Number") . "
									, price = " . $db->toSql($order_detail["custom"]["price"], "Number") . "
									, weight = " . $db->toSql($order_detail["custom"]["weight"], "Number") . "
									, qta = " . $db->toSql($order_detail["quantity"], "Number") . "
									, qta_display = " . $db->toSql(($order_detail["custom"]["qta_display"] ? $order_detail["custom"]["qta_display"] : $order_detail["quantity"]), "Number") . "
								WHERE 
									tbl_src = " . $db->toSql($order_detail["custom"]["tbl_src"]) . "
									AND ID_items = " . $db->toSql($ID_node, "Number") . "
								";
						$db->execute($sSQL);
					}
				}
			}

            if($component->user_vars["send_mail"]) {
                if($component->user_vars["ID_email"]) {
                	if(check_function("process_mail"))
		                $rc = process_mail(
		                            $component->user_vars["ID_email"]
		                            , $to
		                            , NULL
		                            , NULL
		                            , $fields
		                            , NULL
		                            , NULL
		                            , NULL
									, ""
									, null
									, false
									, null
									, null
									, array()
									, ""
		                        );
                } else {
                    $rc = ffTemplate::_get_word_by_code("mail_undefined");
                }
            }
            if(!$_REQUEST["XHR_CTX_ID"]) {
				if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
			    	$component->json_result["url"] = $component->ret_url;
			    	
					$component->json_result["close"] = false;
					die(ffCommon_jsonenc($component->json_result, true));
				} else {
            		$component->redirect($component->ret_url);	
				}
			}
            
            break;
        
        case "confirmdelete":
            $ID_node = $component->user_vars["ID_form_node"];
            if($component->user_vars["enable_revision"]) {
	            $sSQL = "DELETE FROM 
	            			module_form_revision 
	                    WHERE module_form_revision.ID IN( SELECT module_form_rel_nodes_fields.ID_module_revision 
	                    									FROM module_form_rel_nodes_fields
	                    									WHERE module_form_rel_nodes_fields.ID_form_nodes = " . $db->toSql($ID_node, "Number") . "
	                    								)";
	            $db->execute($sSQL);
            }
            $sSQL = "DELETE FROM  
                        module_form_rel_nodes_fields 
                    WHERE module_form_rel_nodes_fields.ID_form_nodes = " . $db->toSql($ID_node, "Number");
            $db->execute($sSQL);
            $sSQL = "DELETE FROM  
                        module_form_nodes 
                    WHERE module_form_nodes.ID = " . $db->toSql($ID_node, "Number");
            $db->execute($sSQL);
            $sSQL = "DELETE FROM  
                        comment_rel_module_form 
                    WHERE comment_rel_module_form.ID_form_node = " . $db->toSql($ID_node, "Number");
            $db->execute($sSQL);
            if(!$_REQUEST["XHR_CTX_ID"]) {
				if($_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") {
			    	$component->json_result["url"] = $component->ret_url;
			    	
					$component->json_result["close"] = false;
					die(ffCommon_jsonenc($component->json_result, true));
				} else {
            		$component->redirect($component->ret_url);
				}
			}
            break;
    }
}

function set_form_node($component, $uid, $ID_node = 0, $check_mail = true) {
	$db = ffDB_Sql::factory();
	$to = array();
	$fields = array();
	$last_update = time();

	if(isset($component->form_fields["name"])) {
		$form_node_name = $component->form_fields["name"]->getValue();
	} else {
		$form_node_name = $last_update;
	}

	if(!$ID_node) {
		$sSQL = "INSERT INTO `module_form_nodes` 
			    (
			        `ID`
			      , `name`
			      , `ID_module`
			      , `ip_visitor`
			      , `uid`
		          , `hide`
		          , `ID_domain`
		          , `created`
		          , `owner`
		          , `visible`
			    )
			    VALUES 
			    (
			        " . (isset($component->user_vars["new_node"]) ? $component->user_vars["new_node"] : "null") . "
			        , " . $db->toSql($form_node_name, "Text") . "
			        , " . $db->toSql($component->key_fields["form-ID"]->default_value, "Number") . "
			        , " . $db->toSql($_SERVER['REMOTE_ADDR'], "Text") . "
			        , " . $db->toSql($uid, "Number") . "
		            , " . $db->toSql((isset($component->user_vars["hide_on_insert"]) ? $component->user_vars["hide_on_insert"] : 0), "Text") . "
		            , " . $db->toSql("0", "Number") . "
		            , " . $db->toSql($last_update, "Number") . "
		            , " . $db->toSql(get_session("UserNID"), "Number") . "
		            , " . $db->toSql("1", "Number") . "
			    )" ;
		$db->execute($sSQL);
		if($db->affectedRows()) {
			$ID_node = (isset($component->user_vars["new_node"]) 
                			? $component->user_vars["new_node"] 
                			: $db->getInsertID(true)
                		);
		}
	}

	if($ID_node > 0) {
		if($component->user_vars["enable_revision"]) {
			$sSQL = "INSERT INTO `module_form_revision` 
		    (
		        `ID`
		      , `created`
		      , `owner`
		      , `tag`
		      , `status`
		    )
		    VALUES 
		    (
		        null
		        , " . $db->toSql($last_update, "Number") . "
		        , " . $db->toSql(get_session("UserNID"), "Number") . "
		        , " . $db->toSql($component->form_fields["tag"]->value, "Text") . "
	            , " . $db->toSql($component->form_fields["status"]->value, "Number") . "
		    )" ;
			$db->query($sSQL);
			$ID_revision = $db->getInsertID(true);
	    } else {
			$ID_revision = 0;
	    }	

		foreach($component->form_fields as $form_key => $form_value) {
			if(strpos($form_key, "privacy") === false 
				&& strpos($form_key, "anonymous") === false
				&& strpos($form_key, "name") === false
				&& strpos($form_key, "tag") === false
				&& strpos($form_key, "status") === false
			) {
				if(is_numeric($form_key))
					$arrFieldOperation[$form_key] = "";
				
				if($component->user_vars["send_mail"]) {
				    if($component->form_fields[$form_key]->user_vars["enable_in_mail"]) {
				        if(!array_key_exists($component->id . "_" . $form_key, $_REQUEST))
							continue;
				        
				        if($component->form_fields[$form_key]->user_vars["is_image"] && is_file(DISK_UPDIR . $form_value->getValue())) {
							$fields[$component->form_fields[$form_key]->user_vars["group_field"]][$component->form_fields[$form_key]->user_vars["name"]] = cm_showfiles_get_abs_url(str_replace(DISK_UPDIR, "", $form_value->getValue()));
				        } else {
				            $fields[$component->form_fields[$form_key]->user_vars["group_field"]][$component->form_fields[$form_key]->user_vars["name"]] = str_replace("<input ", '<input readonly="readonly" ', $form_value->getValue());
						}
				    }
				    if($component->form_fields[$form_key]->user_vars["send_mail"]) {
			            if($check_mail) {
			                if(verifyMailbox($form_value->getValue())) {
				                $to[] = $form_value->getValue();
			                }
			            }
				    }
				}
			}
		}

		if(is_array($arrFieldOperation) && count($arrFieldOperation)) {
			$sSQL = "SELECT `module_form_rel_nodes_fields`.*
					FROM `module_form_rel_nodes_fields` 
					WHERE `module_form_rel_nodes_fields`.ID_form_nodes = " . $db->toSql($ID_node, "Number") . "
						AND `module_form_rel_nodes_fields`.`ID_form_fields` IN (" . implode(",", array_keys($arrFieldOperation)) . ")
						AND `ID_module_revision` = " . $db->toSql($ID_revision, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$arrFieldOperation[$db->getField("ID_form_fields", "Number", true)] = "UPDATE `module_form_rel_nodes_fields` SET 
								`value` = " . $db->toSql($component->form_fields[$db->getField("ID_form_fields", "Number", true)]->value) . "
							WHERE `module_form_rel_nodes_fields`.ID = " . $db->toSql($db->getField("ID"));
				} while($db->nextRecord());
			}
			
			foreach($arrFieldOperation AS $form_key => $operation) {
				if(strlen($operation)) {
					$db->execute($operation);
				} else {
					$sSQL = "INSERT INTO `module_form_rel_nodes_fields` 
						    (
						          `ID` 
						        , `ID_form_nodes` 
						        , `ID_form_fields`
						        , `value`
						        , `ID_module_revision`
						    )
						    VALUES 
						    (
						          NULL 
						        , " . $db->toSql($ID_node, "Number") . "
						        , ( SELECT
						                ID 
						            FROM 
						                module_form_fields 
						            WHERE 
						                module_form_fields.ID = " . $db->toSql($form_key, "Number") . "
						                AND module_form_fields.ID_module = " . $db->toSql($component->key_fields["form-ID"]->default_value, "Number") . "
						          )
						        , " . $db->toSql($component->form_fields[$form_key]->value) . "
						        , " . $db->toSql($ID_revision, "Number") . "
						    )";
					$db->execute($sSQL);	
				}	
			}
		}
	}
	
	if($check_mail) {
		return array("mail" => array(
								"to" => $to
								, "fields" => $fields
							)
				);
	} else {
		return $ID_node;
	}
}

function MD_form_clone($ID_form, $form_new_name = "") {
    $db = ffDB_Sql::factory();

    $addit_name = ffTemplate::_get_word_by_code("form_clone");
    $addit_smart_url = ffCommon_url_rewrite($addit_name);

    if(strlen($form_new_name)) {
    	$form_new_smart_url = ffCommon_url_rewrite($form_new_name);
	    $sSQL = "SELECT module_form.*
					, IF(module_form.display_name = ''
						, REPLACE(module_form.name, '-', ' ')
						, module_form.display_name
					) AS display_name
	            FROM module_form
	            WHERE module_form.name LIKE " . $db->toSql($form_new_smart_url . "%") . "
	            ORDER BY LENGTH(module_form.name) DESC";
	    $db->query($sSQL);
	    if($db->nextRecord()) {
			if(strpos($db->getField("name", "Text", true), "-" . $addit_smart_url) !== false) {
        		$tmpCountCloneSmartUrl = explode("-" . $addit_smart_url, ffCommon_url_rewrite($db->getField("name", "Text", true)));
        		$tmpCountCloneName = explode(" " . $addit_name, $db->getField("display_name", "Text", true));

                $countClone = $db->numRows();

                $form_new_smart_url = $tmpCountCloneSmartUrl[0] . "-" . $addit_smart_url . $countClone;
                $form_new_name = $tmpCountCloneName[0] . " " . $addit_name . $countClone;
			} else {
				$form_new_smart_url = $form_new_smart_url . "-" . $addit_smart_url;
				$form_new_name = $form_new_name . " " . $addit_name;
			}
		}    
	}
    
    $sSQL = "SELECT module_form.*
     			, IF(module_form.display_name = ''
					, REPLACE(module_form.name, '-', ' ')
					, module_form.display_name
				) AS display_name
            FROM module_form
            WHERE module_form.ID = " . $db->toSql($ID_form, "Number");
    $db->query($sSQL);
    if($db->nextRecord()) {
        $form_old_name = $db->getField("display_name", "Text", true);
        $form_old_smart_url = ffCommon_url_rewrite($db->getField("name", "Text", true));
        
        if($form_new_smart_url == $form_old_smart_url) {
            $form_new_smart_url = $form_new_smart_url . "-" . $addit_smart_url;
            $form_new_name = $form_new_name . " " . $addit_name;
        } elseif(strpos($form_old_smart_url, $form_new_smart_url . "-" . $addit_smart_url) === 0) {
            $countClone = str_replace($form_new_smart_url . "-" . $addit_smart_url, "", $form_old_smart_url);
            
            if(!$countClone)
                $countClone = 2;
            else
                $countClone = ((int) $countClone) + 1;

            $form_new_smart_url = $form_new_smart_url . "-" . $addit_smart_url . $countClone;
            $form_new_name = $form_new_name . " " . $addit_name . $countClone;
        }

        if(!strlen($form_new_smart_url)) {
            if(strpos($form_old_smart_url, "-" . $addit_smart_url) !== false) {
               $tmpCountCloneSmartUrl = explode("-" . $addit_smart_url, $form_old_smart_url);
               $tmpCountCloneName = explode(" " . $addit_name, $form_old_name);
                
                if(!$tmpCountCloneSmartUrl[1])
                    $countClone = 2;
                else
                    $countClone = ((int) $tmpCountCloneSmartUrl[1]) + 1;
                    
                $form_new_smart_url = $tmpCountCloneSmartUrl[0] . "-" . $addit_smart_url . $countClone;
                $form_new_name = $tmpCountCloneName[0] . " " . $addit_name . $countClone;
            } else {
                $form_new_smart_url = $form_old_smart_url . "-" . $addit_smart_url;
                $form_new_name = $form_old_name . " " . $addit_name;
            }
        }
        
        $sSQL = "INSERT INTO module_form
                (
                    `ID`
                    , `name`
                    , `display_name`
                    , `order`
                    , `force_redirect`
                    , `fixed_pre_content`
                    , `fixed_post_content`
                    , `privacy`
                    , `send_mail`
                    , `send_copy_to_guest`
                    , `force_from_with_domclass`
                    , `force_to_with_user`
                    , `ID_email`
                    , `report`
                    , `require_note`
                    , `tpl_form_path`
                    , `tpl_report_path`
                    , `limit_by_groups`
                    , `display_view_mode`
                    , `enable_ecommerce`
                    , `enable_ecommerce_weight`
                    , `enable_dynamic_cart`
                    , `enable_dynamic_cart_advanced`
                    , `skip_form_cart`
                    , `skip_shipping_calc`
                    , `discount_perc`
                    , `discount_val`
                    , `skip_vat_by_anagraph_type`
                    , `enable_sum_quantity`
                    , `reset_cart`
                    , `restore_default_by_cart`
                    , `fixed_cart_qta`
                    , `fixed_cart_price`
                    , `fixed_cart_vat`
                    , `fixed_cart_weight`
                    , `hide_vat`
                    , `hide_weight`
                    , `show_title`
                    , `group`
                    , `description`
                    , `visible`
                    , `enable_revision`
                    , `field_default_ID_form_fields_group`
                    , `field_default_ID_extended_type`
                    , `field_default_disable_select_one`
                    , `field_default_disable_free_input`
                    , `field_default_require`
                    , `field_default_hide_label`
                    , `field_default_placeholder`
                    , `field_default_ID_check_control`
                    , `field_default_unic_value`
                    , `field_default_send_mail`
                    , `field_default_enable_in_mail`
                    , `field_default_enable_in_grid`
                    , `field_default_enable_in_menu`
                    , `field_default_enable_in_document`
                    , `field_default_enable_tip`
                    , `field_default_writable`
                    , `field_default_hide`
                    , `field_default_preload_by_domclass`
                    , `field_default_fixed_pre_content`
                    , `field_default_fixed_post_content`
                    , `field_default_preload_by_db`
                    , `field_default_vgallery_field`
                    , `field_default_domclass`
                    , `field_default_custom`
                    , `public`
                    , `field_default_val_min`
                    , `field_default_val_max`
                    , `field_default_val_step`
                    , `field_default_show_price_in_label`
                    , `field_enable_dep`
                    , `field_enable_pricelist`
                    , `decumulation`
                )
                SELECT 
                    null
                    , " . $db->toSql($form_new_smart_url) . "
                    , " . $db->toSql($form_new_name) . "
                    , `order`
                    , `force_redirect`
                    , `fixed_pre_content`
                    , `fixed_post_content`
                    , `privacy`
                    , `send_mail`
                    , `send_copy_to_guest`
                    , `force_from_with_domclass`
                    , `force_to_with_user`
                    , `ID_email`
                    , `report`
                    , `require_note`
                    , `tpl_form_path`
                    , `tpl_report_path`
                    , `limit_by_groups`
                    , `display_view_mode`
                    , `enable_ecommerce`
                    , `enable_ecommerce_weight`
                    , `enable_dynamic_cart`
                    , `enable_dynamic_cart_advanced`
                    , `skip_form_cart`
                    , `skip_shipping_calc`
                    , `discount_perc`
                    , `discount_val`
                    , `skip_vat_by_anagraph_type`
                    , `enable_sum_quantity`
                    , `reset_cart`
                    , `restore_default_by_cart`
                    , `fixed_cart_qta`
                    , `fixed_cart_price`
                    , `fixed_cart_vat`
                    , `fixed_cart_weight`
                    , `hide_vat`
                    , `hide_weight`
                    , `show_title`
                    , `group`
                    , `description`
                    , `visible`
                    , `enable_revision`
                    , `field_default_ID_form_fields_group`
                    , `field_default_ID_extended_type`
                    , `field_default_disable_select_one`
                    , `field_default_disable_free_input`
                    , `field_default_require`
                    , `field_default_hide_label`
                    , `field_default_placeholder`
                    , `field_default_ID_check_control`
                    , `field_default_unic_value`
                    , `field_default_send_mail`
                    , `field_default_enable_in_mail`
                    , `field_default_enable_in_grid`
                    , `field_default_enable_in_menu`
                    , `field_default_enable_in_document`
                    , `field_default_enable_tip`
                    , `field_default_writable`
                    , `field_default_hide`
                    , `field_default_preload_by_domclass`
                    , `field_default_fixed_pre_content`
                    , `field_default_fixed_post_content`
                    , `field_default_preload_by_db`
                    , `field_default_vgallery_field`
                    , `field_default_domclass`
                    , `field_default_custom`
                    , `public`
                    , `field_default_val_min`
                    , `field_default_val_max`
                    , `field_default_val_step`
                    , `field_default_show_price_in_label` 
                    , `field_enable_dep`
                    , `field_enable_pricelist`
                    , `decumulation`
                FROM module_form
                WHERE module_form.ID = " . $db->toSql($ID_form, "Number");
        $db->execute($sSQL);
        $ID_new_form = $db->getInsertID(true);
        if($ID_new_form > 0) {
            $sSQL = "SELECT module_form_fields.*
                    FROM module_form_fields
                    WHERE module_form_fields.ID_module = " . $db->toSql($ID_form, "Number") . "
                    ORDER BY module_form_fields.ID";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $arrFormField[] = $db->getField("ID", "Number", true);
                } while($db->nextRecord());
            }
			
            $arrFormPricelist = MD_form_clone_pricelist($ID_form, $ID_new_form);
			
            if(is_array($arrFormField) && count($arrFormField)) {
            	$arrFormFieldSelectionValue = array();
                foreach($arrFormField AS $ID_form_field) {
                    $sSQL = "INSERT INTO module_form_fields
                            (
                                `ID`
                                , `ID_module`
                                , `name`
                                , `ID_extended_type`
                                , `ID_selection`
                                , `disable_select_one`
                                , `disable_free_input`
                                , `val_min`
                                , `val_max`
                                , `val_step`
                                , `ID_form_fields_group`
                                , `require`
                                , `ID_check_control`
                                , `unic_value`
                                , `send_mail`
                                , `enable_in_mail`
                                , `enable_in_grid`
                                , `enable_in_menu`
                                , `enable_in_document`
                                , `enable_tip`
                                , `writable`
                                , `hide`
                                , `order`
                                , `qta`
                                , `price`
                                , `vat`
                                , `weight`
                                , `custom`
                                , `preload_by_domclass`
                                , `ID_vgallery_field`
                                , `preload_by_db`
                                , `domclass`
                                , `fixed_pre_content`
                                , `fixed_post_content`
                                , `public`
                                , `type`
                                , `show_price_in_label`
                                , `sum_price_from`
                            )
                            SELECT 
                                null
                                , " . $db->toSql($ID_new_form, "Number") . "
                                , `name`
                                , `ID_extended_type`
                                , `ID_selection`
                                , `disable_select_one`
                                , `disable_free_input`
                                , `val_min`
                                , `val_max`
                                , `val_step`
                                , `ID_form_fields_group`
                                , `require`
                                , `ID_check_control`
                                , `unic_value`
                                , `send_mail`
                                , `enable_in_mail`
                                , `enable_in_grid`
                                , `enable_in_menu`
                                , `enable_in_document`
                                , `enable_tip`
                                , `writable`
                                , `hide`
                                , `order`
                                , `qta`
                                , `price`
                                , `vat`
                                , `weight`
                                , `custom`
                                , `preload_by_domclass`
                                , `ID_vgallery_field`
                                , `preload_by_db`
                                , `domclass`
                                , `fixed_pre_content`
                                , `fixed_post_content`
                                , `public`
                                , `type`
                                , `show_price_in_label`
                                , `sum_price_from`
                            FROM module_form_fields
                            WHERE module_form_fields.ID = " . $db->toSql($ID_form_field, "Number");
                    $db->execute($sSQL);
                    
                    $arrFormField[$ID_form_field] = $db->getInsertID(true);
                    
                    $arrFormFieldSelectionValue = array_replace($arrFormFieldSelectionValue, MD_form_clone_field_selection_value($ID_form_field, $arrFormField[$ID_form_field]));
					
                    MD_form_clone_pricelist_detail($arrFormPricelist, $ID_form_field, $arrFormField[$ID_form_field]);
		    		
		    		
                }
            }
			MD_form_clone_dep($ID_form, $ID_new_form, $arrFormField, $arrFormFieldSelectionValue);            			

            MD_form_clone_field_selection($arrFormField, $form_new_smart_url);
        }  
    }
    
    return array("ID" => $ID_new_form
    			, "name" => $form_new_smart_url
    			, "display_name" => $form_new_name);
}

function MD_form_clone_pricelist($ID_form, $ID_new_form) 
{
	$db = ffDB_Sql::factory();
	$sSQL = "SELECT module_form_pricelist.*
			FROM module_form_pricelist
			WHERE module_form_pricelist.ID_module = " . $db->toSql($ID_form, "Number") . "
				AND module_form_pricelist.ID";
	$db->query($sSQL);
	if($db->nextRecord())
	{
		do {
			$ID = $db->getField("ID", "Number", true);
			$arrFormPricelist[$ID] = 0;
		} while ($db->nextRecord());
	}
	
	if(is_array($arrFormPricelist) && count($arrFormPricelist))
	{
		foreach($arrFormPricelist AS $ID_old_pricelist => $value)
		{
			$sSQL = "INSERT INTO module_form_pricelist
					(
						`ID`
						, `ID_module`
						, `price`
						, `weight`
					)
					SELECT 
						null
						, " . $db->toSql($ID_new_form, "Number") . "
						, `price`
						, `weight`
					FROM module_form_pricelist
					WHERE module_form_pricelist.ID = " . $db->toSql($ID_old_pricelist, "Number");
			$db->execute($sSQL);
			$arrFormPricelist[$ID_old_pricelist] = $db->getInsertID(true);
		}
	}
	return $arrFormPricelist;
}

function MD_form_clone_pricelist_detail($arrFormPricelist, $ID_old_form_field, $ID_new_form_field)
{
	$db = ffDB_Sql::factory();
	if(is_array($arrFormPricelist) && count($arrFormPricelist))
	{
		foreach($arrFormPricelist AS $ID_old_pricelist => $ID_new_pricelist)
		{
			$sSQL = "INSERT INTO module_form_pricelist_detail
					(
						`ID`
						, `ID_form_pricelist`
						, `ID_form_fields`
						, `value`
					)
					SELECT 
						null
						, " . $db->toSql($ID_new_pricelist, "Number") . "
						, " . $db->toSql($ID_new_form_field, "Number") . "
						, `value`
					FROM module_form_pricelist_detail
					WHERE module_form_pricelist_detail.ID_form_fields = " . $db->toSql($ID_old_form_field, "Number") . "
						AND module_form_pricelist_detail.ID_form_pricelist = " . $db->toSql($ID_old_pricelist, "Number") . "
					ORDER BY module_form_pricelist_detail.ID";
			$db->execute($sSQL);
		}
	}
}

function MD_form_clone_dep($ID_form, $ID_new_form, $arrFormField, $arrFormFieldSelectionValue)
{
	$db = ffDB_Sql::factory();

	$sSQL = "SELECT module_form_dep.*
                FROM module_form_dep
                WHERE module_form_dep.ID_module = " . $db->toSql($ID_form, "Number") . "
                ORDER BY module_form_dep.ID"; 
    $db->query($sSQL);
    if($db->nextRecord())
    {
        do {
            $arrDep[$db->getField("ID", "Number", true)] = array(
                                                                "ID_form_fields" => $arrFormField[$db->getField("ID_form_fields", "Number", true)],
                                                                "ID_selection_value" =>  $arrFormFieldSelectionValue[$db->getField("ID_selection_value", "Number", true)],
                                                                "dep_fields" => $arrFormField[$db->getField("dep_fields", "Number", true)],
                                                                "dep_selection_value" => $arrFormFieldSelectionValue[$db->getField("dep_selection_value", "Number", true)]
                                                            );
        } while($db->nextRecord());
    }	

	if(is_array($arrDep) && count($arrDep))
    {
        foreach($arrDep AS $ID_dep => $dep_value)
        {
            $sSQL = "INSERT INTO module_form_dep
                            (
                                    `ID`
                                    , `ID_module`
                                    , `ID_form_fields`
                                    , `ID_selection_value`
                                    , `dep_fields`
                                    , `dep_selection_value`    
                                    , `operator`
                                    , `value`
                            )
                            SELECT 
                                    null
                                    , " . $db->toSql($ID_new_form, "Number") . "
                                    , " . $db->toSql($dep_value["ID_form_fields"], "Number") . "    
                                    , " . $db->toSql($dep_value["ID_selection_value"], "Number") . "
                                    , " . $db->toSql($dep_value["dep_fields"], "Number") . "
                                    , " . $db->toSql($dep_value["dep_selection_value"], "Number") . "
                                    , `operator`
                                    , `value`
                            FROM module_form_dep
                            WHERE module_form_dep.ID = " . $db->toSql($ID_dep, "Number");
            $db->execute($sSQL);
        }
    }
}
function MD_form_clone_field_selection($arrNewFormFieldsID, $smart_url = "")
{
	$db = ffDB_Sql::factory();

	$sSQL = "SELECT module_form_fields_selection_value.*
            FROM module_form_fields_selection_value
            WHERE module_form_fields_selection_value.ID_form_fields IN(" . $db->toSql(implode(",", $arrNewFormFieldsID), "Number", false) . ")
                AND module_form_fields_selection_value.`ID_selection` > 0
            ORDER BY module_form_fields_selection_value.ID";
    $db->query($sSQL);
    if($db->nextRecord()) {
        do {
            $arrFormFieldSelection[$db->getField("ID_selection", "Number", true)][] = $db->getField("ID", "Number", true);
        } while($db->nextRecord());
    }
    
    if(is_array($arrFormFieldSelection) && count($arrFormFieldSelection)) {
        foreach($arrFormFieldSelection AS $arrFormFieldSelection_key => $arrFormFieldSelection_value) {
            $sSQL = "INSERT INTO module_form_fields_selection
                    (
                        `ID`
                        , `name`
                        , `ID_vgallery_type`
                        , `ID_vgallery_fields` 
                    )
                    SELECT 
                        null
                        , CONCAT(`name`, '-', " . $db->toSql($smart_url) . ")
                        , `ID_vgallery_type`
                        , `ID_vgallery_fields` 
                    FROM module_form_fields_selection
                    WHERE module_form_fields_selection.`ID` = " . $db->toSql($arrFormFieldSelection_key, "Number");
            $db->execute($sSQL); 
            $ID_new_form_field_selection = $db->getInsertID(true);
            if($ID_new_form_field_selection > 0) {
                $sSQL = "UPDATE module_form_fields_selection_value SET
                            ID_selection = " . $db->toSql($ID_new_form_field_selection, "Number") . "
                        WHERE module_form_fields_selection_value.ID IN(" . $db->toSql($arrFormFieldSelection_value, "Text", false) . ")
                            AND module_form_fields_selection_value.ID_form_fields IN(SELECT module_form_fields.ID FROM module_form_fields.ID_module = " . $db->toSql($ID_new_form, "Number");
                $db->execute($sSQL);
            }
        }
    }	
}
function MD_form_clone_field_selection_value($ID_old_form_field, $ID_new_form_field)
{
	$db = ffDB_Sql::factory();
	$arrFormFieldSelectionValue = array();
	
	$sSQL = "SELECT module_form_fields_selection_value.*
	        FROM module_form_fields_selection_value
	        WHERE module_form_fields_selection_value.`ID_form_fields` = " . $db->toSql($ID_old_form_field, "Number") . "
	        ORDER BY module_form_fields_selection_value.ID";
	$db->query($sSQL);
	if($db->nextRecord()) {
		do {
			$arrFormFieldSelectionValue[$db->getField("ID", "Number", true)] = 0;
		} while($db->nextRecord());
	}	
	
	if(is_array($arrFormFieldSelectionValue) && count($arrFormFieldSelectionValue)) {
		foreach($arrFormFieldSelectionValue AS $ID_selection_value => $value) {
		    $sSQL = "INSERT INTO module_form_fields_selection_value
		        (
		            `ID`
		            , `name`
		            , `ID_selection`
		            , `order` 
		            , `enable_in_cart`
		            , `qta`    
		            , `price`
		            , `vat`
		            , `weight`
		            , `ID_form_fields`
		            , `sum_qta_from`
		            , `price_basic`
                    , `price_nostep`
		        )
		        SELECT 
		            null
		            , `name`
		            , `ID_selection`
		            , `order` 
		            , `enable_in_cart`
		            , `qta`    
		            , `price`
		            , `vat`
		            , `weight`
		            , " . $db->toSql($ID_new_form_field, "Number") . "    
		            , `sum_qta_from`
		            , `price_basic`
                    , `price_nostep`
		        FROM module_form_fields_selection_value
		        WHERE module_form_fields_selection_value.`ID` = " . $db->toSql($ID_selection_value, "Number");
		    $db->execute($sSQL); 
			$arrFormFieldSelectionValue[$ID_selection_value] = $db->getInsertID(true);
		}
	}

    return $arrFormFieldSelectionValue;
}





function resolve_ecommerce_detail($component, $ID_node) {
	$cart_item = array();
	if($component->user_vars["enable_ecommerce"] == "onegood")
	{
		//con il seo da problemi
		/*if($component->user_vars["form_title"]) {
			$cart_item[0]["custom"]["description"] = $component->user_vars["form_title"]; 
		} else {
			$cart_item[0]["custom"]["description"] = $component->user_vars["form_display_name"];
		}*/
		$cart_item[0]["custom"]["description"] = $component->user_vars["form_display_name"]; 

		$price_index = 1;
		$price_basic = 0;
		$cart_item[0]["quantity"] = 1;
		$cart_item[0]["custom"]["enable_sum_quantity"] = $component->user_vars["enable_sum_quantity"]; 
		$cart_item[0]["custom"]["skip_vat_by_anagraph_type"] = $component->user_vars["skip_vat_by_anagraph_type"];
		$cart_item[0]["custom"]["shipping_gratis"] = $component->user_vars["skip_shipping_calc"];

		foreach($component->form_fields AS $form_key => $form_value) {
			 switch($form_value->user_vars["type"]) 
		    {
		        case "multiplier":
		            if($component->form_fields[$form_key]->user_vars["disable_free_input"]) {
	                    $cart_item[0]["quantity"] = $cart_item[0]["quantity"] * $component->form_fields[$form_key]->user_vars["selection_price"][$form_value->getValue()]["qta"];
	                    $price_index = $price_index * $component->form_fields[$form_key]->user_vars["selection_price"][$form_value->getValue()]["price"];
					} else {
	                    $cart_item[0]["quantity"] = $form_value->getValue();
	                    if(is_array($component->form_fields[$form_key]->user_vars["selection_price"]) && count($component->form_fields[$form_key]->user_vars["selection_price"])) {
                        	foreach($component->form_fields[$form_key]->user_vars["selection_price"] AS $selection_key => $selection_value) {
                        		if($selection_key > $cart_item[0]["quantity"])
                        			break;

                        		$price_index = $selection_value["price"];
                        	}
	                    }
					}
		            break;
		        case "price":
		            $qta = 0;
		            $qta_step = 0;
		            $price = 0;
		            $weight = 0;
                    $price_nostep = 0;

					if(isset($component->form_fields[$form_key]->user_vars["selection_price"])) {
						$arrForm = explode(";", $form_value->getValue());
						if(is_array($arrForm) && count($arrForm)) {
							foreach($arrForm AS $arrForm_value) {
								$qta_step = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["qta"];
								if(is_array($component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["sum_qta_from"]) && count($component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["sum_qta_from"])) {
									$arrQfrom = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["sum_qta_from"];
									if(is_array($arrQfrom) && count($arrQfrom)) {
										foreach($arrQfrom AS $arrQFrom_value) {
											$qta = $qta + $component->form_fields[$arrQFrom_value]->getValue();
										}
									}
								}

								$price = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["price"];
								$price_basic = $price_basic + $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["price_basic"];
                                $price_nostep = $price_nostep + $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["price_nostep"];
								if(!$component->user_vars["hide_weight"])
									$weight = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["weight"];

							}
						}

						if(!$qta)
							$qta = 1;
						
						if($qta_step > 0) {
							$qta = ceil($qta / $qta_step);
						}
					} else {
						$qta = $form_value->getValue();
						$price = $component->form_fields[$form_key]->user_vars["price"];
						if(!$component->user_vars["hide_weight"])
							 $weight = $component->form_fields[$form_key]->user_vars["weight"];
					}
					
					if(is_array($form_value->user_vars["sum_price_from"]) && count($form_value->user_vars["sum_price_from"])) {
						foreach($form_value->user_vars["sum_price_from"] AS $sum_price_from_value) {
							if(isset($component->form_fields[$sum_price_from_value]->user_vars["selection_price"])) {
								$arrForm = explode(";", $component->form_fields[$sum_price_from_value]->getValue());
								if(is_array($arrForm) && count($arrForm)) {
									foreach($arrForm AS $arrForm_value) {
										$price = $price + $component->form_fields[$sum_price_from_value]->user_vars["selection_price"][$arrForm_value]["price"];
										$price_basic = $price_basic + $component->form_fields[$sum_price_from_value]->user_vars["selection_price"][$arrForm_value]["price_basic"];
                                        $price_nostep = $price_nostep + $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["price_nostep"];
										//if(!$component->user_vars["skip_shipping_calc"] && !$component->user_vars["hide_weight"])
										//	$weight = $weight + $component->form_fields[$sum_price_from_value]->user_vars["selection_price"][$arrForm_value]["weight"];
									}
								}
							} else {
								$price = $price + $component->form_fields[$sum_price_from_value]->user_vars["price"];
								//if(!$component->user_vars["skip_shipping_calc"] && !$component->user_vars["hide_weight"])
								//	 $weight = $weight + $component->form_fields[$sum_price_from_value]->user_vars["weight"];
							}
						}
					}
//echo("price: " . $price . " qta: " . $qta . " total: " . (($price * $qta)) . "\n");

					$cart_item[0]["custom"]["price"] = $cart_item[0]["custom"]["price"] + ($price * $qta) + $price_nostep;
					if(!$component->user_vars["hide_weight"])
						$cart_item[0]["custom"]["weight"] = $cart_item[0]["custom"]["weight"] + ($weight);
		        default:
			}
			
		}
//echo("------------------------\n");
//echo($cart_item[0]["custom"]["price"] . "  " . $cart_item[0]["quantity"]  . "  " . $price_index . "  " . $price_basic . " total: " . (($cart_item[0]["custom"]["price"] + (($price_basic + $component->user_vars["fixed_cart"]["price"]) / $cart_item[0]["quantity"] )) * $price_index));
		if(is_array($component->user_vars["pricelist"]) && count($component->user_vars["pricelist"])) {
            $arrPricelistCompare = array();
            $arrPricelistCompareAlt = array();
            foreach($component->form_fields AS $form_key => $form_value) {
                if($form_value->user_vars["type"] == "pricelist") {
                    $arrPricelistCompare[] = $form_key . "=" . $form_value->getValue();
                    if(strlen($form_value->getValue()))
                        $arrPricelistCompareAlt[] = $form_key . "=" . $form_value->getValue();
                }
            }

            $pricelist_rule = null;
            $str_pricelist_compare = implode(":", $arrPricelistCompare);
            $str_pricelist_compare_alt = implode(":", $arrPricelistCompareAlt);
            
            if(strlen($str_pricelist_compare) && array_key_exists($str_pricelist_compare, $component->user_vars["pricelist"]))
                $pricelist_rule = $component->user_vars["pricelist"][$str_pricelist_compare];
            elseif(strlen($str_pricelist_compare_alt) && array_key_exists($str_pricelist_compare_alt, $component->user_vars["pricelist"]))
                $pricelist_rule = $component->user_vars["pricelist"][$str_pricelist_compare_alt];

            if(is_array($pricelist_rule) && count($pricelist_rule)) {
                $cart_item[0]["custom"]["price"] =  $cart_item[0]["custom"]["price"] + $pricelist_rule["price"];
                //if(!$component->user_vars["skip_shipping_calc"])
                    $cart_item[0]["custom"]["weight"] = $cart_item[0]["custom"]["weight"] + $pricelist_rule["weight"];
            }                
        }

		$cart_item[0]["custom"]["qta_display"] = $pricelist_rule["qta"];
		$cart_item[0]["custom"]["vat"] = $component->user_vars["fixed_cart"]["vat"];
		$cart_item[0]["custom"]["price"] = (($cart_item[0]["custom"]["price"] + (($price_basic + $component->user_vars["fixed_cart"]["price"]) / $cart_item[0]["quantity"] )) * $price_index);
        //$cart_item[0]["custom"]["price"] = (($cart_item[0]["custom"]["price"] + $price_basic + $component->user_vars["fixed_cart"]["price"]) * $price_index);
		$cart_item[0]["custom"]["weight"] = $cart_item[0]["custom"]["weight"] + $component->user_vars["fixed_cart"]["weight"];
	   
		$cart_item[0]["custom"]["decumulation"] = $component->user_vars["fixed_cart"]["decumulation"];

		if($component->user_vars["discount"]["perc"]) {
			$cart_item[0]["custom"]["discount"] = $component->user_vars["discount"]["perc"];
		}
		if($component->user_vars["discount"]["val"]) {
			$cart_item[0]["custom"]["price"] = $cart_item[0]["custom"]["price"] - $component->user_vars["discount"]["val"];
			if($cart_item[0]["custom"]["price"] < 0)
				$cart_item[0]["custom"]["price"] = 0;
		}
		
		if($component->user_vars["restore_default_by_cart"]) {
			$cart_item[0]["custom"]["ID_items"] = $ID_node;
			$cart_item[0]["custom"]["source_url"] = $component->user_vars["MD_chk"]["ret_url"];
		}
		//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
	}
	elseif(strlen($component->user_vars["enable_ecommerce"])) 
	{
		foreach($component->form_fields AS $form_key => $form_value) {
			if(isset($component->form_fields[$form_key]->user_vars["selection_price"])) {
				$arrForm = explode(";", $form_value->getValue());
				if(is_array($arrForm) && count($arrForm)) {
					foreach($arrForm AS $arrForm_value) {
						if(isset($component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value])) {
							$field_desc = ffTemplate::_get_word_by_code("cart_" . strtolower(preg_replace('/[^a-zA-Z0-9\_]/', '', str_replace(" ", "_", substr(strip_tags($arrForm_value), 0, 20))))); 
								
							$cart_item[$arrForm_value]["quantity"] = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["qta"];
							$cart_item[$arrForm_value]["custom"]["enable_sum_quantity"] = $component->user_vars["enable_sum_quantity"];
							$cart_item[$arrForm_value]["custom"]["skip_vat_by_anagraph_type"] = $component->user_vars["skip_vat_by_anagraph_type"];

			                $cart_item[$arrForm_value]["custom"]["price"] = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["price"];

							if($component->user_vars["discount"]["perc"]) {
								$cart_item[$arrForm_value]["custom"]["discount"] = $component->user_vars["discount"]["perc"];
							}
							if($component->user_vars["discount"]["val"]) {
								$cart_item[$arrForm_value]["custom"]["price"] = $cart_item[$arrForm_value]["custom"]["price"] - $component->user_vars["discount"]["val"];
								if($cart_item[$arrForm_value]["custom"]["price"] < 0)
									$cart_item[$arrForm_value]["custom"]["price"] = 0;
							}

			                if($component->user_vars["hide_vat"])
			                    $cart_item[$arrForm_value]["custom"]["vat"] = $component->user_vars["fixed_cart"]["vat"];
			                else
								$cart_item[$arrForm_value]["custom"]["vat"] = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["vat"];
							
				            if($component->user_vars["hide_weight"])
				                $cart_item[$arrForm_value]["custom"]["weight"] = $component->user_vars["fixed_cart"]["weight"];
				            else
				                $cart_item[$arrForm_value]["custom"]["weight"] = $component->form_fields[$form_key]->user_vars["selection_price"][$arrForm_value]["weight"];
			                
			                $cart_item[$arrForm_value]["custom"]["decumulation"] = $component->user_vars["fixed_cart"]["decumulation"];
							$cart_item[$arrForm_value]["custom"]["description"] = $field_desc;

							if($component->user_vars["restore_default_by_cart"]) {
								$cart_item[$arrForm_value]["custom"]["ID_items"] = $ID_node;
								$cart_item[$arrForm_value]["custom"]["source_url"] = $component->user_vars["MD_chk"]["ret_url"];
							}
						}
					}
				}
			} else {
				if(isset($component->form_fields[$form_key]->user_vars["price"]) 
					&& $form_value->getValue()
				) {
					if($component->form_fields[$form_key]->label)
						$field_key = $component->form_fields[$form_key]->user_vars["label"];
					else
						$field_key = $form_value->getValue();

			        
			        $cart_item[$field_key]["quantity"] = $form_value->getValue() > 0 ? $form_value->getValue() : $component->form_fields[$form_key]->user_vars["qta"];
					if(!($cart_item[$field_key]["quantity"] > 0))
			            $cart_item[$field_key]["quantity"] = 1;
			        
			        $cart_item[$field_key]["custom"]["enable_sum_quantity"] = $component->user_vars["enable_sum_quantity"];
			        $cart_item[$field_key]["custom"]["skip_vat_by_anagraph_type"] = $component->user_vars["skip_vat_by_anagraph_type"];
					$cart_item[$field_key]["custom"]["price"] = $component->form_fields[$form_key]->user_vars["price"];
					
					if($component->user_vars["discount"]["perc"]) {
						$cart_item[$field_key]["custom"]["discount"] = $component->user_vars["discount"]["perc"];
					}
					if($component->user_vars["discount"]["val"]) {
						$cart_item[$field_key]["custom"]["price"] = $cart_item[$field_key]["custom"]["price"] - $component->user_vars["discount"]["val"];
						if($cart_item[$field_key]["custom"]["price"] < 0)
							$cart_item[$field_key]["custom"]["price"] = 0;
					}
					
			        if($component->user_vars["hide_vat"])
						$cart_item[$field_key]["custom"]["vat"] = $component->user_vars["fixed_cart"]["vat"];
			        else
			            $cart_item[$field_key]["custom"]["vat"] = $component->form_fields[$form_key]->user_vars["vat"];
					
				    if($component->user_vars["hide_weight"])
				        $cart_item[$field_key]["custom"]["weight"] = $component->user_vars["fixed_cart"]["weight"];
				    else
				        $cart_item[$field_key]["custom"]["weight"] = $component->form_fields[$form_key]->user_vars["weight"];
			        
			        $cart_item[$field_key]["custom"]["decumulation"] = $component->user_vars["fixed_cart"]["decumulation"];
					$cart_item[$field_key]["custom"]["description"] = ffTemplate::_get_word_by_code("cart_" . strtolower(preg_replace('/[^a-zA-Z0-9\_]/', '', str_replace(" ", "_", substr(strip_tags($field_key), 0, 20))))); 

					if($component->user_vars["restore_default_by_cart"]) {
						$cart_item[$field_key]["custom"]["ID_items"] = $ID_node;
						$cart_item[$field_key]["custom"]["source_url"] = $component->user_vars["MD_chk"]["ret_url"];
					}
				}
			}
		}

		if($component->user_vars["enable_ecommerce"] != "onegood" 
			&& is_array($component->user_vars["fixed_cart"]) && count($component->user_vars["fixed_cart"]) 
			&& $component->user_vars["fixed_cart"]["price"] > 0
		) {
			$cart_item[] = array("quantity" => $component->user_vars["fixed_cart"]["qta"]
								, "custom" => array(
			                        "enable_sum_quantity" => $component->user_vars["enable_sum_quantity"]
			                        , "skip_vat_by_anagraph_type" => $component->user_vars["skip_vat_by_anagraph_type"]
									, "price" => $component->user_vars["fixed_cart"]["price"]
									, "vat" => $component->user_vars["fixed_cart"]["vat"]
									, "decumulation" => $component->user_vars["fixed_cart"]["decumulation"]
									, "description" => ($component->user_vars["form_title"]
															? $component->user_vars["form_title"]	
															: ffTemplate::_get_word_by_code($component->user_vars["form_name"] . "_fixed_cart")
														)
									, "weight" => $component->user_vars["fixed_cart"]["weight"]
									, "tbl_src" => "form" . ($component->user_vars["restore_default_by_cart"] ? "-" . $component->key_fields["ID"]->getValue() : "")
									, "ID_items" => ($component->user_vars["restore_default_by_cart"] ? $ID_node : 0)
									, "source_url" => ($component->user_vars["restore_default_by_cart"] ? $component->user_vars["MD_chk"]["ret_url"] : "")
								)
							);
		}	
	}

	return $cart_item;
}