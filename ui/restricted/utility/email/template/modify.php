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
 * @subpackage console
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

if (!Auth::env("AREA_EMAIL_SHOW_MODIFY")) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------

if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
	$base_path = FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH;
	if(isset($_REQUEST["keys"]["path"]) && strlen($_REQUEST["keys"]["path"]) && is_file($base_path . $_REQUEST["keys"]["path"])) {
		$tpl_email_path = $_REQUEST["keys"]["path"];
		$content = file_get_contents($base_path . $_REQUEST["keys"]["path"]);
		$name = basename(ffcommon_dirname($_REQUEST["keys"]["path"]));
	} else {
		$content = file_get_contents(__CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/contents/email/email.tpl");
		$name = $_REQUEST["name"];
	}
	$is_valid = true;
} else {
	$is_valid = false;
}

$ID_email = $_REQUEST["keys"]["email-ID"];
if($ID_email > 0) {
	$to[] = $email_name . "@example.ex";
	if(check_function("process_mail"))
		$buffer = process_mail(email_system($ID_email), $to, NULL, null, null, NULL, NULL, NULL, true, false);
}



$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "EmailTemplateModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("email_template_modify_title");
$oRecord->src_table = "";
$oRecord->addEvent("on_done_action", "EmailTemplateModify_on_done_action");
$oRecord->addEvent("on_loaded_data", "EmailTemplateModify_on_loaded_data");
$oRecord->user_vars["name"] = $name;
$oRecord->user_vars["content"] = $content;
$oRecord->skip_action = true;
$oRecord->fixed_pre_content = $buffer;

$oField = ffField::factory($cm->oPage);
$oField->id = "path";
$oRecord->addKeyField($oField);

if($is_valid) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("email_template_modify_name");
	$oField->required = true;
	$oField->store_in_db = false;
	$oField->default_value = new ffData($name);
	if(strlen($_REQUEST["keys"]["path"]))
		$oField->control_type = "label";
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "content";
	$oField->label = ffTemplate::_get_word_by_code("email_template_modify_content");
	$oField->display_label = false;
	$oField->extended_type = "Text";
	$oField->encode_entities = false;
	$oField->widget = "editarea";
	$oField->editarea_syntax = "html";
	$oField->editarea_writable = true;
	$oField->required = true;
	$oField->store_in_db = false;
	$oField->default_value = new ffData($content);
        if(check_function("set_field_textarea")) { 
            $oField = set_field_textarea($oField);
        }
	$oRecord->addContent($oField);
}
                 
$cm->oPage->addContent($oRecord);

function EmailTemplateModify_on_loaded_data($component) {
    if(isset($component->form_fields["content"]) && !strlen($component->form_fields["content"]->getValue()) && strlen($component->user_vars["content"])) {
        $component->form_fields["content"]->setValue($component->user_vars["content"]);
    }    
}

function EmailTemplateModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

	$base_file = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH;
    
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
		            	$type_mode = strtolower(preg_replace('/[^a-zA-Z0-9\_\s]/', '', $component->form_fields["name"]->getValue()));
		            	
						$tpl_email_html = $base_file . "/email/" . $type_mode . "/email.tpl";
						$tpl_email_txt = $base_file . "/email/" . $type_mode . "/email.txt";
						
		            	if($action == "confirmdelete") {
		            		if(check_function("fs_operation"))
		            			ftp_purge_dir($conn_id, $real_ftp_path, ffcommon_dirname($tpl_email_html), FF_DISK_PATH);
						} else {
					        if (isset($component->form_fields["content"])) {
		        				switch($action) {
									case "insert":
									case "update":
										if(!@ftp_chdir($conn_id, $real_ftp_path . $base_file)) {
											if(@ftp_mkdir($conn_id, $real_ftp_path . $base_file)) {
												if(@ftp_chmod($conn_id, 0775, $real_ftp_path . $base_file) === false) {
													if(@chmod(FF_DISK_PATH . $base_file, 0775) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											} else {
												$strError = ffTemplate::_get_word_by_code("unable_create_dir") . " (/contents)";
											}
										}
										if(!@ftp_chdir($conn_id, $real_ftp_path . $base_file . "/email")) {
											if(@ftp_mkdir($conn_id, $real_ftp_path . $base_file . "/email")) {
												if(@ftp_chmod($conn_id, 0775, $conn_id, $real_ftp_path . $base_file . "/email") === false) {
													if(@chmod(FF_DISK_PATH . $base_file . "/email", 0775) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											} else {
												$strError .= ffTemplate::_get_word_by_code("unable_create_dir") . " (/contents/email)\n";
											}
										}
										if(!@ftp_chdir($conn_id, $real_ftp_path . $base_file . "/email/" . $type_mode)) {
											if(@ftp_mkdir($conn_id, $real_ftp_path . $base_file . "/email/" . $type_mode)) {
												if(@ftp_chmod($conn_id, 0775, $conn_id, $real_ftp_path . $base_file . "/email/" . $type_mode) === false) {
													if(@chmod(FF_DISK_PATH . $base_file . "/email/" . $type_mode, 0775) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											} else {
												$strError .= ffTemplate::_get_word_by_code("unable_create_dir") . " (/contents/email/" . $type_mode . ")\n";
											}
										}

										$email_html_content = $component->form_fields["content"]->getValue();
										$email_txt_content = strip_tags($email_html_content);

										//write email.tpl
										$handle = @tmpfile();
										@fwrite($handle, $email_html_content);
										@fseek($handle, 0);
										if(!@ftp_fput($conn_id, $real_ftp_path . $tpl_email_html, $handle, FTP_ASCII)) {
											$strError = ffTemplate::_get_word_by_code("unable_write_file");
										} else {
											if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $tpl_email_html) === false) {
												if(@chmod(FF_DISK_PATH . $tpl_email_html, 0644) === false) {
													$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
												}
											}
										}
										@fclose($handle);

										//write email.txt
										$handle = @tmpfile();
										@fwrite($handle, $email_txt_content);
										@fseek($handle, 0);
										if(!@ftp_fput($conn_id, $real_ftp_path . $tpl_email_txt, $handle, FTP_ASCII)) {
											$strError = ffTemplate::_get_word_by_code("unable_write_file");
										} else {
											if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $tpl_email_txt) === false) {
												if(@chmod(FF_DISK_PATH . $tpl_email_txt, 0644) === false) {
													$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
												}
											}
										}
										@fclose($handle);

										break;
									default:
								}
								$file_chmod = "644";

								//permission email.tpl
								if(substr(decoct( @fileperms(FF_DISK_PATH . $tpl_email_html)), 3) != $file_chmod) {
									$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
							        if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $tpl_email_html) === false) {
						            	if(@chmod(FF_DISK_PATH . $tpl_email_html, $file_chmod) === false) {
				            				$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
										}
									}
								}
								//permission email.txt
								if(substr(decoct( @fileperms(FF_DISK_PATH . $tpl_email_txt)), 3) != $file_chmod) {
									$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
							        if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $tpl_email_txt) === false) {
						            	if(@chmod(FF_DISK_PATH . $tpl_email_txt, $file_chmod) === false) {
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
		}
    }
}