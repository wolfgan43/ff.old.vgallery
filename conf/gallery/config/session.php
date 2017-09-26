<?php
if ($_SERVER["HTTP_HOST"] === "vgallery.alex")
{
    define("SESSION_SAVE_PATH", 'c:/windows/temp');

} else {
        define("SESSION_SAVE_PATH", '/tmp');

}    
    define("SESSION_NAME", 'PHPSESS_abq42619');
    define("APPID", '9abe42619b6fa5ce92889ff1e6fed8b4-888ef0s118e1004221606882ef3ca09f');
    define("MEMORY_LIMIT", '96M');
    
    $config_check["session"] = true;