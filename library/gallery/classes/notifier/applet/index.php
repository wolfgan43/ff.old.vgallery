<?php

if(get_session("UserNID") && get_session("UserNID") != MOD_SEC_GUEST_USER_ID) {
	$globals = ffGlobals::getInstance("gallery");
	$path = __DIR__;

	switch ($applet_params["tpl"]) {
		case "handlebars":
			$tpl_name = "text/x-handlebars-template";
			$globals->js["link"]["handlebars"] = "https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.11/handlebars.js";
			break;
		default:
			$tpl_name = "text/template";

	}

	$globals->js["embed"]["notifier"] = file_get_contents($path . "/script.js");
	$globals->css["embed"]["notifier"] = file_get_contents($path . "/style.css");

	$head = '<script id="notification-item" type="' . $tpl_name . '" >' . file_get_contents($path . "/item.tpl") . '</script>';
/*
	$tpl = ffTemplate::factory($path);
	$tpl->load_file("item.tpl", "main");

	$html = $tpl->rpparse("main", false);
*/
	$html = file_get_contents($path . "/index.tpl");
	$html = str_replace(array(
		"{notifier_title}"
		, "{notifier_not_found}"
	),
	array(
		ffTemplate::_get_word_by_code("notifier_title")
		, ffTemplate::_get_word_by_code("notifier_not_found")
	), $html);

	$out_buffer = $head . $html;
}