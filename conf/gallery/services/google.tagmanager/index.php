<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if(isset($actual_srv["code"]) && strlen($actual_srv["code"])) {
     /*   $tpl = ffTemplate::factory(get_template_cascading("/", "google.tagmanager.html", "/services"));
        $tpl->load_file("google.tagmanager.html", "main");
        
        if($actual_srv["datalayer"]) {
            $tpl->set_var("datalayer", " dataLayer.push(" . json_encode($actual_srv["datalayer"]) . ");");
        }
        $tpl->set_var("code", $actual_srv["code"]);
       
        $globals->fixed_pre["body"][] = $tpl->rpparse("main", false); */
        
        $js = "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $actual_srv["code"] . "');
			" . ($actual_srv["datalayer"]
				? $actual_srv["datalayer"]
				: ""
			);
		
		$oPage->tplAddJs("gtm", null, null, false, $oPage->isXHR(), $js, false, "top"); 
    }