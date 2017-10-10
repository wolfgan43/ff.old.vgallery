<?php 
	// $db_gallery : access db object
	// $globals : globals settings
    // $actual_srv = params defined by system
    
    if(isset($actual_srv["enable"]) && strlen($actual_srv["enable"])) {
        switch($globals->user_path) {
            case "/OneSignalSDKUpdaterWorker.js":
            case "/OneSignalSDKWorker.js":
                header("Content-type: application/javascript");

                echo "importScripts('https://cdn.onesignal.com/sdks/OneSignalSDK.js');";
                exit;
            default:
        }
		//$cm->router->addRule('^/OneSignalSDKUpdaterWorker.js', array("url"=> '/conf/gallery/services/notification.onesignal/gateway.php'), cmRouter::PRIORITY_HIGH, true, false);
		//$cm->router->addRule('^/OneSignalSDKWorker.js', array("url"=> '/conf/gallery/services/notification.onesignal/gateway'), cmRouter::PRIORITY_HIGH, true, false);
        //$oPage->tplAddCss("manifest", "manifest.json", FF_THEME_DIR . '/' . FRONTEND_THEME . '/javascript', "manifest", "", false, false, null, false, "bottom");

        $globals->manifest["name"] = ($actual_srv["name"] ? $actual_srv["name"] : CM_LOCAL_APP_NAME);
        $globals->manifest["short_name"] = ($actual_srv["short_name"] ? $actual_srv["short_name"] : CM_LOCAL_APP_NAME);
        $globals->manifest["start_url"] = "/";
        $globals->manifest["display"] = "standalone";
        $globals->manifest["gcm_sender_id"] = "482941778795";
        
		$oPage->tplAddJs("OneSignalSDK", "OneSignalSDK.js", "https://cdn.onesignal.com/sdks", true, false, null, true);
		$js_content = 'var OneSignal = window.OneSignal || [];
                        OneSignal.push(["init", {
                          appId: "' . $actual_srv["code"] . '",
                          autoRegister: true,
                          notifyButton: {
                            enable: false 
                          }' . 
						  (strlen($actual_srv["safari"]) ? ', safari_web_id: "' . $actual_srv["safari"] . '"' : "" ) . '
						}]);';
        
        $oPage->tplAddJs("OneSignal", null, null, false, $oPage->isXHR(), $js_content, false, "bottom");  
    }
