<?php
function get_resource_cascading($oPage, $setting_path, $name, $no_cascading = false, $base_path = null) {
    $real_name = ffGetFilename($name);

    if($base_path === null)
        $base_path = FF_THEME_DIR . "/library/plugins" . "/" . strtolower($real_name);

    switch(ffGetFilename($name, false)) {
        case "js":
            $destination_path = FF_THEME_DIR . "/" . $oPage->getTheme() . "/javascript";
            $type = "js";
            break;
        case "css":
            $destination_path = FF_THEME_DIR . "/" . $oPage->getTheme() . "/css";
            $type = "css";
            break;
        default:
            $destination_path = $base_path;
            $type = "";
            return;
    }
    
    $res = "";    
    if($setting_path != ffCommon_dirname($setting_path)) {
        do {
            $cascading_name = str_replace("/", "_", trim($setting_path, "/"));

            if (is_file($oPage->disk_path . $destination_path . "/" . $cascading_name . "_" . $name)) {
                $res = $destination_path . "/" . $cascading_name . "_" . $name;
                break;
            }

            if ($no_cascading)
                break;
        } while($setting_path != ffCommon_dirname($setting_path) && $setting_path = ffCommon_dirname($setting_path));
    } 
    
    if(!strlen($res)
        && is_file($oPage->disk_path . $destination_path . "/" . $name)
    ) {
        
        $res = $destination_path . "/" . $name;
    }
    if(!strlen($res)
        && is_file($oPage->disk_path . $base_path . "/" . $name)
    ) {
        $res = $base_path . "/" . $name;
    }

    if(strlen($res)) {
        switch($type) {
            case "js":
                $oPage->tplAddJs($real_name
                    , array(
                        "file" => basename($res)
                        , "path" => ffCommon_dirname($res)
                ));
                break;
            case "css":
                $oPage->tplAddCss($real_name
                    , array(
                        "file" => basename($res)
                        , "path" => ffCommon_dirname($res)
                ));
                break;
            default:
        }
    }
}
?>
