<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_THEME_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
	$is_valid = false;
	
	//DA STRUTTURARE BENE PER MODIFICARE I TEMPLATE HTML DI BASE della layout
	if(!isset($_REQUEST["path"])) {
		if(isset($_REQUEST["prototype_path"]) && strlen($_REQUEST["prototype_path"])) {
			$actual_path = $_REQUEST["prototype_path"];
			if(is_array($_REQUEST) && count($_REQUEST)) {
				foreach($_REQUEST AS $key => $value) {
					$actual_path = str_replace("[" . $key . "]", $value, $actual_path);
				}
			}
		}
	} else {
		$actual_path = $_REQUEST["path"];	
	}
	
	
	$extype = $_REQUEST["extype"];
	if(isset($_REQUEST["writable"]))
		$writable = $_REQUEST["writable"];
	else
		$writable = true;
		
	if(isset($_REQUEST["deletable"]))
		$deletable = $_REQUEST["deletable"];
	else
		$deletable = true;
		
	if(strlen($actual_path)) {
		if (
			substr(strtolower($actual_path), 0, 7) == "http://"
			|| substr(strtolower($actual_path), 0, 8) == "https://"
		) {
			$content = file_get_contents($actual_path);

			if(!isset($_REQUEST["keys"]["nameID"])) {
				$_REQUEST["keys"]["nameID"] = basename($actual_path);
			}
		} else {
			if(is_file(FF_DISK_PATH . $actual_path)) {
				if(!is_binary(FF_DISK_PATH . $actual_path))
					$content = file_get_contents(FF_DISK_PATH . $actual_path);
				else
					$content = null;

				if(!isset($_REQUEST["keys"]["nameID"])) {
					$_REQUEST["keys"]["nameID"] = basename($actual_path);
				}
			} elseif(is_dir(FF_DISK_PATH . $actual_path)) {
				$content = "";
				if(!isset($_REQUEST["keys"]["nameID"])) {
					$_REQUEST["keys"]["nameID"] = basename($actual_path);
				}
			} 
		}

	    $file_name = ffGetFilename($actual_path);
	    $file_ext = ffGetFilename($actual_path, false);
        $is_valid = true;
	}
	
	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "ThemeModify";
	$oRecord->resources[] = $oRecord->id;
	$oRecord->title = ffTemplate::_get_word_by_code("html_modify_title");
	$oRecord->src_table = "";
	$oRecord->skip_action = true;

	$oRecord->buttons_options["delete"]["display"] = AREA_HTML_SHOW_DELETE;
	$oRecord->addEvent("on_done_action", "HtmlModify_on_done_action");
	$oRecord->addEvent("on_process_template", "HtmlModify_on_process_template");

	$oRecord->user_vars["parent"] = ffCommon_dirname($actual_path);
	$oRecord->user_vars["is_valid"] = $is_valid;
	if($writable) {
		if(isset($_REQUEST["keys"]["nameID"]) && $is_valid) {
			$oRecord->buttons_options["insert"]["display"] = false;
			$oRecord->buttons_options["update"]["display"] = true;
			
			/*if(!$_REQUEST["XHR_DIALOG_ID"]) {
				$oRecord->buttons_options["update"]["label"] = ffTemplate::_get_word_by_code("html_modify_updateback");
				$oButton = ffButton::factory($cm->oPage);
				$oButton->id = "updatenext"; 
				$oButton->label = ffTemplate::_get_word_by_code("html_modify_updatenext");
				$oButton->action_type = "submit";
				$oButton->frmAction = "updatenext";
                                $oButton->aspect = "link";
				$oRecord->addActionButton($oButton, 2);
				
				$oRecord->default_actions[$oButton->id] = "update";
			}  */
		} else {
			$oRecord->buttons_options["insert"]["display"] = true;
			$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("html_modify_insertback");
			$oRecord->buttons_options["update"]["display"] = false;

			$oButton = ffButton::factory($cm->oPage);
			$oButton->id = "insertnext"; 
			$oButton->label = ffTemplate::_get_word_by_code("html_modify_insertnext");
			$oButton->action_type = "submit";
			$oButton->frmAction = "insertnext";
                        $oButton->aspect = "link";
			$oRecord->addActionButton($oButton, 2);
		}
		$oRecord->buttons_options["delete"]["display"] = $deletable;
	} else {
		$oRecord->buttons_options["insert"]["display"] = false;
		$oRecord->buttons_options["update"]["display"] = false;
		$oRecord->buttons_options["delete"]["display"] = false;
	}
	$oField = ffField::factory($cm->oPage);
	$oField->id = "nameID";
	$oRecord->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("html_modify_name");
	$oField->store_in_db = false;
	$oField->default_value = new ffData($file_name);
	if($writable && $extype == "files")
		$oField->required = true;
	else
		$oField->control_type = "label";
	$oRecord->addContent($oField);
	
	if(substr(strtolower($actual_path), 0, 7) == "http://" || substr(strtolower($actual_path), 0, 8) == "https://" || substr($actual_path, 0, 2) == "//" || is_file(FF_DISK_PATH . $actual_path)) {
		$oField = ffField::factory($cm->oPage);
		$oField->id = "type";
		$oField->label = ffTemplate::_get_word_by_code("html_modify_type");
		$oField->extended_type = "Selection";
		
		if($content !== null && $extype == "files" && $writable) {
			$oField->multi_pairs = array (
			                            array(new ffData("html"), new ffData("Html"))
                            			, array(new ffData("xml"), new ffData("Xml")) 
                            			, array(new ffData("css"), new ffData("Css")) 
                            			, array(new ffData("js"), new ffData("Js")) 
			                       );
			$oField->required = true;
		} else {
			$oField->multi_pairs = array (
                            			array(new ffData($file_ext), new ffData(ucfirst($file_ext))) 
			                       );
			$oField->control_type = "label";
		}
		$oField->store_in_db = false;
		$oField->multi_select_one = false;
		$oField->default_value = new ffData($file_ext);
		$oRecord->addContent($oField);

		if($content !== null) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "content";
			$oField->label = ffTemplate::_get_word_by_code("html_modify_content");
			$oField->display_label = false;
			$oField->store_in_db = false;
			$oField->extended_type = "Text";
			$oField->encode_entities = false;
            $oField->widget = "editarea";
			$oField->editarea_syntax = $file_ext;
			$oField->editarea_writable = $writable;
			$oField->required = true;
			$oField->default_value = new ffData($content);
            if(check_function("set_field_textarea")) { 
                $oField = set_field_textarea($oField);
            }
			$oRecord->addContent($oField);
		}
	}
	$cm->oPage->addContent($oRecord);   
} else {
	$cm->oPage->addContent(ffTemplate::_get_word_by_code("ftp_not_configutated"));
}  

