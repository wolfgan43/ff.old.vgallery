<?php
if (!AREA_THEME_SHOW_ADDNEW) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}  


if(strpos($_GET["path"], FF_THEME_DIR) === 0)
	$base_path = FF_THEME_DIR;
if(strpos($_GET["path"], SITE_UPDIR) === 0)
	$base_path = SITE_UPDIR;

if(!strlen($base_path) || (isset($_REQUEST["source"]) && strlen($_REQUEST["source"])))
	$base_path = FF_THEME_DIR;	

$arrBasePath = array("css" => "css"
					, "javascript" => "js"
				);	

$extype = $_REQUEST["extype"];
				
if(isset($_REQUEST["basepath"]))
    $base_path = $base_path . $_REQUEST["basepath"];

$path = urldecode($_REQUEST["path"]);
$tmp_path = $_GET["path"]; 
$arrPath = array();

if(strlen($base_path) && strpos($path, $base_path) === 0) {
    $path = substr($path, strlen($base_path));
    $tmp_path = substr($tmp_path, strlen($base_path));
}
if(isset($_REQUEST["source"])) {
	$source = $_REQUEST["source"];
} else {
	$source	= null;
}

   

do {
	if(strlen($base_path) && $source !== null && !strlen($source)) {
		if($tmp_path != "/") {
			$check_file_prefix = str_replace("/", "_", trim($tmp_path, "/")) . "." . $arrBasePath[basename($base_path)];
		} else {
			$check_file_prefix = "root." . $arrBasePath[basename($base_path)];
		}
		if(file_exists(FF_DISK_PATH . $base_path . "/" . $check_file_prefix))
			continue;
	}

    $arrPath[] = array(new ffData($tmp_path), new ffData($tmp_path));
} while($tmp_path != "/" && $tmp_path = ffCommon_dirname($tmp_path));

$type = basename($cm->real_path_info);
$ret_url = $_REQUEST["ret_url"];

