<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_HTML_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
$db = ffDB_Sql::factory();

$sSQL = "SELECT " . FF_PREFIX . "languages.ID AS ID_languages
				, " . FF_PREFIX . "languages.description AS description 
				, " . FF_PREFIX . "languages.code AS code_lang 
			FROM " . FF_PREFIX . "languages
			WHERE
			" . FF_PREFIX . "languages.status > 0
			ORDER BY " . FF_PREFIX . "languages.description";
$db->query($sSQL);
if($db->nextRecord())
{
	do {
		$code_lang = $db->getField("code_lang", "Text", true);
		$enabled_lang[$code_lang] = array(
			"description" => $db->getField("description", "Text", true)
			, "path" => FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH. "/" . $code_lang
		);
	} while ($db->nextRecord());
}

if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
	if(is_array($enabled_lang) && count($enabled_lang))
	{
		if(isset($_REQUEST["keys"]["nameID"]))
		{
			$_REQUEST["keys"]["nameID"] = ffCommon_url_rewrite($_REQUEST["keys"]["nameID"]);
			$file_title = ffTemplate::_get_word_by_code("modify_file") . ": " . $_REQUEST["keys"]["nameID"];
		} else 
		{
			$file_title = ffTemplate::_get_word_by_code("addnew_file");
		}
		
		
		$static_file = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/*");
		$is_valid = false;
		
		if(is_array($static_file) && count($static_file)) {
		    foreach($static_file AS $real_file) {
		        if(is_file($real_file)) {
		            $relative_path = str_replace(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH, "", $real_file);
		            
		            if(ffCommon_url_rewrite(basename($real_file)) == $_REQUEST["keys"]["nameID"]) {
						$draft_content[LANGUAGE_DEFAULT] = array (
							"path" => FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/"
							, "relative_path" => ffGetFilename($real_file)
							, "file_ext" => ffGetFilename($real_file, false)
							, "content" => file_get_contents($real_file)
							, "is_valid" => true
						);
            			$file_name = ffGetFilename($real_file);
            			$file_ext = ffGetFilename($real_file, false);
            			$content = file_get_contents($real_file);
            			$is_valid = true;
					}
		        }
		    }
		}
		
		foreach($enabled_lang AS $code_lang => $code) 
		{
			//echo $code_lang;
			//echo FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/*";
			${"static_file_" . $code_lang} = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/*");
			//print_r(${"static_file_" . $code_lang});
			${"is_valid_" . $code_lang} = false;
		
			if(is_array(${"static_file_" . $code_lang}) && count(${"static_file_" . $code_lang})) {
				foreach(${"static_file_" . $code_lang} AS ${"real_file_" . $code_lang}) {
					if(is_file(${"real_file_" . $code_lang})) {
						${"relative_path_" . $code_lang} = str_replace($code["path"], "", $real_file);

						if(ffCommon_url_rewrite(basename(${"real_file_" . $code_lang})) == $_REQUEST["keys"]["nameID"]) {
							$draft_content[$code_lang] = array (
								"path" => FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/"
								, "relative_path" => ffGetFilename(${"real_file_" . $code_lang})
								, "file_ext" => ffGetFilename(${"real_file_" . $code_lang}, false)
								, "content" => file_get_contents(${"real_file_" . $code_lang})
								, "is_valid" => true
							);
							${"file_name_" . $code_lang} = ffGetFilename(${"real_file_" . $code_lang});
							${"file_ext_" . $code_lang} = ffGetFilename(${"real_file_" . $code_lang}, false);
							${"content_" . $code_lang} = file_get_contents(${"real_file_" . $code_lang});
							${"is_valid_" . $code_lang} = true;
							$is_valid = true;
							$file_name = ffGetFilename(${"real_file_" . $code_lang});
							$file_ext = ffGetFilename(${"real_file_" . $code_lang}, false);
						}
					}
				}
			}
			
		}
		
		if(!strlen($file_ext))
			$file_ext = "html";
		
		$oRecord = ffRecord::factory($cm->oPage);
		$oRecord->id = "HtmlModify";
		$oRecord->resources[] = $oRecord->id;
		//$oRecord->title = ffTemplate::_get_word_by_code("html_modify_title");
		$oRecord->src_table = "";
		$oRecord->skip_action = true;
		$oRecord->user_vars["arrLang"] = $enabled_lang;
		$oRecord->buttons_options["delete"]["display"] = AREA_HTML_SHOW_DELETE;
		$oRecord->addEvent("on_do_action", "HtmlModify_on_do_action");
		$oRecord->addEvent("on_process_template", "HtmlModify_on_process_template");
		/* Title Block */
		$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . cm_getClassByFrameworkCss("vg-file", "icon-tag", array("2x", "content")) . $file_title . '</h1>';
		
		if(is_array($draft_content) && count($draft_content))
			$oRecord->user_vars["draftContent"] = $draft_content;

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

		$oField = ffField::factory($cm->oPage);
		$oField->id = "name";
		$oField->label = ffTemplate::_get_word_by_code("html_modify_name");
		$oField->store_in_db = false;
		$oField->default_value = new ffData($file_name);
		$oField->required = true;
		$oRecord->addContent($oField);
		
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
		$oField->default_value = new ffData($file_ext);
		$oField->required = true;
		$oRecord->addContent($oField);

		foreach($enabled_lang AS $code_lang => $code)
		{
			$oRecord->addTab("html_" . $code_lang);
			$oRecord->setTabTitle($code["description"]);
			
			$oRecord->addContent(null, true, "html_" . $code_lang); 
			$oRecord->groups["html_" . $code_lang] = array(
													 "title" => $code["description"]
													 , "cols" => 1
													 , "tab" => "html_" . $code_lang
												  );

			
			$oField = ffField::factory($cm->oPage);
			$oField->id = "content_" . $code_lang;
			$oField->label = ffTemplate::_get_word_by_code("html_modify_content");
			$oField->display_label = false;
			$oField->extended_type = "Text";
			$oField->store_in_db = false;
			$oField->default_value = new ffData($draft_content[$code_lang]["content"]);
			$oField->encode_entities = false;
			$oField->widget = "editarea";
			$oField->editarea_syntax = $draft_content[$code_lang]["file_ext"];
			//$oField->required = true;
			if(check_function("set_field_textarea")) { 
				$oField = set_field_textarea($oField);
			}
			$oRecord->addContent($oField, "html_" . $code_lang);
		}
	}
	$cm->oPage->addContent($oRecord);   
} else {
	$cm->oPage->addContent(ffTemplate::_get_word_by_code("ftp_not_configutated"));
}  

