<?php 
if(!defined("SHOWFILES_IS_RUNNING")) {
    function dd($array) {
        echo "<pre>";
        var_dump($array);
        exit;
    }

	require(FF_DISK_PATH . "/library/gallery/common.php");
	require(FF_DISK_PATH . "/library/gallery/init.php");
	require(FF_DISK_PATH . "/library/gallery/job.php");
}