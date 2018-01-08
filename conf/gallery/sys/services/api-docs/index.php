<?php
/*
  $tpl = ffTemplate::factory(get_template_cascading("/", "api.json", "/doc"));
  $tpl->load_file("api.json", "main");

  $res = $tpl->rpparse("main", false);
  */
  	if(check_function("get_schema_def"))
		$schema_def = get_schema_def();

	$service_module = $schema_def["module_available"];
	$service_schema = $schema_def["schema"];

  	$apis = array();
    if(is_array($service_schema) && count($service_schema)) {
        $default_errorResponses = array(
            400 => array(
                "reason" => ffTemplate::_get_word_by_code("doc_api_error_responses_400")
                , "code" => 400
            )
            , 401 => array(
                "reason" => ffTemplate::_get_word_by_code("doc_api_error_responses_401")
                , "code" => 401
            )
            , 500 => array(
                "reason" => ffTemplate::_get_word_by_code("doc_api_error_responses_500")
                , "code" => 500
            )
        );          

       // $service_available = get_service_file_name();

        if(is_array($service_module) && count($service_module)) {
            foreach($service_module AS $service_module_key) {
                foreach($service_schema AS $service_schema_key => $service_schema_value) {
                    $arrOperations = array();
                    if(array_key_exists("operations", $service_schema_value) 
                        && is_array($service_schema_value["operations"])
                        && count($service_schema_value["operations"])
                    ) {
                        foreach($service_schema_value["operations"] AS $operation_key => $operation_value) {
                           /* $arrParameters = (array_key_exists("parameters", $operation_value) && is_array($operation_value["parameters"])
                                                ? array_merge($default_parameters, $operation_value["parameters"])
                                                : $default_parameters
                                            );*/
                            $arrParameters = (array_key_exists("parameters", $operation_value) && is_array($operation_value["parameters"])
                                                ? $operation_value["parameters"]
                                                : array()
                                            );
                            
                            if(is_array($arrParameters) && count($arrParameters)) {
                                foreach($arrParameters AS $parameter_key => $parameter_value) {
                                    if(!array_key_exists("name", $arrParameters[$parameter_key]))
                                        $arrParameters[$parameter_key]["name"] = $parameter_key;

                                    if(array_key_exists($arrParameters[$parameter_key]["name"], $operation_value["parameters"]))
                                        $arrParameters[$parameter_key]["name"] = "params[" . $arrParameters[$parameter_key]["name"] . "]";

                                    $arrParameters[$parameter_key]["description"] = ffTemplate::_get_word_by_code("doc_api_" . $service_module_key . "_" . $service_schema_key . "_" . strtolower($operation_key) . "_" . $parameter_key  . "_description");

                                    if(!array_key_exists("allowMultiple", $arrParameters[$parameter_key]))
                                        $arrParameters[$parameter_key]["allowMultiple"] = false;
                                    if(!array_key_exists("dataType", $arrParameters[$parameter_key]))
                                        $arrParameters[$parameter_key]["dataType"] = "string"; //boolean, string, FoldersAndFiles, Container, File, Folders
                                    if(!array_key_exists("paramType", $arrParameters[$parameter_key]))
                                        $arrParameters[$parameter_key]["paramType"] = "query";  //query, path, body
                                    if(!array_key_exists("required", $arrParameters[$parameter_key]))
                                        $arrParameters[$parameter_key]["required"] = false;
                                    if(!array_key_exists("defaultValue", $arrParameters[$parameter_key]))
                                        $arrParameters[$parameter_key]["defaultValue"] = false;
                                    
                                }
                            }
                            
                            $arrOperations[] = array(
                                "httpMethod" => $operation_key
                                , "summary" => ffTemplate::_get_word_by_code("doc_api_" . $service_module_key . "_" . $service_schema_key . "_" . strtolower($operation_key) . "_summary")
                                , "nickname" => "getContainers"
                                , "responseClass" => "Containers"
                                , "parameters" => array_values($arrParameters)
                                , "errorResponses" => array_values(array_key_exists("errorResponses", $operation_value) && is_array($operation_value["errorResponses"])
                                                    ? array_merge($default_errorResponses, $operation_value["errorResponses"])
                                                    : $default_errorResponses
                                                )
                                , "notes" => ffTemplate::_get_word_by_code("doc_api_" . $service_module_key . "_" . $service_schema_key . "_" . strtolower($operation_key) . "_notes")
                            );
                        }
                        $apis[$service_module_key . $service_schema_key] = array(
                            "path" => "/srv/" . $service_schema_key . "/" . $service_module_key
                            , "operations" => $arrOperations
                            , "description" => ffTemplate::_get_word_by_code("doc_api_" . $service_module_key . "_" . $service_schema_key . "_description")
                        );                        
                    }
                }
            }
        }
    }

    if(is_array($apis) && count($apis)) {
        $res = array(
            "basePath"  => "http://" . DOMAIN_INSET
            , "swaggerVersion"  => "1.1"
            , "apiVersion"  => "1.0"
            , "resourcePath" => "/srv"
            , "apis" => array_values($apis) 
            , "models" => array( //da finire
                "Containers" => array(
                    "id" => "Containers"
                    , "properties" => array(
                        "container" => array(
                            "type" => "Array"
                            , "description" =>   "An array of containers."
                            , "items" => array(
                                '$ref' => "Container"
                            )
                        )
                    )
                )
            )
            
        );
        
        
    }
	if ($bad_request)
		http_response_code(400);
	else
		http_response_code(200);


    echo ffCommon_jsonenc($res, true);
        
	//echo $res;
	exit;
    
    /*
    function get_service_file_name() {
        $service_params = array();
        $disable_service_process = true;

        $arrServiceFileName = glob(FF_DISK_PATH . "/library/" . THEME_INSET . "/service/include/*");
        if(is_array($arrServiceFileName) && count($arrServiceFileName)) {
            foreach($arrServiceFileName AS $real_service_name) {
                if(is_file($real_service_name) && strpos($real_service_name, "." . FF_PHP_EXT) !== false) {

                    require($real_service_name);
                }
            }
        }  
        if(count($service_params))
            krsort($service_params);

        return $service_params;
    }*/
?>