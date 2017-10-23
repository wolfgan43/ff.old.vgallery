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
 	check_function("write");
 
 	$user_path = $cm->real_path_info;
 	
 	if(basename($user_path) == "empty") {
 		$import_file = "empty";
 	} elseif(is_file(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/xml/struct" . $user_path . ".xml")) {
 		$import_file = FF_THEME_DIR . "/" . FRONTEND_THEME . "/xml/struct" . $user_path . ".xml";
 	} elseif(is_file(__CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/xml/struct" . $user_path . ".xml")) {
 		$import_file = FF_THEME_DIR . "/" . THEME_INSET . "/xml/struct" . $user_path . ".xml";
 	}
	if($import_file && check_function("import")) {
		$imported = import_layout_structure($import_file);
	} 

	
	if($imported) 
	{
 		$ret_url 		= $_REQUEST["ret_url"];
		$description 	= $imported["description"] . '
			<script>
				setTimeout(' . ($ret_url
					? 'window.location.href="' . $ret_url . '"'
					: 'top.location.reload()'
				) . ', 2000);
			</script>';
	
		if(check_function("process_html_page_error")) 
			$tpl = process_html_notify("success", $imported["name"], $description, array("icon" =>  array("svg" => $imported["svg"])));
	
	
	
	}
	else
	{
 		$tpl = ffTemplate::factory(__CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/contents/import");
		$tpl->load_file("struct.html", "main");
		$tpl->set_var("container_class", cm_getClassByFrameworkCss("wizard-struct", "row"));

		$tpl->set_var("item_class", cm_getClassByFrameworkCss(array(12,12,4,4), "col")); 
		
		$res = glob(__CMS_DIR__ . FF_THEME_DIR . "/" . THEME_INSET . "/xml/struct/*");
		if(is_array($res) && count($res)) {
			foreach($res AS $file) {
				$arrStruct[basename($file)] = xml2array(new SimpleXMLElement($file, null, true));
			}
		} 
 		$res = glob(FF_DISK_PATH . FF_THEME_DIR . "/" . FRONTEND_THEME . "/xml/struct/*");
		if(is_array($res) && count($res)) {
			foreach($res AS $file) {
				$arrStruct[basename($file)] = xml2array(new SimpleXMLElement($file, null, true));
			}
		} 
		
		$svg_default = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiIFs8IUVOVElUWSBuc19mbG93cyAiaHR0cDovL25zLmFkb2JlLmNvbS9GbG93cy8xLjAvIj5dPjxzdmcgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczphPSJodHRwOi8vbnMuYWRvYmUuY29tL0Fkb2JlU1ZHVmlld2VyRXh0ZW5zaW9ucy8zLjAvIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjUwMHB4IiBoZWlnaHQ9IjUwMHB4IiB2aWV3Qm94PSIwLjUgMiA1MDAgNTAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAuNSAyIDUwMCA1MDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxkZWZzPjwvZGVmcz48Zz48cGF0aCBkPSJNNDk5LDEuNXY1MDBIMFYxLjVINDk5IE00ODksMTEuNUgxMHY0ODBoNDc5VjExLjVMNDg5LDExLjV6Ii8+PC9nPjxwYXRoIGRpc3BsYXk9Im5vbmUiIGQ9Ik01MDAuNSwwdjUwMS41SDAuNVYwSDUwMC41IE00OTAuNSwxMGgtNDgwdjQ4MS41aDQ4MFYxMEw0OTAuNSwxMHoiLz48cGF0aCBkPSJNMjM5Ljk3LDI5OS40NGMtMC4xNjgtOS4xNjQsMC40NTctMTcuNSwxLjg3NS0yNWMxLjQxNC03LjUsNS4zNzUtMTQuNjY0LDExLjg3NS0yMS41YzMuNjY0LTMuODMyLDcuNS03LjU4MiwxMS41LTExLjI1YzQtMy42NjQsNy42NjQtNy4zNzUsMTEtMTEuMTI1YzMuMzMyLTMuNzUsNi4xMjUtNy42MjUsOC4zNzUtMTEuNjI1czMuMzc1LTguMzMyLDMuMzc1LTEzYzAtNC41LTAuODc5LTguNzA3LTIuNjMzLTEyLjYyNWMtMS43NTgtMy45MTQtNC4zMDktNy4zMzItNy42NTItMTAuMjVjLTMuMzQ0LTIuOTE0LTcuNDQxLTUuMjA3LTEyLjI4OS02Ljg3NWMtNC44NTItMS42NjQtMTAuMzY3LTIuNS0xNi41NTEtMi41Yy02LjE4OCwwLTExLjcwNywxLjA4Ni0xNi41NTUsMy4yNWMtNC44NTIsMi4xNjgtOC45MDYsNS4xNjgtMTIuMTY0LDljLTMuMjYyLDMuODM2LTUuNzI3LDguMjkzLTcuMzk4LDEzLjM3NWMtMS42NzIsNS4wODYtMi40MjYsMTAuNjI1LTIuMjU4LDE2LjYyNWgtMTVjLTAuNjY4LTguMzMyLDAuMjUtMTUuOTE0LDIuNzUtMjIuNzVjMi41LTYuODMyLDYuMjA3LTEyLjcwNywxMS4xMjUtMTcuNjI1YzQuOTE0LTQuOTE0LDEwLjc4OS04LjcwNywxNy42MjUtMTEuMzc1YzYuODMyLTIuNjY0LDE0LjMzMi00LDIyLjUtNGM4LjUsMCwxNi4xMjUsMS4xMjUsMjIuODc1LDMuMzc1czEyLjQ1Nyw1LjQxOCwxNy4xMjUsOS41YzQuNjY0LDQuMDg2LDguMjUsOS4wNDMsMTAuNzUsMTQuODc1YzIuNSw1LjgzNiwzLjc1LDEyLjI1LDMuNzUsMTkuMjVjMCw3LjMzNi0xLjYyNSwxMy42MjUtNC44NzUsMTguODc1cy03LjIxMSwxMC4xMjUtMTEuODc1LDE0LjYyNWMtNC42NjgsNC41LTkuNSw5LjA0My0xNC41LDEzLjYyNWMtNSw0LjU4Ni05LjMzNiw5LjcxMS0xMywxNS4zNzVjLTIuODM2LDQuMzM2LTQuNDE4LDkuMTI1LTQuNzUsMTQuMzc1Yy0wLjMzNiw1LjI1LTAuNSwxMC4zNzUtMC41LDE1LjM3NUgyMzkuOTd6IE0yMzguNDcsMzQ0Ljk0di0yMmgxOC4yNXYyMkgyMzguNDd6Ii8+PC9zdmc+";
		
		$svg_empty = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48IURPQ1RZUEUgc3ZnIFBVQkxJQyAiLS8vVzNDLy9EVEQgU1ZHIDEuMS8vRU4iICJodHRwOi8vd3d3LnczLm9yZy9HcmFwaGljcy9TVkcvMS4xL0RURC9zdmcxMS5kdGQiIFs8IUVOVElUWSBuc19mbG93cyAiaHR0cDovL25zLmFkb2JlLmNvbS9GbG93cy8xLjAvIj5dPjxzdmcgdmVyc2lvbj0iMS4xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4bWxuczphPSJodHRwOi8vbnMuYWRvYmUuY29tL0Fkb2JlU1ZHVmlld2VyRXh0ZW5zaW9ucy8zLjAvIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjUwMHB4IiBoZWlnaHQ9IjUwMHB4IiB2aWV3Qm94PSIwLjUgMiA1MDAgNTAwIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAuNSAyIDUwMCA1MDAiIHhtbDpzcGFjZT0icHJlc2VydmUiPjxkZWZzPjwvZGVmcz48Zz48cGF0aCBkPSJNNDk5LDEuNWgtNzF2MTBoNjF2MjhoMTBWMS41TDQ5OSwxLjV6IE00MDEsMS41aC03MXYxMGg3MVYxLjVMNDAxLDEuNXogTTMwMywxLjVoLTcxdjEwaDcxVjEuNUwzMDMsMS41eiBNMjA1LDEuNWgtNzF2MTBoNzFWMS41TDIwNSwxLjV6IE0xMDcsMS41SDM2djEwaDcxVjEuNUwxMDcsMS41eiBNMTAsMS41SDB2MTBsMCwwYzAsMTcuNTM4LDAsNTIsMCw1MmgxMFYxLjVMMTAsMS41eiBNMTAsOTAuNUgwdjcxaDEwVjkwLjVMMTAsOTAuNXogTTEwLDE4OC41SDB2NzFoMTBWMTg4LjVMMTAsMTg4LjV6IE0xMCwyODYuNUgwdjcxaDEwVjI4Ni41TDEwLDI4Ni41eiBNMTAsMzg0LjVIMHY3MWgxMFYzODQuNUwxMCwzODQuNXogTTEwLDQ4Mi41SDB2MTloMTBsMCwwbDAsMGwwLDBsMCwwYzE1LjEwNSwwLDQyLDAsNDIsMHYtMTBIMTBWNDgyLjVMMTAsNDgyLjV6IE0xNTAsNDkxLjVINzl2MTBoNzFWNDkxLjVMMTUwLDQ5MS41eiBNMjQ4LDQ5MS41aC03MXYxMGg3MVY0OTEuNUwyNDgsNDkxLjV6IE0zNDYsNDkxLjVoLTcxdjEwaDcxVjQ5MS41TDM0Niw0OTEuNXogTTQ0NCw0OTEuNWgtNzF2MTBoNzFWNDkxLjVMNDQ0LDQ5MS41eiBNNDk5LDQ1OC41aC0xMHYzM2gtMTh2MTBoMjhWNDU4LjVMNDk5LDQ1OC41eiBNNDk5LDM2MC41aC0xMHY3MWgxMFYzNjAuNUw0OTksMzYwLjV6IE00OTksMjYyLjVoLTEwdjcxaDEwVjI2Mi41TDQ5OSwyNjIuNXogTTQ5OSwxNjQuNWgtMTB2NzFoMTBWMTY0LjVMNDk5LDE2NC41eiBNNDk5LDY2LjVoLTEwdjcxaDEwVjY2LjVMNDk5LDY2LjV6Ii8+PC9nPjxwYXRoIGRpc3BsYXk9Im5vbmUiIGQ9Ik01MDAuNSwwdjUwMS41SDAuNVYwSDUwMC41IE00OTAuNSwxMGgtNDgwdjQ4MS41aDQ4MFYxMEw0OTAuNSwxMHoiLz48cGF0aCBmaWxsPSJub25lIiBzdHJva2U9IiMwMDAwMDAiIHN0cm9rZS13aWR0aD0iMTAiIGQ9Ik0yNDksMTU4djE4NyBNMzQyLjUsMjUxLjVoLTE4NyIvPjwvc3ZnPg==";
		if(is_array($arrStruct) && count($arrStruct)) {
			foreach($arrStruct AS $struct) {
				if($_REQUEST["XHR_DIALOG_ID"])
				{
					$url = "javascript:ff.ffPage.dialog.goToUrl('" . $_REQUEST["XHR_DIALOG_ID"] . "', '" . "/admin/import/struct/" . ffCommon_url_rewrite($struct["name"]) . "')";
				} else { 
					$url = "/admin/import/struct/" . ffCommon_url_rewrite($struct["name"]) . ($ret_url ? "?ret_url=" . rawurlencode($ret_url) : "");
				}

	    		$tpl->set_var("item_url", $url);
	    		$tpl->set_var("item_name", $struct["name"]);
	    		$tpl->set_var("item_description", $struct["description"]);
	    		$tpl->set_var("item_svg", ($struct["svg"] ? $struct["svg"] : $svg_default));
				$tpl->parse("SezItem", true);
			}

			if($_REQUEST["XHR_DIALOG_ID"])
			{
				$url = "javascript:ff.ffPage.dialog.goToUrl('" . $_REQUEST["XHR_DIALOG_ID"] . "', '" . "/admin/import/struct/" . ffCommon_url_rewrite("empty") . "')";
			} else { 
				$url = "/admin/import/struct/" . ffCommon_url_rewrite("empty") . ($ret_url ? "?ret_url=" . rawurlencode($ret_url) : "");
			}
		    $tpl->set_var("item_url", $url);
		    $tpl->set_var("item_name", "empty");
		    $tpl->set_var("item_description", "");
		    $tpl->set_var("item_svg", $svg_empty);
			$tpl->parse("SezItem", true);
		}
	}
	
	$cm->oPage->addContent($tpl);
