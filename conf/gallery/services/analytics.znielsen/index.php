<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    if($actual_srv["enable"] && strlen($actual_srv["v"]) && strlen($actual_srv["cid"]) && strlen($actual_srv["content"]) && strlen($actual_srv["server"])) 
    {
        $globals->fixed_post["body"][] =  
            '<!-- START Nielsen Online SiteCensus V6.0 -->
            <!-- COPYRIGHT 2012 Nielsen Online -->
            <script type="text/javascript" src="//secure-it.imrworldwide.com/v' . $actual_srv["v"] . '.js">
            </script>
            <script type="text/javascript" defer="defer">
             var pvar = { cid: "' . $actual_srv["cid"] . '", content: "' . $actual_srv["content"] . '", server: "' . $actual_srv["server"] . '" };
             var feat = { check_cookie: 0 };
             var trac = nol_t(pvar, feat);
             trac.record().post();
            </script>
            <noscript>
             <div>
             <img src="//secure-it.imrworldwide.com/cgi-bin/m?ci=' . $actual_srv["cid"] . '&amp;cg=0&amp;cc=0&amp;ts=noscript"
             width="1" height="1" alt="" />
             </div>
            </noscript>
            <!-- END Nielsen Online SiteCensus V6.0 -->';
    }