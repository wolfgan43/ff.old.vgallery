<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system

    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) { 
        $oPage->tplAddJs("Typekit",  $actual_srv["code"] . ".js", "http://use.typekit.com", true, false, null);
        $tpl = ffTemplate::factory(get_template_cascading("/", "typekit.html", "/services"));
        $tpl->load_file("typekit.html", "main");

        $oPage->tplAddJs("typekit.data", null, null, true, true, $tpl->rpparse("main", false));
        
//        $tpl->set_var("code", constant(strtoupper(basename(ffCommon_dirname(__FILE__))) . "_CODE"));
        
    }