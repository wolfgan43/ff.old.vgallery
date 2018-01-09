<?php
$user = get_session("user_permission");
if($user["ID"] && $user["ID"] != MOD_SEC_GUEST_USER_ID) {
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
	$html = file_get_contents($path . "/index.tpl");

	$out_buffer = $head . $html;
}