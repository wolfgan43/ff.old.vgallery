<?php
function MD_swf_on_load_template($component) {
    $tpl =& $component->tpl[0];
    $oPage =& $component->parent[0];

    $globals = ffGlobals::getInstance("gallery");
    $settings_path = $globals->settings_path;
    $user_path = $globals->user_path;
    $selected_lang = $globals->selected_lang;
    
    setJsRequest("swfobject");
    
    $tpl->set_var("site_path", FF_SITE_PATH);
    $tpl->set_var("domain_inset", DOMAIN_INSET);
    $tpl->set_var("theme_inset", THEME_INSET);
    $tpl->set_var("language_inset", $selected_lang);
    $tpl->set_var("settings_path", $settings_path);
    $tpl->set_var("title", $oPage->title);
    $tpl->set_var("swf_url", CM_SHOWFILES . $component->user_vars["swf_url"]);
    
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
?>
