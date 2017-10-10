<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_PROPERTIES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------

if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
	$is_valid = true;
	$is_public = false;

	$base_path = FF_THEME_DIR . "/" . THEME_INSET . "/contents";
	if(isset($_REQUEST["keys"]["ID"])) {
		$sSQL = "SELECT name, content, public FROM settings_thumb_mode WHERE ID = " . $db_gallery->toSql($_REQUEST["keys"]["ID"], "Number");
		$db_gallery->query($sSQL);
		if($db_gallery->nextRecord()) {
			$is_public = $db_gallery->getField("public", "Number", true);
			$file_name = $db_gallery->getField("name", "Text", true);
			if($file_name == "HIDE") {
				$is_valid = false;
			} else {
				$content = $db_gallery->getField("content", "Text", true);
				if(!strlen($content)) {
					$content = file_get_contents(FF_DISK_PATH . $base_path . "/vgallery.tpl");
				}
			}
		}
	} else {
		$content = file_get_contents(FF_DISK_PATH . $base_path . "/vgallery.tpl");
	}
} else {
	$is_valid = false;
}


$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ExtrasModeModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("extras_mode_modify_title");
$oRecord->src_table = "settings_thumb_mode";
$oRecord->addEvent("on_done_action", "ExtrasModeModify_on_done_action");
$oRecord->addEvent("on_loaded_data", "ExtrasModeModify_on_loaded_data");
$oRecord->user_vars["content"] = $content;

$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);


$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("extras_mode_modify_name");
$oField->required = true;
if(!$is_valid)
	$oField->control_type = "label";
$oRecord->addContent($oField);

$oField = ffField::factory($cm->oPage);
$oField->id = "description";
$oField->label = ffTemplate::_get_word_by_code("extras_mode_modify_description");
$oRecord->addContent($oField);

if(MASTER_CONTROL) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "public";
	$oField->label = ffTemplate::_get_word_by_code("extras_mode_modify_public");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number");
	$oRecord->addContent($oField);
}

if($is_public) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "use_default";
	$oField->label = ffTemplate::_get_word_by_code("extras_mode_modify_use_default");
	$oField->base_type = "Number";
	$oField->extended_type = "Boolean";
	$oField->control_type = "checkbox";
	$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
	$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
	$oField->default_value = new ffData("0", "Number");
	$oRecord->addContent($oField);
}

$oField = ffField::factory($cm->oPage);
$oField->id = "type";
$oField->label = ffTemplate::_get_word_by_code("extras_mode_modify_type");
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
	                        array(new ffData("thumb"), new ffData(ffTemplate::_get_word_by_code("template_thumb"))),
	                        array(new ffData("detail"), new ffData(ffTemplate::_get_word_by_code("template_detail")))
	                   );      
$oField->required = true;
$oRecord->addContent($oField);

if($is_valid) {
	$oField = ffField::factory($cm->oPage);
	$oField->id = "content";
	$oField->label = ffTemplate::_get_word_by_code("extras_mode_modify_content");
	$oField->display_label = false;
	$oField->extended_type = "Text";
	$oField->encode_entities = false;
	$oField->widget = "editarea";
	$oField->editarea_syntax = "html";
	$oField->editarea_writable = true;
	$oField->required = true;
	$oField->default_value = new ffData($content);
        if(check_function("set_field_textarea")) { 
            $oField = set_field_textarea($oField);
        }
	$oRecord->addContent($oField);
}
                 
$cm->oPage->addContent($oRecord);

function ExtrasModeModify_on_loaded_data($component) {
    if(isset($component->form_fields["content"]) && !strlen($component->form_fields["content"]->getValue()) && strlen($component->user_vars["content"])) {
        $component->form_fields["content"]->setValue($component->user_vars["content"]);
    }    
}

function ExtrasModeModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();

    $base_file = FF_THEME_DIR . "/" . FRONTEND_THEME . "/contents";
    
$admin_ajax = '<!--BeginSezAdminAjax-->
	<input class="{class_plugin}" type="hidden" value="{admin_menu}" />
<!--EndSezAdminAjax-->
<!--BeginSezAdminNoAjax-->
	{admin_menu}
<!--EndSezAdminNoAjax-->';

$controls = '<!--BeginSezControls-->
	<div class="hidden">
	    <!--BeginSezSearchControl-->
	    <input name="search_param" type="hidden" value="{search_param}" />
	    <!--EndSezSearchControl-->
	    <!--BeginSezPageNavigatorControl-->
	    <input id="{unic_id_lower}_page" name="{unic_id_lower}_page" type="hidden" value="{page}" />
	    <input id="{unic_id_lower}_records_per_page" name="{unic_id_lower}_records_per_page" type="hidden" value="{rec_per_page}" />
	    <!--EndSezPageNavigatorControl-->
	</div>
