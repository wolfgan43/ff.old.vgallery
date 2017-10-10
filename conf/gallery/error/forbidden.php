<?php
    require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

    if(check_function("process_html_page_error"))
    	$cm->oPage->addContent(process_html_page_error(403)); 
?>