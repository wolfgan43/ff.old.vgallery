<?php
	$userID = get_session("UserID");
	$userNID = get_session("UserNID");

	if(mod_security_check_session(false) && $userID != MOD_SEC_GUEST_USER_NAME) 
	{
		$db = ffDB_Sql::factory();
                
                if (!$cm->isXHR()/* && strpos(strtolower($_SERVER["HTTP_USER_AGENT"]), "googlebot") === false*/)
                {
                        ffRedirect(str_replace("/parsedata", "", $_SERVER["REQUEST_URI"]), 301); // TO FIX!!!
                }

		$smart_url = basename($cm->real_path_info);
		$res = array();
		
		$out =	(isset($_REQUEST["out"]) && strlen($_REQUEST["out"])
						? $_REQUEST["out"]
						: "json"
					);
		
		$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID AS ID_idea
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.title AS title
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.teaser AS teaser
				, " . CM_TABLE_PREFIX . "mod_crowdfund_idea.cover AS cover
				, anagraph.*
			FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea
				INNER JOIN anagraph ON anagraph.uid = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.owner
				INNER JOIN " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages ON " . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_idea = " . CM_TABLE_PREFIX . "mod_crowdfund_idea.ID
			WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea.smart_url = " . $db->toSql($smart_url) . "
				AND "  . CM_TABLE_PREFIX . "mod_crowdfund_idea_rel_languages.ID_languages = " . $db->toSql(LANGUAGE_INSET_ID, "Number");
		$db->query($sSQL);
		if($db->nextRecord()) 
		{
			$crowdfund_public_path = mod_crowdfund_get_path_by_lang("public");

			$ID_idea = $db->getField("ID_idea", "Number", true);
			$email = $db->getField("email", "Text", true);
			$name = $db->getField("name", "Text", true);
			$title = $db->getField("title", "Text", true);
			$teaser = $db->getField("teaser", "Text", true);
			$cover = $db->getField("cover", "Text", true);
			$surname = $db->getField("surname", "Text", true);
		
			$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.*
							, anagraph.ID AS ID_user
						FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
							INNER JOIN anagraph ON anagraph.ID = " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_user_anagraph
						WHERE anagraph.uid = " . $db->toSql($userNID, "Number") . "
							AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . $db->toSql($ID_idea, "Number");
			$db->query($sSQL);
			if($db->nextRecord()) 
			{
				$ID_user = $db->getField("ID_user", "Number", true);
				$sSQL = "DELETE  
							FROM " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
							WHERE " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_user_anagraph = " . $db->toSql($ID_user, "Number") . "
								AND " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers.ID_idea = " . $db->toSql($ID_idea, "Number"); 
				$db->execute($sSQL);
				
			
				$res = array("class" => "", "label" => ffTemplate::_get_word_by_code("crowdfund_label_follow"));
			} else
			{
				$sSQL = "SELECT anagraph.*
							FROM anagraph
							WHERE anagraph.uid = " . $db->toSql($userNID, "Number");
				$db->query($sSQL);
				if($db->nextRecord()) 
				{
					$ID_user = $db->getField("ID", "Number", true);  
					$user_name = $db->getField("name", "Text", true);
					$user_surname = $db->getField("surname", "Text", true);
					$user_email = $db->getField("email", "Text", true);
					$complete_name = $user_name . " " . $user_surname;
					
					$time = time();
					$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_crowdfund_idea_followers
						(
							ID
							, ID_user_anagraph
							, ID_idea
							, created
							, last_update
						)
						VALUES
						(
							null
							, " . $db->toSql($ID_user, "Number") . "
							, " . $db->toSql($ID_idea, "Number") . " 
							, " . $db->toSql($time, "Number") . "
							, " . $db->toSql($time, "Number") . "
						)";
					$db->execute($sSQL); 

					//facebook publish

					if(check_function("system_lib_facebook")) {
							$res = facebook_publish($user_name . " " . $user_surname . " " . ffTemplate::_get_word_by_code("crowdfund_follow") . " " . $title
							, DOMAIN_INSET . FF_SITE_PATH . $crowdfund_public_path . "/" . $smart_url
							, "http://" . DOMAIN_INSET . FF_SITE_PATH . CM_SHOWFILES . "/crowdfundme-social" . $cover
							, $title
							, ""
							, $teaser
							, array()//array("name" => CM_LOCAL_APP_NAME, "link" => "http://" . DOMAIN_INSET)	
							, "" // place serve read_stream ...ma non serve
							, "" // spazio per raccogliere le persone citate
							, "{'value':'EVERYONE'}"//funzionano solo self e fiends.. serve read_stream per {'value':'EVERYONE'} e {'value':'ALL_FRIENDS'} e {'value':'FRIENDS_OF_FRIENDS'}
							);  
						
					}
				
					$res = array("class" => "followed", "label" => ffTemplate::_get_word_by_code("crowdfund_label_followed"));
					$to[0]["name"] = $name . " " . $surname;
					$to[0]["mail"] = $email;
					
					//$to[0]["mail"] = "pale619@hotmail.com";
					//$from[0]["name"] = $user_name . " " . $user_surname;
					//$from[0]["mail"] = "pale619@hotmail.com";
					//$from[0]["mail"] = $user_email;
			
					$fields["idea"]["title"] = $title; 
					$fields["account"]["name"] = $user_name . " " . $user_surname; 
					$fields["account"]["email"] = $user_email;

					if(check_function("process_mail")) 
					{ 
						$rc = process_mail(email_system("follow"), $to, NULL, NULL, $fields, null, null, null, false, null, false);
					}
				}
			}
		}
		switch($out) 
		{
			case "html":
				  break;
			case "array":
				  break;
			case "json":	
			default:
			  echo ffCommon_jsonenc($res, true);
		}
		exit;
	} else 
	{
		ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
	}
?>
