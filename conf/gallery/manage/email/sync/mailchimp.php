<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!(Auth::env("AREA_EMAIL_SHOW_MODIFY") || Auth::env("AREA_EMAIL_ADDRESS_SHOW_MODIFY"))) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

$apikey = "46b45bb54ba63a5d57fb7ddbfd44dfdd-us11";

if(check_function("class.mailchimp")) {
	
$mc = new mailchimp($apikey);

// Get 10 lists starting from offset 10 and include only a specific set of fields

	mailchimp_sync_lists($mc);
	
}


function mailchimp_sync_lists($mc) {
	$db = ffDB_Sql::factory();
	
	$res = $mc->get('lists', array(
		'fields' => 'lists.id,lists.name,lists.stats.member_count',
		'offset' => 0,
		'count' => 10
	));	
	if(is_array($res["lists"]) && count($res["lists"])) {
		$arrListKeys = array_flip(array_column($res["lists"], 'id'));
		
		$sSQL = "SELECT email_list.*
				FROM email_list
				WHERE email_list.ID_src IN ('" . implode("', '", array_keys($arrListKeys)) . "')";
		$db->query($sSQL);
		if($db->nextRecord()) {
			do {
				unset($arrListKeys[$db->getField("ID_src", "Text", true)]);
			} while($db->nextRexord());
		}
		
		if(count($arrListKeys)) {
			foreach($arrListKeys AS $key) {
				$sSQL = "INSERT INTO email_list
						(
							ID
							, ID_src
							, name
							, member_count
							, created
						)
						VALUES
						(
							null
							, " . $db->toSql($res["lists"][$key]["id"]) . "
							, " . $db->toSql($res["lists"][$key]["name"]) . "
							, " . $db->toSql($res["lists"][$key]["stats"]["member_count"], "Numnber") . "
							, " . $db->toSql(time(), "Number") . "
						)";
				$db->execute($sSQL);
			}
		}
	}
}