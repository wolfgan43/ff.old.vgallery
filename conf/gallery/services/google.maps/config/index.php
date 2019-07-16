<?php
    if (!Auth::env("AREA_SERVICES_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 
   	
    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["key"] = "String";
	$type_field["region"] = "String";
	$type_field["scroll"] = "Boolean";
	$type_field["marker.limit"] = "Number";
	$type_field["marker.icon"] = "String";
	$type_field["zoom.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	$type_field["zoom.style"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(SMALL), new ffData("SMALL"))
			, array(new ffData(LARGE), new ffData("LARGE"))
			//, array(new ffData(DEFAULT), new ffData("DEFAULT"))
		)
	);
	$type_field["zoom.max"] = "Number";
	$type_field["zoom.min"] = "Number";

	$type_field["control.options"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(HORIZONTAL_BAR), new ffData("HORIZONTAL_BAR"))
			, array(new ffData(DROPDOWN_MENU), new ffData("DROPDOWN_MENU"))
			//, array(new ffData(DEFAULT), new ffData("DEFAULT"))
		)
	);
	$type_field["control.style"] = "String";

	$type_field["pan.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	
	$type_field["scale.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	
	$type_field["streetview.position"] = array(
		"extended_type" => "Selection"
		, "multi_pairs" => array (
			array(new ffData(TOP_CENTER), new ffData("TOP CENTER")),
			array(new ffData(TOP_LEFT), new ffData("TOP LEFT")),
			array(new ffData(TOP_RIGHT), new ffData("TOP RIGHT")),
			array(new ffData(LEFT_TOP), new ffData("LEFT TOP")),
			array(new ffData(RIGHT_TOP), new ffData("RIGHT TOP")),
			array(new ffData(LEFT_CENTER), new ffData("LEFT CENTER")),
			array(new ffData(RIGHT_CENTER), new ffData("RIGHT CENTER")),
			array(new ffData(LEFT_BOTTOM), new ffData("LEFT BOTTOM")),
			array(new ffData(RIGHT_BOTTOM), new ffData("RIGHT BOTTOM")),
			array(new ffData(BOTTOM_CENTER), new ffData("BOTTOM CENTER")),
			array(new ffData(BOTTOM_LEFT), new ffData("BOTTOM LEFT")),
			array(new ffData(BOTTOM_RIGHT), new ffData("BOTTOM RIGHT"))
		)
	);
	
	$type_field["style"] = "TextSimple";
   
	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(__DIR__)), $type_field);