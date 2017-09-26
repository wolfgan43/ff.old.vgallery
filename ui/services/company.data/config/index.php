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
 * @subpackage connector
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
    
    if (!(AREA_SERVICES_SHOW_MODIFY || $force_company_data)) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["enable_international"] = "Boolean";
    $type_field["force_compilation"] = "Boolean";
    $type_field["tpl"] = "String";
    $type_field["company_name"] = "String";
    $type_field["cf"] = "String";
    $type_field["piva"] = "String";
    $type_field["address"] = "String";
    $type_field["cap"] = "String";
    $type_field["city"] = "String";
    $type_field["prov"] = "String";
    $type_field["state"] = "String";
    $type_field["tel"] = "String";
    $type_field["fax"] = "String";
    $type_field["email"] = "String";
    $type_field["info"] = "Text";
    $type_field["label"] = "Boolean";
    
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(ffCommon_dirname(__FILE__))), $type_field);