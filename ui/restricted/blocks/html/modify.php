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
 
if (!AREA_HTML_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

check_function("system_ffcomponent_set_title");
system_ffcomponent_resolve_by_path();

$db = ffDB_Sql::factory();

if(check_function("get_locale")) {
	$arrLang = get_locale("lang", true);
}

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "HtmlModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->src_table = "";
$oRecord->skip_action = true;

/**
* Title
*/
if(isset($_REQUEST["keys"]["permalink"]))
{
	$file_basename = basename($_REQUEST["keys"]["permalink"]);
	$file_name = ffGetFilename($_REQUEST["keys"]["permalink"]);
} else 
{
	$file_basename = "";
	$file_name = ffTemplate::_get_word_by_code("addnew_file");
}

system_ffcomponent_set_title(
	$file_name
	, true
	, false
	, false
	, $oRecord
);

if(is_array($arrLang) && count($arrLang))
{
	$oRecord->user_vars["arrLang"] = $arrLang;
	$oRecord->buttons_options["delete"]["display"] = AREA_HTML_SHOW_DELETE;
	$oRecord->addEvent("on_do_action", "HtmlModify_on_do_action");

	$is_valid = true;
	if(!(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD))) {
		$oRecord->strError = ffTemplate::_get_word_by_code("ftp_not_configutated");
		$is_valid = false;
	}
		
	if($file_basename && $is_valid) {
		$oRecord->buttons_options["insert"]["display"] = false;
		$oRecord->buttons_options["update"]["display"] = true;
	} else {
		$oRecord->buttons_options["insert"]["display"] = true;
		$oRecord->buttons_options["update"]["display"] = false;
	}

	$oField = ffField::factory($cm->oPage);
	$oField->id = "permalink";
	$oRecord->addKeyField($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "name";
	$oField->label = ffTemplate::_get_word_by_code("html_modify_name");
	$oField->store_in_db = false;
	$oField->default_value = new ffData($file_name);
	$oField->required = true;
	$oRecord->addContent($oField);

	foreach($arrLang AS $lang_code => $lang)
	{
		$oRecord->addTab("html_" . $lang_code);
		$oRecord->setTabTitle("html_" . $lang_code, $lang["description"]);
        $oRecord->tab = "right";
		
		$oRecord->addContent(null, true, "html_" . $lang_code); 
		$oRecord->groups["html_" . $lang_code] = array(
												 "title" => $lang["description"]
												 , "tab" => "html_" . $lang_code
											  );

										 	  

		$html_content = "";
		if(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $lang_code . "/" . $file_basename)) {
			$html_content = file_get_contents(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $lang_code . "/" . $file_basename);
		} elseif(LANGUAGE_DEFAULT == $lang_code && is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $file_basename)) {
			$html_content = file_get_contents(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $file_basename);
		}
		 
		$oField = ffField::factory($cm->oPage);
		$oField->id = "content_" . $lang_code;
		$oField->label = ffTemplate::_get_word_by_code("html_modify_content");
		$oField->display_label = false;
		$oField->extended_type = "Text";
		$oField->store_in_db = false;
		$oField->default_value = new ffData($html_content);
		$oField->encode_entities = false;
		$oField->widget = "editarea";
		$oField->editarea_syntax = "html";
		if(check_function("set_field_textarea")) { 
			$oField = set_field_textarea($oField);
		}
		$oRecord->addContent($oField, "html_" . $lang_code);
	}
} else {
	$oRecord->hide_all_controls = true;
	if(check_function("process_html_page_error"))
		$oRecord->fixed_pre_content = process_html_page_error(404);
}

$cm->oPage->addContent($oRecord);   
  
