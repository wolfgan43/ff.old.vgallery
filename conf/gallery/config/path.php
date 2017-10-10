<?php
if ($_SERVER["HTTP_HOST"] === "vgallery.alex")
{
    define("FF_DISK_PATH", 'D:/htdocs/vgallery');
    define("FF_SITE_PATH", '');

    define("DISK_UPDIR", 'D:/htdocs/vgallery/uploads');
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