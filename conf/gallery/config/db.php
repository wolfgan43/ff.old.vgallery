<?php
if ($_SERVER["HTTP_HOST"] === "vgallery.alex")
{
define("FF_DATABASE_NAME", 'vgallery');
    define("FF_DATABASE_HOST", 'localhost');
    define("FF_DATABASE_USER", 'root');
    define("FF_DATABASE_PASSWORD", '');


} else {
define("FF_DATABASE_NAME", 'www_blueocarina_net');
    define("FF_DATABASE_HOST", 'localhost');
    define("FF_DATABASE_USER", 'bonetdb');
    define("FF_DATABASE_PASSWORD", 'EveryDay273');


}

    define("DB_CHARACTER_SET", 'utf8');
    define("DB_COLLATION", 'utf8_unicode_ci');
    
    $config_check["db"] = true;