if($_REQUEST["frmAction"] == "adddir") {
    $directory = $_REQUEST["directory"];
    
    if(strlen($directory)) {
		if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
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
                        if(!is_dir(FF_DISK_PATH . $base_path . $path . "/" . $directory)) {
                            if(!@ftp_mkdir($conn_id, $real_ftp_path . $base_path . $path . "/" . $directory)) {
                                $strError .= ffTemplate::_get_word_by_code("creation_failure_directory") . " (" . $real_ftp_path . $base_path . $path . "/" . $directory . ")" . "<br>";
							} else {
								if(@ftp_chmod($conn_id, 0775, $real_ftp_path . $base_path . $path . "/" . $directory) === false) {
									@chmod(FF_DISK_PATH . $base_path . $path . "/" . $directory, 0775);
								}
								ffRedirect($ret_url);
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
    }
} 




if($type == "dir") {
	$tpl = ffTemplate::factory(get_template_cascading("/", "addresources.html"));
	$tpl->load_file("addresources.html", "main");


	$oField = ffField::factory($cm->oPage);
	$oField->id = "path";
	$oField->label = ffTemplate::_get_word_by_code("gallery_add_parent");
	$oField->base_type = "Text";
	$oField->extended_type = "Selection";
	$oField->multi_pairs = $arrPath;
	$oField->value = new ffData($path, "Text");
	if($source === null)
		$oField->properties["onchange"] = "window.location.href='" . $cm->oPage->site_path . $cm->oPage->page_path . $cm->real_path_info . "?path='+this.value+'&ret_url=" . urlencode($ret_url) . "'";

	$oField->required = true;
	$oField->parent_page = array(&$cm->oPage);

	$tpl->set_var("parent_label", ffTemplate::_get_word_by_code("gallery_add_parent"));
	$tpl->set_var("parent", $oField->process());
	$tpl->set_var("SezUpload", "");
	
	if($source !== null) {
		$tpl->set_var("SezExecute", "");
	} else {
	    $tpl->set_var("title", ffTemplate::_get_word_by_code("gallery_add_dir_title"));
	    $tpl->set_var("description", ffTemplate::_get_word_by_code("gallery_add_dir_description"));
	    
	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "directory";
	    $oField->label = ffTemplate::_get_word_by_code("gallery_add_directory");
	    $oField->base_type = "Text";
	    $oField->parent_page = array(&$cm->oPage);

	    $tpl->set_var("name_label", ffTemplate::_get_word_by_code("gallery_add_directory"));
	    $tpl->set_var("name", $oField->process());
	    
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "adddir"; 
	    $oButton->label = ffTemplate::_get_word_by_code("add_dir");
	    $oButton->action_type = "submit";
	    $oButton->frmAction = "adddir";
	    $oButton->form_method = "post";
            $oButton->aspect = "link";
	    $oButton->parent_page = array(&$cm->oPage);
	    
	    $tpl->set_var("insert", $oButton->process());
	    
		$oButton = ffButton::factory($cm->oPage);
		$oButton->id = "back"; 
		$oButton->label = ffTemplate::_get_word_by_code("back");
		$oButton->action_type = "gotourl";
		$oButton->url = $_REQUEST["ret_url"]; 
                $oButton->aspect = "link";
		$oButton->parent_page = array(&$cm->oPage);
		
	    $tpl->set_var("back", $oButton->process());
	    
	    $tpl->parse("SezExecute", false);
	}
    $cm->oPage->addContent($tpl->rpparse("main", false));
} else {
	/*if($source !== null) {
		$tpl->set_var("SezUpload", "");
		$tpl->set_var("SezExecute", "");
	} else {
	    setJsRequest("uploadify");


	    $tpl->set_var("max_upload", MAX_UPLOAD);
	    $tpl->set_var("path_upload", $path);
	    $tpl->set_var("type_upload", $base_path);
	    
	    $tpl->parse("SezUpload", false);
	    
	    $tpl->set_var("title", ffTemplate::_get_word_by_code("gallery_add_item_title"));
	    $tpl->set_var("description", ffTemplate::_get_word_by_code("gallery_add_item_description"));

	    $oField = ffField::factory($cm->oPage);
	    $oField->id = "item";
//	    $oField->class = "uploadify";
	    $oField->label = ffTemplate::_get_word_by_code("gallery_add_item");
	    $oField->base_type = "Text";
	    $oField->extended_type = "File";
	    $oField->control_type = "file";
	    $oField->file_max_size = MAX_UPLOAD;
	    //$oField->file_storing_path = DISK_UPDIR . $_REQUEST["parent"];
	    //$oField->file_temp_path = DISK_UPDIR . $_REQUEST["parent"];
	    $oField->parent_page = array(&$cm->oPage);

	    $tpl->set_var("name_label", ffTemplate::_get_word_by_code("gallery_add_item"));
	    $tpl->set_var("name", $oField->process());
	    
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "additem"; 
	    $oButton->class = " noactivebuttons";    
	    $oButton->label = ffTemplate::_get_word_by_code("add_item");
	    $oButton->action_type = "gotourl";
	    $oButton->url = "javascript:jQuery(\'.uploadify\').uploadifyUpload();";
            $oButton->aspect = "link";
	    $oButton->parent_page = array(&$cm->oPage);
	    
	    $tpl->set_var("insert", $oButton->process()); 
	    
	    $oButton = ffButton::factory($cm->oPage);
	    $oButton->id = "clearqueue"; 
	    $oButton->class = " noactivebuttons";
	    $oButton->label = ffTemplate::_get_word_by_code("clear_queue");
	    $oButton->action_type = "gotourl";
	    $oButton->url = "javascript:jQuery(\'.uploadify\').uploadifyClearQueue();";
            $oButton->aspect = "link";
	    $oButton->parent_page = array(&$cm->oPage);
	    
	    $tpl->set_var("reset", $oButton->process()); 
	    $tpl->parse("SezExecute", false);
	}
	   	
   	$cm->oPage->addContent($tpl->rpparse("main", false));*/
   	
	if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
		$is_valid = false;

	    if(strlen($source)) {
			if (
				substr(strtolower($source), 0, 7) == "http://"
				|| substr(strtolower($source), 0, 8) == "https://"
			) {
				$content = file_get_contents($source);

	            $file_name = ffGetFilename($source);
	            $file_ext = ffGetFilename($source, false);
			} elseif(is_file(FF_DISK_PATH . $source)) {
	            $content = file_get_contents(FF_DISK_PATH . $source);

		        $file_name = ffGetFilename($source);
		        $file_ext = ffGetFilename($source, false);
			}
		} else {
			$content = "";
		}
		//$actual_path = $_REQUEST["path"];

		$oRecord = ffRecord::factory($cm->oPage);
		$oRecord->id = "ThemeModify";
		$oRecord->resources[] = $oRecord->id;
		$oRecord->title = ffTemplate::_get_word_by_code("html_modify_title");
		$oRecord->src_table = "";
		$oRecord->skip_action = true;

		$oRecord->buttons_options["delete"]["display"] = AREA_HTML_SHOW_DELETE;
		$oRecord->addEvent("on_done_action", "HtmlModify_on_done_action");
		$oRecord->addEvent("on_process_template", "HtmlModify_on_process_template");

		$oRecord->user_vars["extype"] = $extype;
		$oRecord->user_vars["parent"] = $base_path . $path;
		$oRecord->user_vars["base_path"] = $base_path;
		$oRecord->user_vars["path"] = $path;
        
		if(isset($_REQUEST["keys"]["nameID"]) && $is_valid) {
			$oRecord->buttons_options["insert"]["display"] = false;
			$oRecord->buttons_options["update"]["display"] = true;
		} else {
			$oRecord->buttons_options["insert"]["display"] = true;
			$oRecord->buttons_options["update"]["display"] = false;
		}

		$oField = ffField::factory($cm->oPage);
		$oField->id = "nameID";
		$oRecord->addKeyField($oField);

		if(strlen($source) || $source === null) {
			$oField = ffField::factory($cm->oPage);
			$oField->id = "name";
			$oField->label = ffTemplate::_get_word_by_code("html_modify_name");
			$oField->store_in_db = false;
			$oField->default_value = new ffData($file_name);
			if($source === null) {
				$oField->required = true;
			} else {
				$oField->control_type = "label";
			}
			$oRecord->addContent($oField);
		}

		$oField = ffField::factory($cm->oPage);
		$oField->id = "type";
		$oField->label = ffTemplate::_get_word_by_code("html_modify_type");
		$oField->extended_type = "Selection";
		
		$oField->multi_pairs = array (
					                array(new ffData("html"), new ffData("Html"))
                            		, array(new ffData("xml"), new ffData("Xml")) 
                            		, array(new ffData("css"), new ffData("Css")) 
                            		, array(new ffData("js"), new ffData("Js")) 
					           );
		$oField->store_in_db = false;
		$oField->multi_select_one = false;
		if(strlen($file_ext))
			$oField->default_value = new ffData($file_ext);
		elseif(strlen($arrBasePath[basename($base_path)]))
			$oField->default_value = new ffData($arrBasePath[basename($base_path)]);
		else
			$oField->default_value = new ffData("html");
			
		if($source === null) {
			$oField->required = true;
		} else {
			$oField->control_type = "label";			
		}
		$oRecord->addContent($oField);
        
        if($file_ext == "css" || $arrBasePath[basename($base_path)] == "css") {
            $oField = ffField::factory($cm->oPage);
            $oField->id = "limit_lang";
            $oField->label = ffTemplate::_get_word_by_code("html_modify_limit_lang");
            $oField->extended_type = "Selection";
            $oField->source_SQL = "SELECT LOWER(" . FF_PREFIX . "languages.code)
                                        , " . FF_PREFIX . "languages.code
                                    FROM  " . FF_PREFIX . "languages
                                    WHERE " . FF_PREFIX . "languages.status > 0
                                    ORDER BY " . FF_PREFIX . "languages.code";
            $oField->store_in_db = false;
            $oRecord->addContent($oField);
        }
        
		$oField = ffField::factory($cm->oPage);
		$oField->id = "content";
		$oField->label = ffTemplate::_get_word_by_code("html_modify_content");
		$oField->display_label = false;
		$oField->store_in_db = false;
		$oField->extended_type = "Text";
		$oField->encode_entities = false;
                $oField->widget = "editarea";
                if(check_function("set_field_textarea")) { 
                    $oField = set_field_textarea($oField);
                }
		//$oField->widget = "codemirror";
                        //
		if(strlen($file_ext)) {
			$oField->editarea_syntax = $file_ext;
		} elseif(strlen($arrBasePath[basename($base_path)])) {
			$oField->editarea_syntax = $arrBasePath[basename($base_path)];
		} else {
			$oField->editarea_syntax = "html";
		}
		$oField->required = true;
		$oField->default_value = new ffData($content);
		$oRecord->addContent($oField);

		$cm->oPage->addContent($oRecord);   
	} else {
		$cm->oPage->addContent(ffTemplate::_get_word_by_code("ftp_not_configutated"));
	}  
}
    
function HtmlModify_on_process_template($component, $tpl) {
	if(isset($_REQUEST["keys"]["nameID"]) && !$component->buttons_options["update"]["display"]) {
		if (isset($_REQUEST["XHR_DIALOG_ID"]))
			$component->dialog(false, "okonly", $component->parent[0]->title, ffTemplate::_get_word_by_code("record_not_found"), "", "[CLOSEDIALOG]");
		else
			$component->dialog(false, "okonly", $component->parent[0]->title, ffTemplate::_get_word_by_code("record_not_found"), "", $component->parent[0]->ret_url);
	}	
}
  
function HtmlModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    if(!strlen($component->user_vars["path"])) {
    	$component->tplDisplayError(ffTemplate::_get_word_by_code("theme_path_required"));
    	return true;
	}
	if($component->user_vars["extype"] == "files") {
		$real_file_prefix = ltrim($component->user_vars["path"], "/") . "/";
	} else {
	    if(strlen($component->user_vars["path"]) && $component->user_vars["path"] != "/") {
    		$real_file_prefix = str_replace("/", "_", trim($component->user_vars["path"], "/"));
    		if(isset($component->form_fields["name"]) && strlen($component->form_fields["name"]->getValue()))
    			$real_file_prefix .= "_";
		} else {
			if(!isset($component->form_fields["name"]) || !strlen($component->form_fields["name"]->getValue()))
				$real_file_prefix = "root";
			else
    			$real_file_prefix = "";
		}
	}
	$real_file_old = $component->user_vars["base_path"] . "/" . $real_file_prefix . (isset($component->form_fields["name"]) ? $component->form_fields["name"]->default_value->getValue() : "") . (isset($component->form_fields["type"]) ? "." . $component->form_fields["type"]->default_value->getValue() : "");
    $real_file = $component->user_vars["base_path"] . "/" . $real_file_prefix . (isset($component->form_fields["name"]) ? $component->form_fields["name"]->getValue() : "") . (isset($component->form_fields["limit_lang"]) && $component->form_fields["limit_lang"]->getValue() ? "-" . $component->form_fields["limit_lang"]->getValue() : "") . (isset($component->form_fields["type"]) ? "." . $component->form_fields["type"]->getValue() : "");

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
						} else {
							if($action == "update") {
								if($real_file_old != $real_file) {
									if(@ftp_rename($conn_id, $real_ftp_path . $real_file_old, $real_ftp_path . $real_file) === false) {
										$strError = ffTemplate::_get_word_by_code("ftp_unavailable_rename");		
									}
								}
							}

					        if (!$strError && isset($component->form_fields["content"])) {
		        				switch($action) {
									case "insert":
									case "update":
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
		}
    }
}
?>
