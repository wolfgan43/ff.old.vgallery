<?php
$permission = check_trivia_permission(); 

$db = ffDB_Sql::factory();

$UserNID = get_session("UserNID");

$achievement = basename($cm->real_path_info);
$new_achievement = array();

if(strlen($achievement))
{
	
	$trofei = mod_trivia_get_user_achievement($UserNID, false, true);
	$new_achievement = array("image" => $trofei[$achievement]["file"]
							, "title" => $trofei[$achievement]["name"]
							, "description" => $trofei[$achievement]["description"]
							, "is_new" => false
						);
	if(is_array($trofei) && !$trofei[$achievement]["is_set"])
	{
		$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_users_fields 
					(
						ID
						, ID_users
						, field
						, value
					)
					VALUES
					(
						null 
						, " . $db->toSql($UserNID, "Number") . "
						, " . $db->toSql($achievement, "String") . "
						, 0
					)";
		$db->execute($sSQL);
		
		$new_achievement["is_new"] = true;
	} 
}
echo ffCommon_jsonenc($new_achievement, true);
exit; 
?>