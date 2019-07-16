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
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */

    if (!Auth::env("AREA_IMPORT_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

	$globals = ffGlobals::getInstance("gallery");

	$cm->oPage->addContent(null, true, "import", array("tab" => "top")); 

  

    if(check_function("import")) {
		// -------------------------
		// check key & form url
		$globals = ffGlobals::getInstance("wizard");
		$globals->var_prefix = "wizcsv_";

		$globals->transit_params = $cm->oPage->get_globals() . "ret_url=" . rawurlencode($_REQUEST["ret_url"]);
		// -------------------------

	    if(basename($cm->real_path_info))
	    	$target_table = $cm->real_path_info;
		elseif(get_session("importcsvtarget"))
	    	$target_table = get_session("importcsvtarget");

	    if($_REQUEST["node"] > 0)
	    	$target_node = $_REQUEST["node"];
		elseif(get_session("importcsvnode"))
	    	$target_node = get_session("importcsvnode");
    		
		$oRecord = ffRecord::factory($cm->oPage);
		$oRecord->id = "ImportCSV";
		//$oRecord->title = ffTemplate::_get_word_by_code("wizcsv_importcsv_title");
		$oRecord->addEvent("on_do_action", "ImportCSV_on_do_action");
		$oRecord->buttons_options["cancel"]["display"] = false;
		$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("wizcsv_step2");

		$menu = '
		<div class="import-type ' . Cms::getInstance("frameworkcss")->get(array(6), "col"). '">
			<h2>' . ffTemplate::_get_word_by_code("import_tool") . '</h2>
			<ul>
				<li><a href="{import_path}/anagraph">{_import_anagraph}</a></li>
				<li><a href="{import_path}/vgallery">{_import_vgallery}</a></li>
				<li><a href="{import_path}/ecommerce">{_import_ecommerce}</a></li>
				<li><a href="{import_path}/orders">{_import_order}</a></li>
				<li><a href="{import_path}/orders_detail">{_import_order_detail}</a></li>
				<li><a href="{import_path}/orders_payments">{_import_billing}</a></li>
				<li><a href="{import_path}/bill">{_import_bill}</a></li>
				<li><a href="{import_path}/bill_detail">{_import_bill_detail}</a></li>
				<li><a href="{import_path}/bill_payments">{_import_billing}</a></li>
			</ul>   
		</div>';
		$oRecord->fixed_pre_content = $menu;

		
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "upload";
		$oField->label  = ffTemplate::_get_word_by_code("wizcsv_source_file");
		$oField->required = true;
		$oField->file_show_delete = false;
		$oField->extended_type = "File";
		$oField->control_type = "file";
		$oField->file_temp_path = FF_DISK_UPDIR . "/importcsv";
		$oField->file_storing_path = FF_DISK_UPDIR . "/importcsv";
		$oField->file_full_path = false;
		$oField->file_check_exist = false;
		$oField->file_saved_view_url		= CM_SHOWFILES . "/importcsv/[_FILENAME_]";
		$oField->file_saved_preview_url		= CM_SHOWFILES . "/thumb/importcsv/[_FILENAME_]";
		$oField->file_temp_view_url			= CM_SHOWFILES . "/importcsv/[_FILENAME_]";
		$oField->file_temp_preview_url		= CM_SHOWFILES . "/thumb/importcsv[_FILENAME_]";
		$oField->widget = "uploadify";
		if (check_function("set_field_uploader")) {
            $oField = set_field_uploader($oField);
        }
		$oField->file_max_size = "10000000";
		$oField->default_value = new ffData(get_session("importcsv"));
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "sep_field";
		$oField->label  = ffTemplate::_get_word_by_code("wizcsv_field_separated_by");
		$oField->extended_type = "Selection";
		$oField->multi_pairs = array(
		                                  array( new ffData("\t"),  new ffData(ffTemplate::_get_word_by_code("wizcsv_sep_tab")))
		                                , array( new ffData(";"),  new ffData(ffTemplate::_get_word_by_code("wizcsv_sep_semicolon")))
		                                , array( new ffData(","),  new ffData(ffTemplate::_get_word_by_code("wizcsv_sep_comma")))
		                            );
		$oField->default_value = new ffData("\t");
		$oField->required = true;
		$oField->default_value = new ffData(get_session("importcsvsep"));
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField);
		
		$oField = ffField::factory($cm->oPage);
		$oField->id = "table";
		$oField->label  = ffTemplate::_get_word_by_code("wizcsv_target_table");
		$oField->widget = "actex";
		$oField->multi_pairs = get_importcsv_def("table_multi_pairs");
		$oField->required = true;
		$oField->actex_update_from_db = true;
		//$oField->actex_child = "node";
		$oField->default_value = new ffData($target_table);
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField);

		$oField = ffField::factory($cm->oPage);
		$oField->id = "node";
		$oField->label  = ffTemplate::_get_word_by_code("wizcsv_target_type");
		$oField->widget = "actex";
		$oField->multi_pairs = get_importcsv_def("node_multi_pairs");
		$oField->required = true;
		$oField->actex_update_from_db = true;
		//$oField->actex_father = "table";
		//$oField->actex_related_field = "type"; 
		$oField->default_value = new ffData($target_node);
		$oField->setWidthComponent(6);
		$oRecord->addContent($oField);


		$cm->oPage->addContent($oRecord, "import", null, array("title" => ffTemplate::_get_word_by_code("csv")));


		function ImportCSV_on_do_action($oRecord, $frmAction)
		{
			if (strlen($frmAction))
			{
				$cm = cm::getInstance();

				$tmpfile = $oRecord->form_fields["upload"]->file_tmpname;
				if(!$tmpfile)
					$tmpfile = $oRecord->form_fields["upload"]->default_value->getValue();

				if (ffMedia::getMimeTypeByFilename(FF_DISK_UPDIR . "/importcsv/" . $tmpfile) != "text/plain"
					&& strpos(ffMedia::getMimeTypeByFilename(FF_DISK_UPDIR . "/importcsv/" . $tmpfile), "text/x-c") === false
				) {
					$oRecord->tplDisplayError(ffTemplate::_get_word_by_code("wizcsv_file_mimetype_not_supported"));
					return true;
				}
				if($oRecord->form_fields["upload"]->file_tmpname && is_file(FF_DISK_UPDIR . "/importcsv/" . $oRecord->form_fields["upload"]->file_tmpname)) {
					$file_data = file_get_contents(FF_DISK_UPDIR . "/importcsv/" . $oRecord->form_fields["upload"]->file_tmpname);
					$file_encoding = mb_detect_encoding($file_data);
					if($file_encoding && $file_encoding != 'utf-8') {
						$fc = iconv($file_encoding, 'utf-8//ignore', $file_data); 
				        file_put_contents(FF_DISK_UPDIR . "/importcsv/" . $oRecord->form_fields["upload"]->file_tmpname, $fc, LOCK_EX);
					}
				}

				set_session("importcsv", $tmpfile);
		        set_session("importcsvsep", $oRecord->form_fields["sep_field"]->getValue());
				set_session("importcsvtarget", $oRecord->form_fields["table"]->getValue());
				set_session("importcsvnode", $oRecord->form_fields["node"]->getValue());
		        set_session("importcsvpage", 1);
		        set_session("importcsvlimit", 100);
		        set_session("importcsvref", time());
		        set_session("importcsvlinetotal", 0);
		        set_session("importcsvlineprocessed", 0);

				ffRedirect(FF_SITE_PATH . $cm->oPage->page_path . "/csv?" . $cm->oPage->get_globals());
			}
		}
	}
	
	
	$oRecord = ffRecord::factory($cm->oPage);
	$oRecord->id = "WP";
	//$oRecord->title = ffTemplate::_get_word_by_code("wizcsv_importcsv_title");
	$oRecord->addEvent("on_do_action", "WP_on_do_action");
	$oRecord->buttons_options["cancel"]["display"] = false;
	$oRecord->buttons_options["insert"]["label"] = ffTemplate::_get_word_by_code("wizcsv_step2");
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "wp_host";
	$oField->label  = ffTemplate::_get_word_by_code("db_host");
	$oField->default_value = new ffData(get_session("importwphost"));
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "wp_db";
	$oField->label  = ffTemplate::_get_word_by_code("db_name");
	$oField->default_value = new ffData(get_session("importwpdb"));
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "wp_user";
	$oField->label  = ffTemplate::_get_word_by_code("db_user");
	$oField->default_value = new ffData(get_session("importwpuser"));
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "wp_pw";
	$oField->label  = ffTemplate::_get_word_by_code("db_pw");
	$oField->default_value = new ffData(get_session("importwppw"));
	$oRecord->addContent($oField);
	
	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_vgallery";
	$oField->label = ffTemplate::_get_word_by_code("import_vgallery_name");
	$oField->extended_type = "Selection";
    $oField->source_SQL = "
                           SELECT
                                vgallery.ID
                                , vgallery.name
                           FROM
                                vgallery
                           WHERE vgallery.status > 0
                           	AND vgallery.public = 0";	
	$oField->default_value = new ffData(get_session("importwpvg"));
	$oField->setWidthComponent(6);
	$oRecord->addContent($oField);

	$oField = ffField::factory($cm->oPage);
	$oField->id = "ID_vgallery_type";
	$oField->label = ffTemplate::_get_word_by_code("import_vgallery_type_name");
	$oField->extended_type = "Selection";
    $oField->source_SQL = "
                           SELECT
                                vgallery_type.ID
                                , vgallery_type.name
                           FROM
                                vgallery_type
                           WHERE 1";
	$oField->default_value = new ffData(get_session("importwpvgtype"));
	$oField->setWidthComponent(6);
	$oRecord->addContent($oField);
	
  	$cm->oPage->addContent($oRecord, "import", null, array("title" => ffTemplate::_get_word_by_code("wp")));
  	
  	
	function WP_on_do_action($oRecord, $frmAction)
	{
		if (strlen($frmAction))
		{
			$cm = cm::getInstance();
			$globals = ffGlobals::getInstance("wizard");

			set_session("importwphost", $oRecord->form_fields["wp_host"]->getValue());
		    set_session("importwpdb", $oRecord->form_fields["wp_db"]->getValue());
			set_session("importwpuser", $oRecord->form_fields["wp_user"]->getValue());
			set_session("importwppw", $oRecord->form_fields["wp_pw"]->getValue());
			
			set_session("importwpvg", $oRecord->form_fields["ID_vgallery"]->getValue());
			set_session("importwpvgtype", $oRecord->form_fields["ID_vgallery_type"]->getValue());

			$db_wp = new ffDB_Sql();
			$db_wp->on_error = "ignore";
			if($db_wp->connect(get_session("importwpdb"), get_session("importwphost"), get_session("importwpuser"), get_session("importwppw"))) {
				$sSQL = "SELECT wp_postmeta.meta_key 
							, wp_postmeta.meta_value
						FROM wp_postmeta
							INNER JOIN wp_posts ON wp_posts.ID = wp_postmeta.post_id
						WHERE wp_posts.post_type = 'post'
							AND wp_posts.post_status = 'publish'
							AND LOCATE('_oembed', wp_postmeta.meta_key) = 0 
						GROUP BY wp_postmeta.meta_key
						ORDER BY wp_postmeta.meta_key";
				$db_wp->query($sSQL);
				if($db_wp->nextRecord()) {
					do {
						$globals->import_fields[$db_wp->getField("meta_key", "Text", true)] = $db_wp->getField("meta_value", "Text", true);
					} while($db_wp->nextRecord());
				}

				ffRedirect(FF_SITE_PATH . $cm->oPage->page_path . "/wp?" . $cm->oPage->get_globals());
			} else {
				$oRecord->tplDisplayError(ffTemplate::_get_word_by_code("connection_failed_to_database"));
				return true;
			}
		}
	}  	
