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
    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

    $operation = basename($cm->real_path_info);
    
	 $base_path = FF_SITE_UPDIR;

	if($_REQUEST["basepath"])
    	$base_path = realpath(FF_DISK_UPDIR . $_REQUEST["basepath"]);

    if($base_path) {
		$path = stripslash(urldecode($_REQUEST["path"]));
		if($path == "")
		    $path = "/";
		
		if(strlen($base_path) && strpos($path, $base_path) === 0)
    		$path = substr($path, strlen($base_path));
	
		
		$is_owner = false;
		if (!Auth::env("AREA_GALLERY_SHOW_ADDNEW")) {
    		if(is_dir(FF_DISK_UPDIR . $path)) {
    			if(Cms::env("ENABLE_STD_PERMISSION")) {
    				if(check_function("get_file_permission"))
    					$file_permission = get_file_permission($path);
    				if($file_permission["owner"] > 0 && $file_permission["owner"] === Auth::get("user")->id) {
						use_cache(false);
    					$is_owner = true;
					} else {
    					ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");	
					}
				} else {
					$owner = $_REQUEST["owner"];
					if($owner == Auth::get("user")->id) {
    					use_cache(false);
    					$is_owner = true;
					} else {
    					ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");	
					}
				}
			} else {
        		ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
			}
		}  

		if(!is_dir(FF_DISK_UPDIR . $path)) {
			if(@mkdir(FF_DISK_UPDIR . $path)) {
				@chmod(FF_DISK_UPDIR . $path, 0777);
			}		
		}
		if(!is_writable(FF_DISK_UPDIR . $path))
			$strError = ffTemplate::_get_word_by_code("gallery_unable_to_write");	

		if(!$strError && check_function("check_fs"))
		    check_fs(FF_DISK_UPDIR . $path, $path);
	
	
	    $title_gallery = $path;

		$oRecord = ffRecord::factory($cm->oPage);
		$oRecord->id = "GalleryModify";
		$oRecord->resources[] = $oRecord->id;
		//$oRecord->title = ffTemplate::_get_word_by_code("drafts_modify_title");
		//$oRecord->src_table = "";
		$oRecord->skip_action = true;

//		$oRecord->buttons_options["print"]["display"] = false;
		$oRecord->user_vars["operation"] = $operation;
		$oRecord->user_vars["path"] = $path;

		$oRecord->addEvent("on_done_action", "GalleryModify_on_done_action");
		/* Title Block */
		//$oRecord->fixed_pre_content = '<h1 class="dialogTitle admin-title vg-content">' . Cms::getInstance("frameworkcss")->get("vg-gallery", "icon-tag", array("2x", "content")) . $title_gallery . '<span class="smart-url"></span>' . '</h1>';
		if(check_function("system_ffcomponent_set_title"))
			$oRecord->setTitle(system_ffcomponent_set_title(
				$title_gallery
				, array(
					"name" => "vg-gallery"
					, "type" => "content"
				)
				, true
			), 'admin-title vg-content');
		//$oRecord->setTitle(Cms::getInstance("frameworkcss")->get("vg-gallery", "icon-tag", array("2x", "content")) . $title_gallery . '<span class="smart-url"></span>', 'admin-title vg-content');
		

		$oField = ffField::factory($cm->oPage);
		$oField->id = "ID";
		$oField->base_type = "Number";
		$oRecord->addKeyField($oField);	    

	    if(!$strError && $operation == "dir")
	    {
	    	$cm->oPage->tplAddJs("ff.cms.admin");
	    
			$oField = ffField::factory($cm->oPage);
		    $oField->id = "directory";
		    $oField->label = ffTemplate::_get_word_by_code("gallery_add_directory");
			$oField->class = "input title-page";
			$oField->properties["onkeyup"] = "javascript:ff.cms.admin.makeNewUrl();";
		    $oField->required = true;
		    $oRecord->addContent($oField);
		    
			$oField = ffField::factory($cm->oPage);
			$oField->id = "upload";
			//$oField->label = ffTemplate::_get_word_by_code("gallery_add_files");
			$oField->data_type = "";
			$oField->store_in_db = false;
			$oField->base_type = "Text";
			$oField->control_type = "file";
			$oField->extended_type = "File";
			$oField->file_storing_path = FF_DISK_UPDIR . $path . "/[directory_VALUE]";
			$oField->file_temp_path = FF_DISK_UPDIR . "/tmp" . $path;
			$oField->file_full_path = true;
			$oField->file_check_exist = true;
			$oField->file_show_filename = true; 
			$oField->file_show_delete = true;
			$oField->file_writable = false;
			$oField->file_normalize = true;
			$oField->file_show_preview = true;
			$oField->file_multi = true;
			$oField->widget = "uploadify";
			if(check_function("set_field_uploader")) { 
				$oField = set_field_uploader($oField);
			}
			$oRecord->addContent($oField);		    
	    }
	    elseif(!$strError && $operation == "item")
	    {
			$oRecord->buttons_options["insert"]["display"] = false;

			$it = new FilesystemIterator(FF_DISK_UPDIR . $path);
			foreach ($it as $fileinfo) {
				$arrPath[] = stripslash($path) . "/" . $fileinfo->getFilename();
			}

			$oField = ffField::factory($cm->oPage);
			$oField->id = "upload";
			//$oField->label = ffTemplate::_get_word_by_code("gallery_add_files");
			$oField->data_type = "";
			$oField->store_in_db = false;
			$oField->base_type = "Text";
			$oField->control_type = "file";
			$oField->extended_type = "File";
			$oField->file_storing_path = FF_DISK_UPDIR . $path;
			$oField->file_temp_path = FF_DISK_UPDIR . $path;
			$oField->file_full_path = true;
			$oField->file_check_exist = true;
			$oField->file_show_filename = true; 
			$oField->file_show_delete = true;
			$oField->file_writable = false;
			$oField->file_normalize = true;
			$oField->file_show_preview = true;
			$oField->file_multi = true;
			$oField->widget = "uploadify";
			if(is_array($arrPath) && count($arrPath))
				$oField->default_value = new ffData(implode(",", $arrPath));

			if(check_function("set_field_uploader")) { 
				$oField = set_field_uploader($oField);
			}
			$oRecord->addContent($oField);
		} elseif(!$strError) {
			$strError = ffTemplate::_get_word_by_code("gallery_missing_operation");				
		}
    } else {
    	$strError = ffTemplate::_get_word_by_code("gallery_wrong_basepath");	
    }
    
	if($strError) {
		$oRecord->strError = $strError;
		$oRecord->buttons_options["insert"]["display"] = false;
	}

	$cm->oPage->addContent($oRecord);    
    
 function GalleryModify_on_done_action($component, $action) {
 	if($action == "insert" && $component->user_vars["operation"] == "dir") { 
 		$directory = $component->form_fields["directory"]->getValue();
 	
	  if(@mkdir(FF_DISK_UPDIR . $component->user_vars["path"] . "/" . $directory)) {
	        @chmod(FF_DISK_UPDIR . $component->user_vars["path"] . "/" . $directory, 0777);
	        if(check_function("check_fs"))
	            check_fs(FF_DISK_UPDIR . $component->user_vars["path"] . "/" . $directory, $component->user_vars["path"] . "/" . $directory);
	        //ffRedirect($ret_url);
	    } else {
	        $component->tplDisplayError(ffTemplate::_get_word_by_code("gallery_unable_create_dir"));
	        return true;
	    }
 	}
 }