<?php
	function get_exclude_by_db($fs_exclude, $include_externals = true) {
		$db = new ffDB_Sql;
		
		$sSQL = "SELECT Table_Name
				FROM information_schema.TABLES
				WHERE Table_Name = 'updater_exclude'
					AND TABLE_SCHEMA = " . $db->toSql(FF_DATABASE_NAME);
		$db->query($sSQL);
		if($db->nextRecord()) {
			$sSQL = "SELECT updater_exclude.path
                        , updater_exclude.status 
                    FROM updater_exclude
                    WHERE updater_exclude.path NOT IN (SELECT updater_externals.path FROM updater_externals WHERE updater_externals.status > 0)";
			if($include_externals)
				$sSQL .= " UNION  SELECT updater_externals.path, updater_externals.status FROM updater_externals";
				
			$db->query($sSQL);
			if($db->nextRecord()) {
				do {
					$fs_exclude[$db->getField("path", "Text", true)] = $db->getField("status", "Number", true);
				} while($db->nextRecord());
			}
		}
		return $fs_exclude;
	}

    $fs_exclude = array();
	$fs_exclude["/.htaccess"] = true;
	$fs_exclude["/.ftpquota"] = true;
	$fs_exclude["/index.html"] = true;
    $fs_exclude["/favicon.ico"] = true;
    $fs_exclude["/favicon.png"] = true;
    $fs_exclude["/robots.txt"] = true;
    $fs_exclude["/wiki"] = true;
	//$fs_exclude["/ns"] = true;
    $fs_exclude["/webalizer"] = true;
    $fs_exclude["/stats"] = true;
    $fs_exclude["/cgi-bin"] = true;
    $fs_exclude["/plesk-stat"] = true;
    $fs_exclude["/conf/gallery/config"] = true;
   // $fs_exclude["/conf/gallery/ajaxplorer/client/themes/oxygen/images/actions/16/player_play copy.png"] = true;
    $fs_exclude["/conf/modules"] = array("notifier" 	=> false
    									, "restricted" 	=> false
    									, "security" 	=> false
    								);
    $fs_exclude["/sessions"] = true;
    //$fs_exclude["/modules"] = true; 

    $fs_exclude["/conf/gallery/updater/check/file.php"] = true;
  //  $fs_exclude["/themes/admin/images/logo_admin.jpg"] = true;
  //  $fs_exclude["/themes/admin/images/logo_login.jpg"] = true;
    
    
    $fs_exclude["/conf/gallery/ajaxplorer/server/users"] = true;
    $fs_exclude["/conf/gallery/ajaxplorer/server/logs"] = true;

	$fs_exclude["/cache"] = true;
	$fs_exclude["/themes/site/.htaccess"] = true;
	$fs_exclude["/themes/site/theme_settings.xml"] = true;
	$fs_exclude["/themes/site/settings.php"] = true;

    if(!($sync 
    	&& (
    		$sync_rev
    		|| (defined("PRODUCTION_SITE") && strlen(PRODUCTION_SITE))
    	)
    )) {
		$fs_exclude["/uploads"] = true;
	}
 
    if(!($sync 
    	&& (
    		$sync_rev
    		|| (defined("DEVELOPMENT_SITE") && strlen(DEVELOPMENT_SITE))
    	)
    )) {
	    $fs_exclude["/applets"] = true;
	    $fs_exclude["/contents"] = true;
	    $fs_exclude["/themes/site/applets"] = true;
	    $fs_exclude["/themes/site/contents"] = true;
	    $fs_exclude["/themes/site/css"] = true;
	    $fs_exclude["/themes/site/favicons"] = true;
	    $fs_exclude["/themes/site/fonts"] = true;
	    $fs_exclude["/themes/site/images"] = true;
	    $fs_exclude["/themes/site/layouts"]["layer_gallery.html"]["update"] = true;
	    $fs_exclude["/themes/site/javascript"] = true;
	    $fs_exclude["/themes/site/modules"] = true;
	    $fs_exclude["/themes/site/swf"] = true;
	    $fs_exclude["/themes/site/xml"] = true;
	    $fs_exclude["/themes/site/routing_table.xml"] = true;
	    $fs_exclude["/themes/site/manifesto.xml"] = true;
        $fs_exclude["/themes/site/common.php"] = true;
	}

    if(file_exists(ffCommon_dirname(__FILE__) . "/exclude_fs_custom.php"))
        include("exclude_fs_custom.php");
        
	if(defined("FF_DATABASE_NAME") && class_exists("ffDB_Sql"))
        $fs_exclude = get_exclude_by_db($fs_exclude, (defined("MASTER_CONTROL") ? false : true));
?>
