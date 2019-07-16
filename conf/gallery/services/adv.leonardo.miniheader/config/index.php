<?php
    if (!Auth::env("AREA_SERVICES_SHOW_MODIFY")) {
        ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
    }

   	$cm->oPage->form_method = "POST"; 

    $type_field = array();
    $type_field["enable"] = "Boolean";
    $type_field["code"] = "String";
    $type_field["mndArea"] = "String";
    $type_field["mndColor"] = array(
        "extended_type" => "Selection"
        , "multi_pairs" => array(
            array(new ffData("Azzurro"), new ffData(ffTemplate::_get_word_by_code("Azzurro")))
            , array(new ffData("Ghiaccio"), new ffData(ffTemplate::_get_word_by_code("Ghiaccio")))
            , array(new ffData("Verde"), new ffData(ffTemplate::_get_word_by_code("Verde")))
            , array(new ffData("Rosso"), new ffData(ffTemplate::_get_word_by_code("Rosso")))
            , array(new ffData("Arancio"), new ffData(ffTemplate::_get_word_by_code("Arancio")))
            , array(new ffData("Viola"), new ffData(ffTemplate::_get_word_by_code("Viola")))
            , array(new ffData("Nero"), new ffData(ffTemplate::_get_word_by_code("Nero")))
        )
    );  
    $type_field["mndFromColor"] = "String";
    $type_field["mndToColor"] = "String";
    $type_field["mndAlignColor"] = "String";
    $type_field["mndTime"] = array(
        "extended_type" => "Selection"
        , "multi_pairs" => array(
            array(new ffData("1000"), new ffData(ffTemplate::_get_word_by_code("1000")))
            , array(new ffData("800"), new ffData(ffTemplate::_get_word_by_code("800")))
            , array(new ffData("500"), new ffData(ffTemplate::_get_word_by_code("500")))
            , array(new ffData("20"), new ffData(ffTemplate::_get_word_by_code("20")))
            , array(new ffData("1"), new ffData(ffTemplate::_get_word_by_code("1")))
        )
    );  
    $type_field["mndImg"] = "String";
    $type_field["mndLnkTxt1"] = "String";
    $type_field["mndLnkUrl1"] = "String";
    $type_field["mndLnkTxt2"] = "String";
    $type_field["mndLnkUrl2"] = "String";
    $type_field["mndLnkTxt3"] = "String";
    $type_field["mndLnkUrl3"] = "String";
    $type_field["mndLnkTxt4"] = "String";
    $type_field["mndLnkUrl4"] = "String";
    $type_field["mndLnkTxt5"] = "String";
    $type_field["mndLnkUrl5"] = "String";
    $type_field["mndLnkColor"] = "String";
    $type_field["mndLnkColorOver"] = "String";

	if(check_function("system_services_modify"))
		system_services_modify(basename(ffCommon_dirname(__DIR__)), $type_field);