<?php
if ($_SERVER["HTTP_HOST"] === "dev.ffcmsmaster.local")
{
    define("FF_DISK_PATH", __TOP_DIR__);
    define("FF_SITE_PATH", '');

    define("DISK_UPDIR", __TOP_DIR__ . DS . 'uploads');
    define("SITE_UPDIR", '/uploads');
}
else
{
    define("FF_DISK_PATH", '/var/www/vhosts/blueocarina.net/httpdocs');
    define("FF_SITE_PATH", '');

    define("DISK_UPDIR", '/var/www/vhosts/blueocarina.net/httpdocs/uploads');
    define("SITE_UPDIR", '/uploads');
}   
    $config_check["path"] = true;