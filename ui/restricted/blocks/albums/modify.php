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

 	if (!(AREA_GALLERY_SHOW_MODIFY || AREA_GALLERY_SHOW_ADDNEW || AREA_GALLERY_SHOW_DELETE)) {
	    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
	}
	
	check_function("system_ffcomponent_set_title");
	
	system_ffcomponent_resolve_by_path();
	if(!is_dir(DISK_UPDIR . $_REQUEST["keys"]["permalink"]))
		mkdir(DISK_UPDIR . $_REQUEST["keys"]["permalink"], 0777, true);

	if(!is_writable(DISK_UPDIR . $_REQUEST["keys"]["permalink"]))
		$strError = ffTemplate::_get_word_by_code("gallery_unable_to_write");	

	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "GalleryModify";
	$oRecord->resources[] = $oRecord->id;
	$oRecord->skip_action = true;
	$oRecord->addEvent("on_done_action", "GalleryModify_on_done_action");

	if($strError) {
		$oRecord->strError = $strError;
		$oRecord->hide_all_controls = true;
	}	
	

	/**
	* Title
	*/
	if(isset($_REQUEST["keys"]["permalink"]))
	{
		$album_name = ffCommon_url_rewrite(basename($_REQUEST["keys"]["permalink"]));
		$album_title = ucwords(str_replace("-", " " , basename($_REQUEST["keys"]["permalink"])));
		
		if(!$strError && check_function("check_fs"))
			check_fs(DISK_UPDIR . $_REQUEST["keys"]["permalink"], $_REQUEST["keys"]["permalink"]);    		

	
		$it = new FilesystemIterator(DISK_UPDIR . $_REQUEST["keys"]["permalink"]);
		foreach ($it as $fileinfo) {
			$arrPath[] = stripslash($_REQUEST["keys"]["permalink"]) . "/" . $fileinfo->getFilename();
		}			
	} else 
	{
		$album_title = ffTemplate::_get_word_by_code("addnew_album");
	}
	
	system_ffcomponent_set_title(
		$album_title
		, true
		, false
		, false
		, $oRecord
	);

	$labelWidth = array(2,3,5,5);	                
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "permalink";
	$oRecord->addKeyField($oField);	    

	$oField = ffField::factory($cm->oPage);
	$oField->id = "display_name";
	$oField->label = ffTemplate::_get_word_by_code("album_name");
	$oField->setWidthLabel($labelWidth);
	$oField->display_label = false;
	$oField->required = true;
	$oField->default_value = new ffData($album_name);
	$oRecord->addContent($oField);		
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "albumName";
	$oField->container_class = "hidden";
	//$oField->required = true;
	$oField->widget = "slug";
	$oField->slug_title_field = "display_name";
	$oField->default_value = new ffData($album_name);
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "upload";
	$oField->data_type = "";
	$oField->store_in_db = false;
	$oField->base_type = "Text";
	$oField->control_type = "file";
	$oField->extended_type = "File";
	$oField->file_storing_path = DISK_UPDIR . "/[albumName_VALUE]";
	$oField->file_temp_path = DISK_UPDIR . "/tmp";
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

	$cm->oPage->addContent($oRecord);    
    
 function GalleryModify_on_done_action($component, $action) {
 	$cm = cm::getInstance();

 	if($action == "update") { 
 		$albumName = $component->form_fields["albumName"]->value->getValue();
 		$albumNameOld = $component->form_fields["albumName"]->default_value	->getValue();
 	
		if($albumNameOld != $albumName) {
			@rename(DISK_UPDIR . "/" . $albumNameOld, DISK_UPDIR . "/" . $albumName);
			
			ffRedirect(ffcommon_dirname($cm->oPage->getRequestUri()) . "/" . $albumName);
		}
 	}
 }