function HtmlModify_on_do_action($component, $action) 
{
    $db = ffDB_Sql::factory();
    $cm = cm::getInstance();
	
    if(strlen($action)) 
	{
		if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) 
		{
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
						$filename_old = $component->form_fields["name"]->default_value->getValue();
						$filename_new = ffCommon_url_rewrite($component->form_fields["name"]->value->getValue());
						foreach($component->user_vars["arrLang"] AS $lang_code => $value)
						{
							if($lang_code == "current")
								continue;

							$base_path = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/template/" . $lang_code;
							switch($action) {
								case "update":
									if(is_file(FF_DISK_PATH . $base_path . "/" . $filename_old . "." . "html")) {
										if($filename_old != $filename_new || !strlen($component->form_fields["content_" . $lang_code]->getValue())) {
											if (!@ftp_delete($conn_id, $real_ftp_path . $base_path . "/" . $filename_old . "." . "html")) {
												$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_rename_file");
											}
										}
									}
								case "insert":
									if(!strlen($component->form_fields["content_" . $lang_code]->getValue()))
										continue;

									if(!is_dir(FF_DISK_PATH . $base_path)) {
										if(@ftp_mkdir($conn_id, $real_ftp_path . $base_path)) {
											if(@ftp_chmod($conn_id, 0755, $real_ftp_path . $base_path) === false) {
												$arrError[] = ffTemplate::_get_word_by_code("unavailable_change_folder_permission");
											}
										} else {
											$arrError[] = ffTemplate::_get_word_by_code("unavailable_create_folder");
										}
									}
									if(!$arrError) {
										$handle = @tmpfile();
										@fwrite($handle, $component->form_fields["content_" . $lang_code]->getValue());
										@fseek($handle, 0);

										if(!@ftp_fput($conn_id, $real_ftp_path . $base_path . "/" . $filename_new . "." . "html", $handle, FTP_ASCII)) {
											$arrError[] = ffTemplate::_get_word_by_code("unable_write_file");
										} else {
											if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $base_path . "/" . $filename_new . "." . "html") === false) {
												if(@chmod(FF_DISK_PATH . $base_path . "/" . $filename_new . "." . "html", 0644) === false) {
													$arrError[] = ffTemplate::_get_word_by_code("unavailable_change_permission");
												}
											}
										}
										@fclose($handle);
									}								
									break;
								case "confirmdelete":
									if(is_file(FF_DISK_PATH . $base_path . "/" . $filename_old . "." . "html")) {
										if (!@ftp_delete($conn_id, $real_ftp_path . $base_path . "/" . $filename_old . "." . "html")) {
											$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_delte_file");
										}
									}
									break;
							}
							$arrRefreshCache[] = basename(${"real_file_" . $lang_code});
						}
						
						if(!$arrError) {
							if($action == "confirmdelete") {
								if(is_file(FF_DISK_PATH . ffCommon_dirname($base_path) . "/" . $filename_old . "." . "html")) {
									if (!@ftp_delete($conn_id, $real_ftp_path . ffCommon_dirname($base_path) . "/" . $filename_old . "." . "html")) {
										$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_delte_file");
									}
								}							
							} else {
								if(strlen($component->form_fields["content_" . LANGUAGE_DEFAULT]->getValue())) {
									$handle = @tmpfile();
									@fwrite($handle, $component->form_fields["content_" . LANGUAGE_DEFAULT]->getValue());
									@fseek($handle, 0);
									if(!@ftp_fput($conn_id, $real_ftp_path . ffCommon_dirname($base_path) . "/" . $filename_new . "." . "html", $handle, FTP_ASCII)) {
										$arrError[] = ffTemplate::_get_word_by_code("unable_write_file_default");
									} else {
										if(@ftp_chmod($conn_id, 0644, $real_ftp_path . ffCommon_dirname($base_path) . "/" . $filename_new . "." . "html") === false) {
											if(@chmod(FF_DISK_PATH . ffCommon_dirname($base_path) . "/" . $filename_new . "." . "html", 0644) === false) {
												$arrError[] = ffTemplate::_get_word_by_code("unavailable_change_permission_default");
											}
										}
									}
									@fclose($handle);
								} elseif(is_file(FF_DISK_PATH . ffCommon_dirname($base_path) . "/" . $filename_old . "." . "html")) {
									if (!@ftp_delete($conn_id, $real_ftp_path . ffCommon_dirname($base_path) . "/" . $filename_old . "." . "html")) {
										$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_delte_file_default");
									}							
								}
							}
						}
					} else {
						$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_root_dir");
					}
		        } else {
		            $arrError[] = ffTemplate::_get_word_by_code("ftp_access_denied");
		        }
		    } else {
		        $arrError[] = ffTemplate::_get_word_by_code("ftp_connection_failure");
		    }
		    // close the connection and the file handler
		    @ftp_close($conn_id);
		} else {
		    $arrError[] = ffTemplate::_get_word_by_code("ftp_not_configutated");
		}

		if($arrError) {
			$component->tplDisplayError(implode("<br />", $arrError));
			return true;
		} else {
			if(is_array($arrRefreshCache) && count($arrRefreshCache) && check_function("refresh_cache")) {
                refresh_cache("T", false, "update", $arrRefreshCache);
			}						
		//	if($cm->isXHR())
				//die(ffCommon_jsonenc(array("close" => true, "refresh" => true, "insert_id" => "/template/" . ffcommon_url_rewrite($component->form_fields["name"]->getValue()) . "." . "html", "resources" => array("HtmlModify")), true));
		}
    }
}