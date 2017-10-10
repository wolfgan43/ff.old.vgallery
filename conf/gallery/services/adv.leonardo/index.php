<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if($actual_srv["enable"]) 
    {
        $js_content = $actual_srv["criteo_tag"] . "
            var JADV_DFP_SEM='';
            var JADV_JSON_SEM='';
            
            var e = document.createElement('script');
            e.type = 'text/javascript'; 
            e.src = 'https://sem.juiceadv.com/?refUrl='+encodeURIComponent(window.top.location.href);
            document.head.appendChild(e);
            
            var googletag = googletag || {};
            googletag.cmd = googletag.cmd || [];
            (function() {
            var gads = document.createElement('script');
            gads.async = true;
            gads.type = 'text/javascript';
            var useSSL = 'https:' == document.location.protocol;
            gads.src = (useSSL ? 'https:' : 'http:') + 
            '//www.googletagservices.com/tag/js/gpt.js';
            var node = document.getElementsByTagName('script')[0];
            node.parentNode.insertBefore(gads, node);
            })();
            var gptAdSlots = [];
            var mapping = {};
            var mapping1 = {};
            googletag.cmd.push(function() {
			"   . ($_SERVER["ORIG_PATH_INFO"] == "/" 
					? $actual_srv["params_home"]
					: $actual_srv["params"]			
				) . "
				googletag.pubads().enableSingleRequest();
				googletag.pubads().disableInitialLoad();
            
			" . $actual_srv["criteo_post_tag"] . " 

            JADV_JSON_SEM && Object.keys(JADV_JSON_SEM).map(function(el){googletag.pubads().setTargeting(el,JADV_JSON_SEM[el]);});
            googletag.pubads().setTargeting('key_topic','');

            googletag.enableServices();
            });
            ";
        /*
        $js_content = "
            var ord = window.ord || Math.floor(Math.random() * 1e16);
            var JADV_DFP_SEM=\"\";
            document.write('<scr'+'ipt src=\"http://sem.juiceadv.com/\"></scr'+'ipt>');
        ";
        if($actual_srv["skin-masthead-interstiziali"]) {
            $js_content .= "document.write('<scr'+'ipt type=\"text/javascript\" src=\"http://ad.doubleclick.net/N5902/adj/" . $actual_srv["nomesito"] . "/" . $oPage->page_path . ";tile=1;sz=970x250,1x3;dcopt=ist;'+JADV_DFP_SEM+'ord=' + ord + '?\"><\/scr'+'ipt>');";
        }
        if($actual_srv["728x90"]) {
            $js_content .= "document.write('<scr'+'ipt type=\"text/javascript\" src=\"http://ad.doubleclick.net/N5902/adj/" . $actual_srv["nomesito"] . "/" . $oPage->page_path . ";tile=2;sz=728x90;'+JADV_DFP_SEM+'ord=' + ord + '?\"><\/scr'+'ipt>');";
        }
        if($actual_srv["300x250-300x600-primoscroll"]) {
            $js_content .= "document.write('<scr'+'ipt type=\"text/javascript\" src=\"http://ad.doubleclick.net/N5902/adj/" . $actual_srv["nomesito"] . "/" . $oPage->page_path . ";tile=3;sz=300x250,300x600;'+JADV_DFP_SEM+'ord=' + ord + '?\"><\/scr'+'ipt>');";
        }
		
        */
        //$oPage->fixed_pre_content .= '<script defer="defer" charset="utf-8" type="text/javascript">' . $js_content . '</script>';
        $oPage->tplAddJs("Leonardo", null, null, false, $oPage->isXHR(), $js_content, false, "bottom");  
        
        $oPage->tplAddCss("LeonardoCss", "leoadv.css", "https://img4.juiceadv.com/clienti/Traffic");
         
        

    }
?>