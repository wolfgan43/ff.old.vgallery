<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
        $tpl = ffTemplate::factory(get_template_cascading("/", "yandex.analytics.html", "/services"));
        $tpl->load_file("yandex.analytics.html", "main");
        
        $tpl->set_var("code", $actual_srv["code"]);
        
        $globals->fixed_pre["body"][] =  $tpl->rpparse("main", false);
    }