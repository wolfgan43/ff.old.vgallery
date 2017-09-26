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

if (!AREA_IMPORT_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}
// -------------------------
// check key & form url
$globals = ffGlobals::getInstance("wizard");
$globals->var_prefix = "wizcsv_";

$globals->transit_params = $cm->oPage->get_globals() . "ret_url=" . rawurlencode($_REQUEST["ret_url"]);
// -------------------------

$cm->oPage->form_method = "POST";

if(check_function("import")) {
	if($_REQUEST["importcsv"] == "continue") {
		if($_REQUEST["frmAction"]) {
			$arrRecord = get_importcsv_def("record", get_session("importcsvtarget"));
			
			if($_REQUEST["frmAction"] == $arrRecord[0]["ID"] . "_update") {
				echo ffCommon_jsonenc(array("close" => true, "refresh" => true, "resources" => $arrRecord[0]["resources"]), true);
				exit;
			}	
		}
		importcsv_open();
		importcsv_exec();
	} else {
		set_session("importcsvlineprocessed", "0");
		set_session("importcsvpage", 1);
		
		importcsv_open();
		// ----------------------------------
		//  BREADCRUMB
		if(is_file(FF_DISK_PATH . "/themes/" . $cm->oPage->getTheme() . "/contents/importcsv/breadcrumb.html")) {
			$tpl_bread = ffTemplate::factory(FF_DISK_PATH . "/themes/" . $cm->oPage->getTheme() . "/contents/importcsv");
			$tpl_bread->load_file("breadcrumb.html", "main");
			$tpl_bread->set_var("site_path", FF_SITE_PATH);
			$tpl_bread->set_var("theme", $cm->oPage->getTheme());
			$tpl_bread->set_var("query_string", $_SERVER["QUERY_STRING"]);

			$tpl_bread->set_var("selected_2", "wizbread_selected");

			$cm->oPage->addContent($tpl_bread);
		}
		// ----------------------------------



		$arrData = get_importcsv_fields($globals->import_fields[0]);

		$oRecord = ffRecord::factory($cm->oPage);
		$oRecord->id = $arrData["record"]["ID"];
		$oRecord->resources = $arrData["record"]["resources"];
		$oRecord->title = ffTemplate::_get_word_by_code("wizcsv_importfield_title");
		$oRecord->description = $arrData["record"]["description"];
		$oRecord->buttons_options["cancel"]["display"] = false;
		$oRecord->buttons_options["insert"]["display"] = false;
		$oRecord->skip_action = true;

		$oRecord->addEvent("on_do_action", "Step2_on_do_action");

		$oField = ffField::factory($cm->oPage);
		$oField->id = "skip_first_row";
		$oField->label = ffTemplate::_get_word_by_code("wizcsv_skip_first_row");
		$oField->base_type = "Number";
		$oField->extended_type = "Boolean";
		$oField->control_type = "checkbox";
		$oField->checked_value = new ffData("1", "Number");
		$oField->unchecked_value = new ffData("0", "Number");
		$oField->default_value = new ffData(get_session("importcsvskipfirstrow"), "Number");
		$oRecord->addContent($oField);

		$bt = ffButton::factory($cm->oPage);
		$bt->id = "back";
		$bt->label = ffTemplate::_get_word_by_code("wizcsv_back");
		$bt->action_type = "submit";
		$bt->frmAction = "back";
		$bt->aspect = "link";
		$oRecord->addActionButton($bt);

		$bt = ffButton::factory($cm->oPage);
		$bt->id = "ActionButtonInsert";
		$bt->label = ffTemplate::_get_word_by_code("wizcsv_run") . " (" . get_session("importcsvlinetotal") . ")";
		$bt->action_type = "submit";
		$bt->frmAction = "insert";
		$bt->aspect = "link";
		$oRecord->addActionButton($bt);

		$email_validator = ffValidator::getInstance("email");
		$importcsvfields = get_session("importcsvfields");
		
		$field_default = $importcsvfields[get_session("importcsvtarget") . "|" . get_session("importcsvnode")];
		
		for ($c = 0; $c < count($globals->import_fields[0]); $c++)
		{
			$oField = ffField::factory($cm->oPage);
			$oField->id = "field_" . $c;
			$oField->label = ffTemplate::_get_word_by_code("wizcsv_field") . " #" . ($c + 1);
			$oField->extended_type = "Selection";
			$oField->multi_pairs = $arrData["field"];
			$oField->multi_select_one_label = ffTemplate::_get_word_by_code("wizcsv_skip_import");
			$oField->fixed_post_content = "<p><label>" . ffTemplate::_get_word_by_code("wizcsv_field_example") . "</label>";
			$is_field_email = false;
			for ($v = ((int) $arrData["skip_first_col"]); $v < (3 + ((int) $arrData["skip_first_col"])); $v++)
			{
				if (strlen($globals->import_fields[$v][$c]))
				{
					if (false === $email_validator->checkValue(new ffData($globals->import_fields[$v][$c]), "", array()))
						$is_field_email = true;
					if ($v > ((int) $arrData["skip_first_col"]))
						$oField->fixed_post_content .= ", ";
					$oField->fixed_post_content .= $globals->import_fields[$v][$c];
				}
			}
			if(is_array($field_default) && count($field_default) && array_key_exists("field_" . $c, $field_default)) {
				$oField->default_value = new ffData($field_default["field_" . $c]);
			} elseif ($is_field_email) {
				$oField->default_value = new ffData("email");
			} else {
				$oField->default_value = new ffData(ffCommon_url_rewrite($globals->import_fields[0][$c]));
			}

			$oField->fixed_post_content .= "</p>";

			$oRecord->addContent($oField);
		}
		$cm->oPage->addContent($oRecord);
		
		$js = '
			function continueImportCSV(count, page) {
				if(jQuery(".' . $arrData["record"]["ID"] . 'Import").hasClass("hidden")) {
					jQuery("#' . $arrData["record"]["ID"] . '").closest(".ui-dialog").find("div.actions").remove();
					jQuery("#' . $arrData["record"]["ID"] . '").remove();
					jQuery(".' . $arrData["record"]["ID"] . 'Import").removeClass("hidden");
				}				
				jQuery(".' . $arrData["record"]["ID"] . 'Import .count").text(count + parseInt(jQuery(".' . $arrData["record"]["ID"] . 'Import .count").text())); 

				jQuery.getJSON("' . FF_SITE_PATH . $cm->oPage->page_path . "/step2?" . (isset($_REQUEST["XHR_CTX_ID"]) ? "XHR_CTX_ID=" . $_REQUEST["XHR_CTX_ID"] . "&" : "") . "importcsv=continue" . (isset($_REQUEST["ret_url"]) ? "&ret_url=" . rawurlencode($_REQUEST["ret_url"]) : "") . '", function(data) { 
					if(data.callback)
						eval(data.callback);
				}); 
			};';

		$cm->oPage->tplAddJs("ff.cms.admin.import", array(
			"embed" => $js
		));		
		$cm->oPage->addContent('<div class="' . $arrData["record"]["ID"] . 'Import hidden"><span class="count">' . get_session("importcsvlimit") . '</span> / <span class="total">' . get_session("importcsvlinetotal") . '</span></div>');
		
		
	}
	
	function Step2_on_do_action($oRecord, $frmAction)
	{
		$cm = cm::getInstance();

		switch ($frmAction)
		{
			case "insert":
				foreach ($oRecord->form_fields as $key => $value)
				{
					if(strpos($key, "field_") !== false) {
						$csv_rel_field[$key] = $value->getValue();
					}
				}
				
				$importcsvfields = get_session("importcsvfields");
				$importcsvfields[get_session("importcsvtarget") . "|" . get_session("importcsvnode")] = $csv_rel_field;
				
				set_session("importcsvfields", $importcsvfields);
				set_session("importcsvskipfirstrow", $oRecord->form_fields["skip_first_row"]->getValue());
				
				$res = importcsv_exec($csv_rel_field, $oRecord->form_fields["skip_first_row"]->getValue());
				if(strlen($res)) {
					$oRecord->tplDisplayError($res);
					return true;
				}	
				break;
			case "back":	
				ffRedirect(FF_SITE_PATH . $cm->oPage->page_path . "?" . $cm->oPage->get_globals());
				break;
		}
	}
}