function HtmlModify_on_process_template($component, $tpl) {
	if(isset($_REQUEST["keys"]["nameID"]) && !$component->buttons_options["update"]["display"]) {
		if (isset($_REQUEST["XHR_DIALOG_ID"]))
			$component->dialog(false, "okonly", $component->parent[0]->title, ffTemplate::_get_word_by_code("record_not_found"), "", "[CLOSEDIALOG]");
		else
			$component->dialog(false, "okonly", $component->parent[0]->title, ffTemplate::_get_word_by_code("record_not_found"), "", $component->parent[0]->ret_url);
	}	
}
  
function HtmlModify_on_do_action($component, $action) 
{
    $db = ffDB_Sql::factory();
    
	
    if(strlen($action)) 
	{
		if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) 
		{
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

		    if($conn_id !== false) 
			{
		        // login with username and password
		        if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) 
				{
					foreach($component->user_vars["arrLang"] AS $code_lang => $value)
					{
						//if(strlen($component->form_fields["content_" . $code_lang]->getValue()))
						//{
							if(isset($component->user_vars["draftContent"][$code_lang]))
							{	
								${"real_file_path_" . $code_lang} = str_replace(FF_DISK_PATH, "", $component->user_vars["draftContent"][$code_lang]["path"]);
								${"real_file_" . $code_lang} = ${"real_file_path_" . $code_lang} . ffCommon_url_rewrite($component->form_fields["name"]->getValue()) . "." . $component->form_fields["type"]->getValue();
								${"real_file_old_" . $code_lang} = ${"real_file_path_" . $code_lang} . $component->user_vars["draftContent"][$code_lang]["relative_path"] . "." . $component->user_vars["draftContent"][$code_lang]["file_ext"];
							} else
							{
								if($code_lang == LANGUAGE_DEFAULT)
								{
									${"real_file_" . $code_lang} = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . ffCommon_url_rewrite($component->form_fields["name"]->getValue()) . "." . $component->form_fields["type"]->getValue();
									${"real_file_old_" . $code_lang} = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $component->form_fields["name"]->default_value->getValue() . "." . $component->form_fields["type"]->default_value->getValue();
								} else
								{
									${"real_file_" . $code_lang} = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/" .ffCommon_url_rewrite($component->form_fields["name"]->getValue()) . "." . $component->form_fields["type"]->getValue();
									${"real_file_old_" . $code_lang} = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $code_lang . "/" .$component->form_fields["name"]->default_value->getValue() . "." . $component->form_fields["type"]->default_value->getValue();
								}
							}
							$local_path = FF_DISK_PATH;
							$part_path = "";
							$real_ftp_path = NULL;

							foreach(explode("/", $local_path) AS $curr_path) 
							{
								if(strlen($curr_path)) 
								{
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
									if (@ftp_delete($conn_id, $real_ftp_path . ${"real_file_" . $code_lang}) === false) {
										$strError = ffTemplate::_get_word_by_code("ftp_unavailable_delte_file");
									}
								} else {
									if($action == "update") {
										if(${"real_file_old_" . $code_lang} != ${"real_file_" . $code_lang}) {
											if(@ftp_rename($conn_id, $real_ftp_path . ${"real_file_old_" . $code_lang}, $real_ftp_path . ${"real_file_" . $code_lang}) === false) {
												$strError = ffTemplate::_get_word_by_code("ftp_unavailable_rename_file");		
											}
										}
									}

									if (!$strError) {
										switch($action) {
											case "insert":
											case "update":
												if(!is_dir(ffCommon_dirname(FF_DISK_PATH . ${"real_file_" . $code_lang}))) {
													if(@ftp_mkdir($conn_id, $real_ftp_path . ffCommon_dirname(${"real_file_" . $code_lang}))) {
														if(@ftp_chmod($conn_id, 0755, $real_ftp_path . ffCommon_dirname(${"real_file_" . $code_lang})) === false) {
															$strError = ffTemplate::_get_word_by_code("unavailable_change_folder_permission");
														}
													} else {
														$strError = ffTemplate::_get_word_by_code("unavailable_create_folder");
													}
												}
												if(!strlen($strError)) {
													$handle = @tmpfile();
													@fwrite($handle, $component->form_fields["content_" . $code_lang]->getValue());
													@fseek($handle, 0);
													if(!@ftp_fput($conn_id, $real_ftp_path . ${"real_file_" . $code_lang}, $handle, FTP_ASCII)) {
														$strError = ffTemplate::_get_word_by_code("unable_write_file");
													} else {
														if(@ftp_chmod($conn_id, 0644, $real_ftp_path . ${"real_file_" . $code_lang}) === false) {
															if(@chmod(FF_DISK_PATH . ${"real_file_" . $code_lang}, 0644) === false) {
																$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
															}
														}
													}
													@fclose($handle);
												}
												break;

											default:
										}
										$file_chmod = "777";
										if(substr(decoct( @fileperms(FF_DISK_PATH . ${"real_file_" . $code_lang})), 3) != $file_chmod) {
											$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
											if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . ${"real_file_" . $code_lang}) === false) {
												if(@chmod(FF_DISK_PATH . ${"real_file_" . $code_lang}, $file_chmod) === false) {
													$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
												}
											}
										}
									}
								}
							} else {
								$strError = ffTemplate::_get_word_by_code("ftp_unavailable_root_dir");
							}
						/*} else {
							if($code_lang == LANGUAGE_DEFAULT)
								$strError = ffTemplate::_get_word_by_code("language_default_text_missing");
						}*/
						
						$arrRefreshCache[] = basename(${"real_file_" . $code_lang});
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
			if(is_array($arrRefreshCache) && count($arrRefreshCache) && check_function("refresh_cache")) {
                refresh_cache("T", false, "update", $arrRefreshCache);
			}						
			die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "insert_id" => "/template/" . ffcommon_url_rewrite($component->form_fields["name"]->getValue()) . "." . $component->form_fields["type"]->getValue(), "resources" => array("HtmlModify")), true));
		}
    }
}

?>