<!--EndSezControls-->';


$header_controls = '<input type="hidden" id="{unic_id_lower}_sort" name="{unic_id_lower}_sort" value="{sort_value}" />
	<input type="hidden" id="{unic_id_lower}_sort_type" name="{unic_id_lower}_sort_type" value="{sort_type}" />';

$header_controls_gallery ='<input type="hidden" id="frmGallerys{unic_id_lower}_sort" name="frmGallerys{unic_id_lower}_sort" value="{sort_value}" />
	<input type="hidden" id="frmGallerys{unic_id_lower}_sort_type" name="frmGallerys{unic_id_lower}_sort_type" value="{sort_type}" />';

$header_items = '<a href="javascript:void(0)" onclick="
	                if(document.getElementById(\'{unic_id_lower}_sort\').value == \'{sort_value_item}\') {
	                    if(document.getElementById(\'{unic_id_lower}_sort_type\').value == \'asc\') {
	                        document.getElementById(\'{unic_id_lower}_sort_type\').value=\'desc\';
	                    } else {
	                        document.getElementById(\'{unic_id_lower}_sort_type\').value=\'asc\';
	                    }
	                } else {
	                    document.getElementById(\'{unic_id_lower}_sort_type\').value=\'asc\'; 
	                }
	                document.getElementById(\'{unic_id_lower}_sort\').value=\'{sort_value_item}\';
	                document.{form_name}.submit();">{sort_name}</a>';

$header_items_gallery = '<a href="javascript:void(0)" onclick="
                            if(document.getElementById(\'frmGallerys{unic_id_lower}_sort\').value == \'{sort_value_item}\') {
                                if(document.getElementById(\'frmGallerys{unic_id_lower}_sort_type\').value == \'asc\') {
                                    document.getElementById(\'frmGallerys{unic_id_lower}_sort_type\').value=\'desc\';
                                } else {
                                    document.getElementById(\'frmGallerys{unic_id_lower}_sort_type\').value=\'asc\';
                                }
                            } else {
                                document.getElementById(\'frmGallerys{unic_id_lower}_sort_type\').value=\'asc\'; 
                            }
                            document.getElementById(\'frmGallerys{unic_id_lower}_sort\').value=\'{sort_value_item}\';
                            document.{form_name}.submit();">{sort_name}</a>';

$item_admin_ajax = '<!--BeginSezVGalleryEdit-->
	                <!--BeginSezAjax-->
	                <input class="{class_plugin}" type="hidden" value="{admin}" />
	                <!--EndSezAjax-->
	                <!--BeginSezNoAjax-->
	                {admin}
	                <!--EndSezNoAjax-->
	            <!--EndSezVGalleryEdit-->';
$item_admin_ajax_gallery = '<!--BeginSezGalleryEdit-->
                        <!--BeginSezAjax-->
                        <input class="{class_plugin}" type="hidden" value="{admin}" />
                        <!--EndSezAjax-->
                        <!--BeginSezNoAjax-->
                        {admin}
                        <!--EndSezNoAjax-->
                    <!--EndSezGalleryEdit-->';
	            
$item_img = '<!--BeginSezVGalleryImageNode-->
	                <!--BeginSezVGalleryImageLink-->
	                <!--BeginSezVGalleryImageLinkAllow-->
	                <a href="{show_file}" class="{class_plugin}" rel="{rel_plugin}" ><img src="{show_thumb}" alt="{alt_name}" /></a>
	                <!--EndSezVGalleryImageLinkAllow-->
	                <!--BeginSezVGalleryImageLinkDenied-->
	                <img src="{show_thumb}" class="{class_plugin}" alt="{alt_name}"/>
	                <!--EndSezVGalleryImageLinkDenied-->
	                <!--EndSezVGalleryImageLink-->
	                <!--BeginSezVGalleryImageNoLink-->
	                <img src="{site_path}/cm/showfiles.php/{theme_inset}/images/spacer.gif" class="{class_plugin}" alt="{alt_name}" />
	                <!--EndSezVGalleryImageNoLink-->
	            <!--EndSezVGalleryImageNode-->';
$item_img_gallery = '<!--BeginSezGalleryImageViewLink-->
                        <a id="{real_name}" href="{show_file}" class="gallery_image {class_plugin}" rel="{rel_plugin}"><img src="{show_thumb}" alt="{alt_name}" /></a>
                        <!--EndSezGalleryImageViewLink-->
                        <!--BeginSezGalleryImageViewNoLink-->
                        <img id="{real_name}" src="{show_thumb}" class="gallery_image {class_plugin}" alt="{alt_name}" />
                        <!--EndSezGalleryImageViewNoLink-->';
	            
$item_desc = '<!--BeginSezVGalleryDescriptionNode-->
	                <!--BeginSezVGalleryDescriptionLabel-->
	                <label class="{class_name}" id="{real_name}">
	                    <!--BeginSezVGalleryDescriptionLabelLink-->
	                    <a href="{show_file}" rel="{rel_plugin}" class="{class_plugin}" {target}>{more_description_label}</a>
	                    <!--EndSezVGalleryDescriptionLabelLink-->
	                    <!--BeginSezVGalleryDescriptionLabelNoLink-->
	                    {more_description_label}
	                    <!--EndSezVGalleryDescriptionLabelNoLink-->
	                </label>
	                <!--EndSezVGalleryDescriptionLabel-->
	                <!--BeginSezVGalleryDescriptionNodeImageLink-->
	                <a class="{class_name}" rel="{rel_plugin}" id="{real_name}" href="{show_file}" {target}><img src="{more_description}" alt="{alt_name}" /></a>
	                <!--EndSezVGalleryDescriptionNodeImageLink-->
	                <!--BeginSezVGalleryDescriptionNodeImageNoLink-->
	                <img class="{class_name}" id="{real_name}" src="{more_description}" alt="{alt_name}" />
	                <!--EndSezVGalleryDescriptionNodeImageNoLink-->

	                <!--BeginSezVGalleryDescriptionNodeNoImageLink-->
	                <a class="{class_name}" rel="{rel_plugin}" id="{real_name}" href="{show_file}" {target}>{more_description}</a>
	                <!--EndSezVGalleryDescriptionNodeNoImageLink-->
	                <!--BeginSezVGalleryDescriptionNodeNoImageNoLink-->
	                <span class="{class_name}" id="{real_name}">
	                    {more_description}
	                </span>
	                <!--EndSezVGalleryDescriptionNodeNoImageNoLink-->
	                <!--BeginSezVGalleryObject-->
	                {object}
	                <!--EndSezVGalleryObject-->
	            <!--EndSezVGalleryDescriptionNode-->';
$item_desc_gallery = '<!--BeginSezGalleryWordName-->
                        <label class="gallery_label">{_gallery_name}</label>
                        {name}
                        <!--EndSezGalleryWordName-->
                        <!--BeginSezGalleryWordDescription-->
                        <label class="gallery_label">{_gallery_description}</label>
                        {description}
                        <!--EndSezGalleryWordDescription-->
                        <!--BeginSezGalleryWordType-->
                        <label class="gallery_label">{_gallery_type}</label>
                        {type}
                        <!--EndSezGalleryWordType-->
                        <!--BeginSezGalleryWordSize-->
                        <label class="gallery_label">{_gallery_size}</label>
                        {size}
                        <!--EndSezGalleryWordSize-->
                        <!--BeginSezGalleryWordPath-->
                        <label class="gallery_label">{_gallery_path}</label>
                        {path}
                        <!--EndSezGalleryWordPath-->
                        <!--BeginSezGalleryWordDescriptionLanguage-->
                        <label class="gallery_label">{_gallery_descriptionlanguage}</label>
                        {descriptionlanguage}
                        <!--EndSezGalleryWordDescriptionLanguage-->
                        <!--BeginSezGalleryWordFileTime-->
                        <label class="gallery_label">{_gallery_filetime}</label>
                        {filetime}
                        <!--EndSezGalleryWordFileTime-->
                        {ecommerce_cart}';

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
		            	$type_mode = strtolower(preg_replace('/[^a-zA-Z0-9\_]/', '', $component->form_fields["name"]->getValue()));
		            	
						$real_vgallery_file = $base_file . "/vgallery/vgallery_" . $type_mode . ".html";
						$real_vgallery_rel_file = $base_file . "/vgallery/vgallery_" . $type_mode . "_rel" . ".html";
						$real_gallery_file = $base_file . "/gallery/gallery_" . $type_mode . ".html";
						$real_gallery_rel_file = $base_file . "/gallery/gallery_" . $type_mode . "_rel" . ".html";
						
		            	if($action == "confirmdelete" || (isset($component->form_fields["use_default"]) && $component->form_fields["use_default"]->getValue() > 0)) {
		            		if(check_function("fs_operation")) {
		            			ftp_purge_dir($conn_id, $real_ftp_path, $real_vgallery_file, FF_DISK_PATH);
		            			ftp_purge_dir($conn_id, $real_ftp_path, $real_vgallery_rel_file, FF_DISK_PATH);
		            			ftp_purge_dir($conn_id, $real_ftp_path, $real_gallery_file, FF_DISK_PATH);
		            			ftp_purge_dir($conn_id, $real_ftp_path, $real_gallery_rel_file, FF_DISK_PATH);
							}
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
										if(!@ftp_chdir($conn_id, $real_ftp_path . $base_file . "/vgallery")) {
											if(@ftp_mkdir($conn_id, $real_ftp_path . $base_file . "/vgallery")) {
												if(@ftp_chmod($conn_id, 0775, $conn_id, $real_ftp_path . $base_file . "/vgallery") === false) {
													if(@chmod(FF_DISK_PATH . $base_file . "/vgallery", 0775) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											} else {
												$strError .= ffTemplate::_get_word_by_code("unable_create_dir") . " (/contents/vgallery)\n";
											}
										}
										if(!@ftp_chdir($conn_id, $real_ftp_path . $base_file . "/gallery")) {
											if(@ftp_mkdir($conn_id, $real_ftp_path . $base_file . "/gallery")) {
												if(@ftp_chmod($conn_id, 0775, $conn_id, $real_ftp_path . $base_file . "/gallery") === false) {
													if(@chmod(FF_DISK_PATH . $base_file . "/gallery", 0775) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											} else {
												$strError .= ffTemplate::_get_word_by_code("unable_create_dir") . " (/contents/gallery)\n";
											}
										}

										$content = $component->form_fields["content"]->getValue();

										$arr_content = explode("\n", $content);
										$vgallery_new["container"] = "";
										$vgallery_new["admin_ajax"] = $admin_ajax;
										$vgallery_new["container_title"] = "";
										$vgallery_new["error_top"] = "";
										$vgallery_new["vgallery_start"] = "<!--BeginSezVGallerys-->";
										$vgallery_new["pagenavigator_top"] = "";
										$vgallery_new["controls"] = $controls;
										$vgallery_new["begin_header"] = "";
										$vgallery_new["header_controls"] = $header_controls;
										$vgallery_new["header_title"] = "";
										$vgallery_new["header_items"] = "";
										$vgallery_new["end_header"] = "<!--EndSezSort-->";
										$vgallery_new["row"] = "";
										$vgallery_new["item"] = "";
										$vgallery_new["item_admin_ajax"] = $item_admin_ajax;
										$vgallery_new["field_img"] = "";
										$vgallery_new["end_field_img"] = "<!--EndSezVGalleryImage-->";
										$vgallery_new["field_desc"] = "";
										$vgallery_new["end_field_desc"] = "<!--EndSezVGalleryDescription-->";
										$vgallery_new["field_last_update"] = "";
										$vgallery_new["end_item"] = "<!--EndSezVGallery-->";
										$vgallery_new["end_row"] = "<!--EndSezVGalleryRow-->";
										$vgallery_new["pagenavigator_bottom"] = "";
										$vgallery_new["vgallery_end"] = "<!--EndSezVGallerys-->";
										$vgallery_new["error_bottom"] = "";

										$gallery_new["container"] = "";
										$gallery_new["admin_ajax"] = $admin_ajax;
										$gallery_new["container_title"] = "";
										$gallery_new["error_top"] = "";
										$gallery_new["gallery_start"] = "<!--BeginSezGallerys-->";
										$gallery_new["pagenavigator_top"] = "";
										$gallery_new["controls"] = $controls;
										$gallery_new["begin_header"] = "";
										$gallery_new["header_controls"] = $header_controls_gallery;
										$gallery_new["header_title"] = "";
										$gallery_new["header_items"] = "";
										$gallery_new["end_header"] = "<!--EndSezSort-->";
										$gallery_new["row"] = "";
										$gallery_new["item"] = "";
										$gallery_new["item_admin_ajax"] = $item_admin_ajax_gallery;
										$gallery_new["field_img"] = "";
										$gallery_new["end_field_img"] = "<!--EndSezGalleryImage-->";
										$gallery_new["field_desc"] = "";
										$gallery_new["end_field_desc"] = "<!--EndSezGalleryWord-->";
										$gallery_new["field_last_update"] = "";
										$gallery_new["end_item"] = "<!--EndSezGallery-->";
										$gallery_new["end_row"] = "<!--EndSezGalleryRow-->";
										$gallery_new["pagenavigator_bottom"] = "";
										$gallery_new["gallery_end"] = "<!--EndSezGallerys-->";
										$gallery_new["error_bottom"] = "";
										
										if(is_array($arr_content) && count($arr_content)) {
											$count = 0;
											$last_new_key = "";
											foreach($arr_content AS $arr_key => $arr_value) {
												if(strlen($arr_value)) {
													$key_is_set = false;
													if(strpos($arr_value, "[container]") !== false) {
														$vgallery_new["container"] = str_replace("[container]", 'block{block_class} vgallery_' . $type_mode . ' {class_vgname}" id="{real_father}', $arr_value);
														$gallery_new["container"] = str_replace("[container]", 'block{block_class} gallery_' . $type_mode . '" id="{real_father}', $arr_value);
														$last_new_key = "container";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[container_title]") !== false) {
														$vgallery_new["container_title"] = "<!--BeginSezVGalleryTitle-->\n" . str_replace("[container_title]", "{name_title}", $arr_value) . "<!--EndSezVGalleryTitle-->";
														$gallery_new["container_title"] = "<!--BeginSezGalleryTitle-->\n" . str_replace("[container_title]", "{name_title}", $arr_value) . "<!--EndSezGalleryTitle-->";
														$last_new_key = "container_title";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[error_top]") !== false) {
														$vgallery_new["error_top"] = "<!--BeginSezError-->\n" . str_replace("[error_top]", "{strError}", $arr_value) . "<!--EndSezError-->";
														$gallery_new["error_top"] = "<!--BeginSezError-->\n" . str_replace("[error_top]", "{strError}", $arr_value) . "<!--EndSezError-->";
														$last_new_key = "error_top";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[pagenavigator_top]") !== false) {
														$vgallery_new["pagenavigator_top"] = "<!--BeginSezPageNavigator-->\n" . str_replace("[pagenavigator_top]", "{PageNavigator}", $arr_value) . "<!--EndSezPageNavigator-->";
														$gallery_new["pagenavigator_top"] = "<!--BeginSezPageNavigator-->\n" . str_replace("[pagenavigator_top]", "{PageNavigator}", $arr_value) . "<!--EndSezPageNavigator-->";
														$last_new_key = "pagenavigator_top";
														$key_is_set = true;
													}
													
													if(strpos($arr_value, "[header]") !== false) {
														$vgallery_new["begin_header"] = "<!--BeginSezSort-->\n" . str_replace("[header]", "vgallery_sort", $arr_value);
														$gallery_new["begin_header"] = "<!--BeginSezSort-->\n" . str_replace("[header]", "gallery_sort", $arr_value);
														$last_new_key = "begin_header";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[header_title]") !== false) {
														$vgallery_new["header_title"] = "<!--BeginSezSortTitle-->\n" . str_replace("[header_title]", "{sort_type_title}", $arr_value) . "<!--EndSezSortTitle-->";
														$gallery_new["header_title"] = "<!--BeginSezSortTitle-->\n" . str_replace("[header_title]", "{sort_type_title}", $arr_value) . "<!--EndSezSortTitle-->";
														$last_new_key = "header_title";
														$key_is_set = true;
													}
													
													if(strpos($arr_value, "[header_items]") !== false) {
														$vgallery_new["header_items"] = "<!--BeginSezSortItems-->\n" . str_replace("[header_items]", $header_items, $arr_value) . "<!--EndSezSortItems-->";
														$gallery_new["header_items"] = "<!--BeginSezSortItems-->\n" . str_replace("[header_items]", $header_items_gallery, $arr_value) . "<!--EndSezSortItems-->";
														$last_new_key = "header_items";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[row]") !== false) {
														$vgallery_new["row"] = "<!--BeginSezVGalleryRow-->\n" . str_replace("[row]", "vgallery_row {vg_switch_style}", $arr_value);
														$gallery_new["row"] = "<!--BeginSezGalleryRow-->\n" . str_replace("[row]", "gallery_row {g_switch_style}", $arr_value);
														$last_new_key = "row";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[item]") !== false) {
														if(strlen($vgallery_new["row"])) {
															$vgallery_new["item"] = "<!--BeginSezVGallery-->\n" . str_replace("[item]", 'vgallery_item" id="{real_name}', $arr_value);
														} else {
															$vgallery_new["item"] = "<!--BeginSezVGallery-->\n" . str_replace("[item]", 'vgallery_item {vg_switch_style}" id="{real_name}', $arr_value);
														}
														if(strlen($gallery_new["row"])) {
															$gallery_new["item"] = "<!--BeginSezGallery-->\n" . str_replace("[item]", 'gallery_item" id="{real_name}', $arr_value);
														} else {
															$gallery_new["item"] = "<!--BeginSezGallery-->\n" . str_replace("[item]", 'gallery_item {g_switch_style}" id="{real_name}', $arr_value);
														}
														
														$last_new_key = "item";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[item_img]") !== false) {
														if(strlen($vgallery_new["field_img"])) {
															if(strpos($vgallery_new["field_img"], "[item_img]")) {
																$vgallery_new["field_img"] = str_replace("[item_img]", $item_img, $vgallery_new["field_img"]);
															} else {
																$vgallery_new["field_img"] = $vgallery_new["field_img"] . str_replace("[item_img]", $item_img, $arr_value);
															}
														} else
															$vgallery_new["field_img"] = str_replace("[item_img]", $item_img, $arr_value);

														if(strlen($gallery_new["field_img"])) {
															if(strpos($gallery_new["field_img"], "[item_img]")) {
																$gallery_new["field_img"] = str_replace("[item_img]", $item_img_gallery, $gallery_new["field_img"]);
															} else {
																$gallery_new["field_img"] = $gallery_new["field_img"] . str_replace("[item_img]", $item_img_gallery, $arr_value);
															}
														} else
															$gallery_new["field_img"] = str_replace("[item_img]", $item_img_gallery, $arr_value);


														$last_new_key = "field_img";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[field_img]") !== false) {
														if(strlen($vgallery_new["field_img"])) {
															if(strpos($vgallery_new["field_img"], "[field_img]")) {
																$vgallery_new["field_img"] = "<!--BeginSezVGalleryImage-->\n" . str_replace("[field_img]", 'vgallery_image" id="{real_name}', $vgallery_new["field_img"]);
															} else {
																$vgallery_new["field_img"] = "<!--BeginSezVGalleryImage-->\n" . str_replace("[field_img]", 'vgallery_image" id="{real_name}', $arr_value) . $vgallery_new["field_img"];
															}
														} else
															$vgallery_new["field_img"] = "<!--BeginSezVGalleryImage-->\n" . str_replace("[field_img]", 'vgallery_image" id="{real_name}', $arr_value);
															
														if(strlen($gallery_new["field_img"])) {
															if(strpos($gallery_new["field_img"], "[field_img]")) {
																$gallery_new["field_img"] = "<!--BeginSezGalleryImage-->\n" . str_replace("[field_img]", 'gallery_image" id="{real_name}', $gallery_new["field_img"]);
															} else {
																$gallery_new["field_img"] = "<!--BeginSezGalleryImage-->\n" . str_replace("[field_img]", 'gallery_image" id="{real_name}', $arr_value) . $gallery_new["field_img"];
															}
														} else
															$gallery_new["field_img"] = "<!--BeginSezGalleryImage-->\n" . str_replace("[field_img]", 'gallery_image" id="{real_name}', $arr_value);

														$last_new_key = "field_img";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[item_desc]") !== false) {
														if(strlen($vgallery_new["field_desc"])) {
															if(strpos($vgallery_new["field_desc"], "[item_desc]")) {
																$vgallery_new["field_desc"] = str_replace("[item_desc]", $item_desc, $vgallery_new["field_desc"]);
															} else {
																$vgallery_new["field_desc"] = $vgallery_new["field_desc"] . str_replace("[item_desc]", $item_desc, $arr_value);
															}
														} else
															$vgallery_new["field_desc"] = str_replace("[item_desc]", $item_desc, $arr_value);

														if(strlen($gallery_new["field_desc"])) {
															if(strpos($gallery_new["field_desc"], "[item_desc]")) {
																$gallery_new["field_desc"] = str_replace("[item_desc]", $item_desc_gallery, $gallery_new["field_desc"]);
															} else {
																$gallery_new["field_desc"] = $gallery_new["field_desc"] . str_replace("[item_desc]", $item_desc_gallery, $arr_value);
															}
														} else
															$gallery_new["field_desc"] = str_replace("[item_desc]", $item_desc_gallery, $arr_value);

														$last_new_key = "field_desc";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[field_desc]") !== false) {
														if(strlen($vgallery_new["field_desc"])) {
															if(strpos($vgallery_new["field_desc"], "[field_desc]")) {
																$vgallery_new["field_desc"] = "<!--BeginSezVGalleryDescription-->\n" . str_replace("[field_desc]", 'vgallery_description {class_plugin}" id="{real_name}', $vgallery_new["field_desc"]);
															} else {
																$vgallery_new["field_desc"] = "<!--BeginSezVGalleryDescription-->\n" . str_replace("[field_desc]", 'vgallery_description {class_plugin}" id="{real_name}', $arr_value) . $vgallery_new["field_desc"];
															}
														} else
															$vgallery_new["field_desc"] = "<!--BeginSezVGalleryDescription-->\n" . str_replace("[field_desc]", 'vgallery_description {class_plugin}" id="{real_name}', $arr_value);

														if(strlen($gallery_new["field_desc"])) {
															if(strpos($gallery_new["field_desc"], "[field_desc]")) {
																$gallery_new["field_desc"] = "<!--BeginSezGalleryWord-->\n" . str_replace("[field_desc]", 'gallery_description" id="{real_name}', $gallery_new["field_desc"]);
															} else {
																$gallery_new["field_desc"] = "<!--BeginSezGalleryWord-->\n" . str_replace("[field_desc]", 'gallery_description" id="{real_name}', $arr_value) . $gallery_new["field_desc"];
															}
														} else
															$gallery_new["field_desc"] = "<!--BeginSezGalleryWord-->\n" . str_replace("[field_desc]", 'gallery_description" id="{real_name}', $arr_value);
															
														$last_new_key = "field_desc";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[field_last_update]") !== false) {
														$vgallery_new["field_last_update"] = "<!--BeginSezVGalleryDate-->\n" . str_replace("[field_last_update]", "{last_update}", $arr_value) . "<!--EndSezVGalleryDate-->";
														$last_new_key = "field_last_update";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[pagenavigator_bottom]") !== false) {
														$vgallery_new["pagenavigator_bottom"] = "<!--BeginSezPageNavigator-->\n" . str_replace("[pagenavigator_bottom]", "{PageNavigator}", $arr_value) . "<!--EndSezPageNavigator-->";
														$gallery_new["pagenavigator_bottom"] = "<!--BeginSezPageNavigator-->\n" . str_replace("[pagenavigator_bottom]", "{PageNavigator}", $arr_value) . "<!--EndSezPageNavigator-->";
														$last_new_key = "pagenavigator_bottom";
														$key_is_set = true;
													}

													if(strpos($arr_value, "[error_bottom]") !== false) {
														$vgallery_new["error_bottom"] = "<!--BeginSezError-->\n" . str_replace("[error_bottom]", "{strError}", $arr_value) . "<!--EndSezError-->";
														$gallery_new["error_bottom"] = "<!--BeginSezError-->\n" . str_replace("[error_bottom]", "{strError}", $arr_value) . "<!--EndSezError-->";
														$last_new_key = "error_bottom";
														$key_is_set = true;
													}

													
													if(!$key_is_set) {
														$vgallery_new[$last_new_key] = $vgallery_new[$last_new_key] . "\n" . $arr_value;
														$gallery_new[$last_new_key] = $gallery_new[$last_new_key] . "\n" . $arr_value;

														$tmp_keys = array_keys($vgallery_new);
														$tmp_position = array_search($last_new_key, $tmp_keys);
														if (isset($tmp_keys[$tmp_position + 1]) && strpos($tmp_keys[$tmp_position + 1], "end_") !== false) {
														    $last_new_key = $tmp_keys[$tmp_position + 1];
														}
													}

													$count++;
												}
											}
											
											foreach($vgallery_new AS $vgallery_new_key => $vgallery_new_value) {
												if(strlen($vgallery_new_value)) {
													if($vgallery_new_key != "container"
														&& $vgallery_new_key != "container_title"
														&& $vgallery_new_key != "begin_header"
														&& $vgallery_new_key != "header_controls"
														&& $vgallery_new_key != "header_title"
														&& $vgallery_new_key != "header_items"
														&& $vgallery_new_key != "end_header"
													) {
														$vgallery_new_rel[$vgallery_new_key] = $vgallery_new_value;
													}
												} else {
													unset($vgallery_new[$vgallery_new_key]);
												}
											}
											if(isset($vgallery_new["container"])) {
												end($vgallery_new_rel);
												$vgallery_new_rel[key($vgallery_new_rel)] = substr($vgallery_new_rel[key($vgallery_new_rel)], 0, strrpos($vgallery_new_rel[key($vgallery_new_rel)], "-->") + 3);
											}
											$vgallery_content = implode("\n", $vgallery_new);
											$vgallery_rel_content = implode("\n", $vgallery_new_rel);

											//write vgallery_file
											$handle = @tmpfile();
											@fwrite($handle, $vgallery_content);
											@fseek($handle, 0);
											if(!@ftp_fput($conn_id, $real_ftp_path . $real_vgallery_file, $handle, FTP_ASCII)) {
												$strError = ffTemplate::_get_word_by_code("unable_write_file");
											} else {
												if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $real_vgallery_file) === false) {
													if(@chmod(FF_DISK_PATH . $real_vgallery_file, 0644) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											}
											@fclose($handle);

											//write vgallery_rel_file
											$handle = @tmpfile();
											@fwrite($handle, $vgallery_rel_content);
											@fseek($handle, 0);
											if(!@ftp_fput($conn_id, $real_ftp_path . $real_vgallery_rel_file, $handle, FTP_ASCII)) {
												$strError = ffTemplate::_get_word_by_code("unable_write_file");
											} else {
												if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $real_vgallery_rel_file) === false) {
													if(@chmod(FF_DISK_PATH . $real_vgallery_rel_file, 0644) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											}
											@fclose($handle);

											foreach($gallery_new AS $gallery_new_key => $gallery_new_value) {
												if(strlen($gallery_new_value)) {
													if($gallery_new_key != "container"
														&& $gallery_new_key != "container_title"
														&& $gallery_new_key != "begin_header"
														&& $gallery_new_key != "header_controls"
														&& $gallery_new_key != "header_title"
														&& $gallery_new_key != "header_items"
														&& $gallery_new_key != "end_header"
													) {
														$gallery_new_rel[$gallery_new_key] = $gallery_new_value;
													}
												} else {
													unset($gallery_new[$gallery_new_key]);
												}
											}
											if(isset($gallery_new["container"])) {
												end($gallery_new_rel);
												$gallery_new_rel[key($gallery_new_rel)] = substr($gallery_new_rel[key($gallery_new_rel)], 0, strrpos($gallery_new_rel[key($gallery_new_rel)], "-->") + 3);
											}
											$gallery_content = implode("\n", $gallery_new);
											$gallery_rel_content = implode("\n", $gallery_new_rel);

											//write gallery_file
											$handle = @tmpfile();
											@fwrite($handle, $gallery_content);
											@fseek($handle, 0);
											if(!@ftp_fput($conn_id, $real_ftp_path . $real_gallery_file, $handle, FTP_ASCII)) {
												$strError = ffTemplate::_get_word_by_code("unable_write_file");
											} else {
												if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $real_gallery_file) === false) {
													if(@chmod(FF_DISK_PATH . $real_gallery_file, 0644) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											}
											@fclose($handle);

											//write gallery_rel_file
											$handle = @tmpfile();
											@fwrite($handle, $gallery_rel_content);
											@fseek($handle, 0);
											if(!@ftp_fput($conn_id, $real_ftp_path . $real_gallery_rel_file, $handle, FTP_ASCII)) {
												$strError = ffTemplate::_get_word_by_code("unable_write_file");
											} else {
												if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $real_gallery_rel_file) === false) {
													if(@chmod(FF_DISK_PATH . $real_gallery_rel_file, 0644) === false) {
														$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
													}
												}
											}
											@fclose($handle);
										}
										break;
									default:
								}
								$file_chmod = "644";
								//permission vgallery_file
								
								if(substr(decoct( @fileperms(FF_DISK_PATH . $real_vgallery_file)), 3) != $file_chmod) {
									$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
							        if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $real_vgallery_file) === false) {
						            	if(@chmod(FF_DISK_PATH . $real_vgallery_file, $file_chmod) === false) {
				            				$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
										}
									}
								}
								//permission vgallery_rel_file
								if(substr(decoct( @fileperms(FF_DISK_PATH . $real_vgallery_rel_file)), 3) != $file_chmod) {
									$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
							        if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $real_vgallery_rel_file) === false) {
						            	if(@chmod(FF_DISK_PATH . $real_vgallery_rel_file, $file_chmod) === false) {
				            				$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
										}
									}
								}
								//permission gallery_file
								if(substr(decoct( @fileperms(FF_DISK_PATH . $real_gallery_file)), 3) != $file_chmod) {
									$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
							        if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $real_gallery_file) === false) {
						            	if(@chmod(FF_DISK_PATH . $real_gallery_file, $file_chmod) === false) {
				            				$strError = ffTemplate::_get_word_by_code("unavailable_change_permission");
										}
									}
								}
								//permission gallery_rel_file
								if(substr(decoct( @fileperms(FF_DISK_PATH . $real_gallery_rel_file)), 3) != $file_chmod) {
									$file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
							        if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $real_gallery_rel_file) === false) {
						            	if(@chmod(FF_DISK_PATH . $real_gallery_rel_file, $file_chmod) === false) {
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
	                    `layout`.`last_update` = (SELECT `settings_thumb_mode`.last_update FROM settings_thumb_mode WHERE settings_thumb_mode.ID = " . $db->toSql($component->key_fields["ID"]->value) . ") 
	                WHERE 
	                    (
	                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("VIRTUAL_GALLERY") . ")
	                    )
	                    OR
	                    (
	                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("GALLERY") . ")
	                    )
	                    OR
	                    (
	                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("PUBLISHING") . ")
	                    )
	                    ";
	        $db->execute($sSQL);
		}
    }
}
?>