function HtmlModify_on_process_template($component, $tpl) {
	if(isset($_REQUEST["keys"]["nameID"]) && !$component->user_vars["is_valid"]) {
		if (isset($_REQUEST["XHR_DIALOG_ID"]))
			$component->dialog(false, "okonly", $component->parent[0]->title, ffTemplate::_get_word_by_code("record_not_found"), "", "[CLOSEDIALOG]");
		else
			$component->dialog(false, "okonly", $component->parent[0]->title, ffTemplate::_get_word_by_code("record_not_found"), "", $component->parent[0]->ret_url);
	}	
}
  
function HtmlModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
	$real_file_old = $component->user_vars["parent"] . "/" . $component->form_fields["name"]->default_value->getValue() . (isset($component->form_fields["type"]) ? "." . $component->form_fields["type"]->default_value->getValue() : "");
    $real_file = $component->user_vars["parent"] . "/" . $component->form_fields["name"]->getValue() . (isset($component->form_fields["type"]) ? "." . $component->form_fields["type"]->getValue() : "");

    if(strlen($action)) {
		if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
		    // set up basic connection
            /*$conn_id = @ftp_connect(DOMAIN_INSET);
            if($conn_id === false && strpos(DOMAIN_INSET, "www.") === false) {
                $conn_id = @ftp_connect("www." . DOMAIN_INSET);
            }*/
			$conn_id = @ftp_connect("localhost");
	        if($conn_id === false)
        		$conn_id = @ftp_connect("127.0.0.1");
			if($conn_id === false)
        		$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

		    if($conn_id !== false) {
		        // login with username and password
		        if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) {
		            $local_path = FF_DISK_PATH;
		            $part_path = "";
		            $real_ftp_path = NULL;
		            
		            foreach(explode("/", $local_path) AS $curr_path) {
		                if(strlen($curr_path)) {
		                    $ftp_path = str_replace($part_path, "", $local_path);
		                    if(@ftp_chdir($conn_id, $ftp_path)) {
		                        $real_ftp_path = $ftp_path;
		                        break;
		                    } 

		                    $part_path .= "/" . $curr_path;
		                }
		            }
		            if($real_ftp_path === NULL && defined("FTP_PATH") && strlen(FTP_PATH)) {
		                if(@ftp_chdir($conn_id, FTP_PATH)) {
		                    $real_ftp_path = FTP_PATH;
		                } 
		            }
		            if($real_ftp_path !== NULL) {
		            	if($action == "confirmdelete") {
                            if($real_file && ffCommon_dirname($real_file) != $real_file) {
                                if(check_function("fs_operation") && !ftp_purge_dir($conn_id, $real_ftp_path, $real_file, FF_DISK_PATH)) {
                                    $strError = ffTemplate::_get_word_by_code("ftp_unavailable_delete");
                                }
                            }
/*		            		if(is_file(FF_DISK_PATH . $real_file)) {
								if (@ftp_delete($conn_id, $real_ftp_path . $real_file) === false) {
									$strError = ffTemplate::_get_word_by_code("ftp_unavailable_delete_file");
								}
							} elseif(is_dir(FF_DISK_PATH . $real_file)) {
								if (@ftp_rmdir($conn_id, $real_ftp_path . $real_file) === false) {
									$strError = ffTemplate::_get_word_by_code("ftp_unavailable_delete_dir");
								}
							}
*/                            
						} else {
							if($action == "update" || $action == "updatenext") {
								if($real_file_old != $real_file) {
									if(@ftp_rename($conn_id, $real_ftp_path . $real_file_old, $real_ftp_path . $real_file) === false) {
										$strError = ffTemplate::_get_word_by_code("ftp_unavailable_rename");		
									}
								}
							}

				            if (!$strError && isset($component->form_fields["content"])) {
		        				switch($action) {
									case "insert":
									case "insertnext":
									case "update":
									case "updatenext":
										$handle = @tmpfile();
										@fwrite($handle, $component->form_fields["content"]->getValue());
										@fseek($handle, 0);
										if(!@ftp_fput($conn_id, $real_ftp_path . $real_file, $handle, FTP_ASCII)) {
											$strError = ffTemplate::_get_word_by_code("unable_write_file");
										} else {
											if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $real_file) === false) {
												if(@chmod(FF_DISK_PATH . $real_file, 0644) === false) {
													$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
												}
											}
										}
										@fclose($handle);
									    break;

									default:
								}
								$file_chmod = "644";
								if(substr(decoct( @fileperms(FF_DISK_PATH . $real_file)), 3) != $file_chmod) {
									$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
							        if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $real_file) === false) {
						            	if(@chmod(FF_DISK_PATH . $real_file, $file_chmod) === false) {
				            				$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
										}
									}
								}
							}
						}
					} else {
						$strError = ffTemplate::_get_word_by_code("ftp_unavailable_root_dir");
					}		            
		        } else {
		            $strError = ffTemplate::_get_word_by_code("ftp_access_denied");
		        }
		    } else {
		        $strError = ffTemplate::_get_word_by_code("ftp_connection_failure");
		    }
		    // close the connection and the file handler
		    @ftp_close($conn_id);
		} else {
		    $strError = ffTemplate::_get_word_by_code("ftp_not_configutated");
		}

		if($strError) {
			$component->tplDisplayError($strError);
			return true;
		} else {
			$sSQL = "UPDATE 
			            `layout` 
			        SET 
			            `layout`.`last_update` = " . $db->toSql(time()) . " 
			        ";
			$db->execute($sSQL); 
			
			switch($action) {
				case "insertnext":
				case "updatenext":
					$component->redirect($_SERVER["REQUEST_URI"]);
				default:
			}
		}
    }
}

?>
