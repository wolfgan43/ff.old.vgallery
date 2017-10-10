<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    if($actual_srv["enable"] && strlen($actual_srv["c1"]) && strlen($actual_srv["c2"]) && strlen($actual_srv["cv"]) && strlen($actual_srv["cj"])) 
    {
        $globals->fixed_post["body"][] = 
            '<!-- Begin comScore Tag -->
                <script defer="defer">
                    var _comscore = _comscore || [];
                    _comscore.push({ c1: "' . $actual_srv["c1"] . '", c2: "' . $actual_srv["c2"] . '" });
                    (function() {
                      var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true;
                      s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js";
                      el.parentNode.insertBefore(s, el);
                    })();
                </script>
            <!-- End comScore Tag -->';
    }