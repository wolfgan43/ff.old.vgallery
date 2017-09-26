<?php 
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if($actual_srv["enable"]) 
    {
        $globals->fixed_pre["body"][] = $actual_srv["html"];

		if($actual_srv["css"])
	        $oPage->tplAddCss("HearstMiniHeaderCss"
                , array(
                    "embed" => $actual_srv["css"]
            ));

		if($actual_srv["js"])
	        $oPage->tplAddJs("HearstMiniHeaderJs"
                , array(
                    "embed" => $actual_srv["js"]
            ));
	        
    }