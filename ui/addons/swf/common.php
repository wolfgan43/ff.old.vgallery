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
 * @subpackage module
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function MD_swf_on_load_template($component) {
    $tpl =& $component->tpl[0];
    $oPage =& $component->parent[0];

    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;
    $user_path = $globals->user_path;
    $selected_lang = $globals->selected_lang;
    
   $oPage->tplAddJs("library.swfobject");
    
    $tpl->set_var("site_path", FF_SITE_PATH);
    $tpl->set_var("domain_inset", DOMAIN_INSET);
    $tpl->set_var("theme_inset", THEME_INSET);
    $tpl->set_var("language_inset", $selected_lang);
    $tpl->set_var("settings_path", $settings_path);
    $tpl->set_var("title", $oPage->title);
    $tpl->set_var("swf_url", FF_SITE_PATH . constant("CM_SHOWFILES") . $component->user_vars["swf_url"]);
    
    if(strlen($component->user_vars["xml_url"]) && $component->user_vars["xml_varname"]) {
	    $tpl->set_var("xml_url", $component->user_vars["xml_url"]);
	    $tpl->set_var("xml_varname", $component->user_vars["xml_varname"]);
		$tpl->parse("SezXml", false);
	} else {
		$tpl->set_var("SezXml", "");
	}
    $tpl->set_var("width", $component->user_vars["width"]);
    $tpl->set_var("height", $component->user_vars["height"]);

	if($component->user_vars["play"])
    	$tpl->set_var("play", "true");
    else
    	$tpl->set_var("play", "false");
	if($component->user_vars["loop"])
    	$tpl->set_var("loop", "true");
    else
    	$tpl->set_var("loop", "false");
	if($component->user_vars["menu"])
    	$tpl->set_var("menu", "true");
    else
    	$tpl->set_var("menu", "false");
	
	$tpl->set_var("quality", $component->user_vars["quality"]);
	$tpl->set_var("scale", $component->user_vars["scale"]);
	$tpl->set_var("salign", $component->user_vars["salign"]);
	$tpl->set_var("wmode", $component->user_vars["wmode"]);
	$tpl->set_var("bgcolor", $component->user_vars["bgcolor"]);
	$tpl->set_var("base", $component->user_vars["base"]);
	if($component->user_vars["swliveconnect"])
    	$tpl->set_var("swliveconnect", "true");
    else
    	$tpl->set_var("swliveconnect", "false");

	if(is_array($component->user_vars["flashvars"]) && count($component->user_vars["flashvars"])) {
		foreach($component->user_vars["flashvars"] AS $flashVars_key => $flashVars_value) {
			$tpl->set_var("flashvars_name", $flashVars_key);
			$tpl->set_var("flashvars_value", $flashVars_value);
			$tpl->parse("SezFlashVars", true);
		}
	} else {
		$tpl->set_var("SezFlashVars", "");
	}
	
	$tpl->set_var("devicefont", $component->user_vars["devicefont"]);
	$tpl->set_var("allowscriptaccess", $component->user_vars["allowscriptaccess"]);
	$tpl->set_var("seamlesstabbing", $component->user_vars["seamlesstabbing"]);
	if($component->user_vars["allowfullscreen"])
    	$tpl->set_var("allowfullscreen", "true");
    else
    	$tpl->set_var("allowfullscreen", "false");
	if($component->user_vars["allownetworking"])
    	$tpl->set_var("allownetworking", "true");
    else
    	$tpl->set_var("allownetworking", "false");

	$tpl->set_var("align", $component->user_vars["align"]);
	$tpl->set_var("version", $component->user_vars["version"]);
	$tpl->set_var("quality", $component->user_vars["quality"]);
	
    $tpl->set_var("real_name", ffCommon_specialchars(preg_replace('/[^a-zA-Z0-9]/', '', $component->id)));
    
    if(strlen($component->title)) {
        $tpl->set_var("title", ffTemplate::_get_word_by_code($component->title));
        $tpl->parse("SezTitle", false);
    } else {
        $tpl->set_var("SezTitle", "");
    }
}

function MD_swf_on_loaded_data($component) {
    $component->form_fields["name"]->value = new ffData(ffCommon_url_rewrite($component->form_fields["name"]->value->getValue()));
    $component->form_fields["xml_url"]->value = new ffData(FF_SITE_PATH . VG_SITE_XMLSWF . "/" . $component->form_fields["name"]->value->getValue());
}
