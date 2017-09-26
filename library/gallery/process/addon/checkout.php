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
function process_addon_checkout($user_path, $item_title, $item_price, $mpay, $layout) {
	$cm = cm::getInstance();
	$globals = ffGlobals::getInstance("gallery");

	static $params = null;

	$db = ffDB_Sql::factory();	
	if(strlen($mpay)) {
		if(is_array($globals->ecommerce["preview"]["vatTime"]) && count($globals->ecommerce["preview"]["vatTime"])) {
			foreach($globals->ecommerce["preview"]["vatTime"] AS $arrVatTime_key => $arrVatTime_value) {
				if(time() > $arrVatTime_key) {
					$actual_vat = $arrVatTime_value;
					break;
				}
			}
		}

		//$tpl_data["custom"] = "checkout-" . ffCommon_url_rewrite($mpay) . ".html";
		$tpl_data["base"] = "checkout-" . ffCommon_url_rewrite($mpay) . ".html";

		$tpl_data["result"] = get_template_cascading($user_path, $tpl_data, "/tpl/addon");

		$tpl = ffTemplate::factory($tpl_data["result"]["path"]);
		$tpl->load_file($tpl_data["result"]["prefix"] . $tpl_data[$tpl_data["result"]["type"]], "main");   

	   // $tpl = ffTemplate::factory(get_template_cascading($user_path, "checkout-" . ffCommon_url_rewrite($mpay) . ".html", "/vgallery", null, $layout["location"])); 
	   // $tpl->load_file("checkout-" . ffCommon_url_normalize($mpay) . ".html", "main");

		$tpl->set_var("site_path", FF_SITE_PATH);
		$tpl->set_var("theme_inset", THEME_INSET);

		if(check_function("ecommerce_cart_mpay_get_params") && !is_array($params))
    		$params = ecommerce_cart_mpay_get_params("/" . $mpay);

		$tpl->set_var("mpay_account", ffCommon_specialchars($params["mpay"]["account"]));
		$tpl->set_var("item_name", ffCommon_specialchars($item_title));
		$tpl->set_var("item_price", str_replace(",", ".", round($item_price, 2)));
		$tpl->set_var("lang", strtolower(substr(LANGUAGE_INSET, 0, 2)));
		$tpl->set_var("currency", $params["mpay"]["currency"]);
		$tpl->set_var("vat", str_replace(",", ".", round($actual_vat, 3)));
		

		
		$buffer = $tpl->rpparse("main", false);
	}	

	return $buffer;
}
