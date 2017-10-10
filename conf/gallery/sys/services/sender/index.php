<?php
	header('Access-Control-Allow-Origin: *'); 

	$status = "ko";

	$settings_default["display_form"] = true;
	$settings_default["disaple_request"] = false;
	$settings_default["template_default"] = "sender_default";

	$sid = basename($cm->real_path_info);
	if(strlen($sid)) {
		$custom_data = json_decode(get_sid($sid), true);
	}
	if(!$custom_data) {
		if($_REQUEST["data"]) {
			$custom_data = json_decode($_REQUEST["data"], true);
		} else {
			$custom_data = $_POST;
		}
	}

	if(is_array($custom_data) && count($custom_data)) {
		$settings = (array_key_exists("settings", $custom_data) && is_array($custom_data["settings"]) && count($custom_data["settings"])
  						? array_merge($settings_default, $custom_data["settings"])
  						: $settings_default
  					);


		$template = (array_key_exists("template", $custom_data) && strlen($custom_data["template"])
  						? $custom_data["template"]
  						: null
  					);
		if(!$settings["disable_request"] && isset($_REQUEST["b"])) {
			if(is_array($_REQUEST["b"])) {
				$body["pre"] = (isset($_REQUEST["b"]["pre"]) && strlen($_REQUEST["b"]["pre"])
  									? $_REQUEST["b"]["pre"]
  									: ""
  								);
				$body["post"] = (isset($_REQUEST["b"]["pre"]) && strlen($_REQUEST["b"]["pre"])
  									? $_REQUEST["b"]["pre"]
  									: ""
  								);
			} else {
				$body = (strlen($_REQUEST["b"])
  							? $_REQUEST["b"]
  							: (array_key_exists("body", $custom_data)
  								? $custom_data["body"]
  								: null
  							)
  						);
			}
		} else {
				$body = (array_key_exists("body", $custom_data)
  						? $custom_data["body"]
  						: null
  					);
		}

		$to[0]["mail"] = (!$settings["disable_request"] && isset($_REQUEST["to"]) && strlen($_REQUEST["to"])
  							? $_REQUEST["to"]
  							: (array_key_exists("to", $custom_data) && is_array($custom_data["to"]) && count($custom_data["to"])
  								? $custom_data["to"]["mail"]
  								: $custom_data["to"]
  							)
  						);
		$to[0]["name"] = (!$settings["disable_request"] && isset($_REQUEST["toname"]) && strlen($_REQUEST["toname"])
  							? $_REQUEST["toname"]
  							: (array_key_exists("to", $custom_data) && is_array($custom_data["to"]) && count($custom_data["to"])
  								? $custom_data["to"]["name"]
  								: $custom_data["to"]
  							)
  						);

  		if(!$to[0]["name"])
  			$to[0]["name"] = $to[0]["mail"];

		$from["mail"] = (!$settings["disable_request"] && isset($_REQUEST["from"]) && strlen($_REQUEST["from"])
					? $_REQUEST["from"]
					: (array_key_exists("from", $custom_data) && is_array($custom_data["from"]) && count($custom_data["from"])
  						? $custom_data["from"]["mail"]
  						: $custom_data["from"]
  					)
  				);
		$from["name"] = (!$settings["disable_request"] && isset($_REQUEST["fromname"]) && strlen($_REQUEST["fromname"])
					? $_REQUEST["fromname"]
					: (array_key_exists("from", $custom_data) && is_array($custom_data["from"]) && count($custom_data["from"])
  						? $custom_data["from"]["name"]
  						: $custom_data["from"]
  					)
  				);
  				
  		if(!$from["name"])
  			$from["name"] = $from["mail"];
  				

		$bcc = (!$settings["disable_request"] && isset($_REQUEST["bcc"]) && strlen($_REQUEST["bcc"])
					? $_REQUEST["bcc"]
					: (array_key_exists("bcc", $custom_data) && is_array($custom_data["bcc"]) && count($custom_data["bcc"])
  						? $custom_data["bcc"]
  						: null
  					)
  				);
		$cc = (!$settings["disable_request"] && isset($_REQUEST["cc"]) && strlen($_REQUEST["cc"])
					? $_REQUEST["cc"]
					: (array_key_exists("cc", $custom_data) && is_array($custom_data["cc"]) && count($custom_data["cc"])
  						? $custom_data["cc"]
  						: null
  					)
  				);

		$template = (array_key_exists("tpl", $custom_data) && strlen($custom_data["tpl"])
  						? $custom_data["tpl"]
  						: $settings["template_default"]
  					);

		$subject = (!$settings["disable_request"] && isset($_REQUEST["subject"]) && strlen($_REQUEST["subject"])
  						? $_REQUEST["subject"]
  						: (array_key_exists("subject", $custom_data) && strlen($custom_data["subject"])
  							? $custom_data["subject"]
  							: ffTemplate::_get_word_by_code($template)
  						)
  					);
		$fields = (!$settings["disable_request"] && isset($_REQUEST["fields"])
						? $_REQUEST["fields"]
						: (array_key_exists("fields", $custom_data) && is_array($custom_data["fields"]) && count($custom_data["fields"])
  							? $custom_data["fields"]
  							: null
  						)
  					);
		$attach = (array_key_exists("attach", $custom_data) && is_array($custom_data["attach"]) && count($custom_data["attach"])
  						? $custom_data["attach"]
  						: array()
  					);

		if($template !== null) {
			if(check_function("process_mail")) 
			{
				$valid_mail = false;
				if($to[0]["mail"]) 
					$valid_mail = true;
				else
					$strError = ffTemplate::_get_word_by_code("email_empty");
				
				if($valid_mail) {
					$rc = process_mail(email_system($template), $to, $subject, NULL, $fields, $from, $bcc, $cc, false, null, false, null, $body, $attach);
					if($rc) {
						$strError = ffTemplate::_get_word_by_code($rc);
					} else {
						$status = "ok";
					}

				}
			}
		} else {
			$strError = ffTemplate::_get_word_by_code("sender_template_not_set");
		}
	} else {
			$strError = ffTemplate::_get_word_by_code("wrong_source_data");
	}

	//, "sid" => $custom_data
	echo ffCommon_jsonenc(array(
            "status" => $status
            , "error" => (strlen($strError) ? $strError : ffTemplate::_get_word_by_code("sender_success"))
        ), true);

	exit;