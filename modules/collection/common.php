<?php

function save_image($url_file, $nome_file, $relative_path)
{
    set_time_limit(300);
    //Percorso file remoto
    $remotefile = pathinfo($url_file);
    if(!strlen($remotefile["extension"]))
    {
        $remotefile["extension"] = "jpg";
    }
    //Cartella locale in cui copiare il file
    $cartella = DISK_UPDIR . $relative_path; // cartella dove mettere immagini
    //apro il file remoto da leggere
    $srcfile1 = fopen($url_file, "r");
    //apro il file in locale
    if (!($fp1 = fopen($cartella . $nome_file . "." .$remotefile["extension"],"w")));
    //scrivo contenuto del file remoto, ora in temp file, in file locale
    while ($contents = fread( $srcfile1, 8192 )) {
        fwrite( $fp1, $contents, strlen($contents) );
    }
    //chiudo i due files
    fclose($srcfile1);
    fclose($fp1);
    
    return($relative_path . $nome_file . "." . $remotefile["extension"]);
}

function console_code()
{
    $console_code = array(
        "play-station" => array("ID" => 1
                                , "metacritic_code" => "plats%5B10%5D=1"
                                , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "play-station-2" => array("ID" => 2
                                , "metacritic_code" => "plats%5B6%5D=1"
                                , "universitaria_code" => "PS2%2CPlaystation+2"
                            )
	, "play-station-3" => array("ID" => 3
                                    , "metacritic_code" => "plats%5B1%5D=1"
                                    , "universitaria_code" => "PS3%2CPlaystation+3"
                            )
	, "play-station-4" => array("ID" => 4
                                    , "metacritic_code" => "plats%5B72496%5D=1"
                                    , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "psp" => array("ID" => 5
                            , "metacritic_code" => "plats%5B7%5D=1"
                            , "universitaria_code" => "PSP%2CSony+Psp"
                            )
	, "play-station-vita" => array("ID" => 6
                                        , "metacritic_code" => "plats%5B67365%5D=1"
                                        , "universitaria_code" => "PSV%2CPs+Vita"
                            )
	, "pc" => array("ID" => 7
                        , "metacritic_code" => "plats%5B3%5D=1"
                        , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "xbox" => array("ID" => 8
                                    , "metacritic_code" => "plats%5B12%5D=1"
                                    , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "xbox-360" => array("ID" => 9
                                    , "metacritic_code" => "plats%5B2%5D=1"
                                    , "universitaria_code" => "X360%2CXbox+360"
                            )
	, "xbox-one" => array("ID" => 10
                                , "metacritic_code" => "plats%5B80000%5D=1"
                                , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "nintendo-64" => array("ID" => 11
                                    , "metacritic_code" => "plats%5B14%5D=1"
                                    , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "gamecube" => array("ID" => 12
                                , "metacritic_code" => "plats%5B13%5D=1"
                                , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "wii" => array("ID" => 13
                            , "metacritic_code" => "plats%5B8%5D=1"
                            , "universitaria_code" => "WII%2CNintendo+Wii"
                            )
	, "wii-u" => array("ID" => 14
                            , "metacritic_code" => "plats%5B68410%5D=1"
                            , "universitaria_code" => "WIIU%2CWii+U"
                            )
	, "game-boy-advanced" => array("ID" => 15
                                        , "metacritic_code" => "plats%5B11%5D=1"
                                        , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
	, "nintendo-ds" => array("ID" => 16
                                    , "metacritic_code" => "plats%5B4%5D=1"
                                    , "universitaria_code" => "NDS%2CNintendo+DS"
                            )
	, "nintendo-3ds" => array("ID" => 17
                                    , "metacritic_code" => "plats%5B16%5D=1"
                                    , "universitaria_code" => "3DS%2CNintendo+3DS"
                            )
	, "dreamcast" => array("ID" => 18
                                , "metacritic_code" => "plats%5B15%5D=1"
                                , "universitaria_code" => "ALL%2CTutti+i+reparti+-+Videogames"
                            )
    );
    return $console_code;
}

function check_subtable_values($category, $key, $value)
{
    $db = ffDB_Sql::factory();
    
    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_collection_" . $category . "_" . $key . ".*
                FROM  " . CM_TABLE_PREFIX . "mod_collection_" . $category . "_" . $key . "
                WHERE  " . CM_TABLE_PREFIX . "mod_collection_" . $category . "_" . $key . ".smart_url = " . $db->toSql(ffCommon_url_rewrite($value));
    $db->query($sSQL);
    if($db->nextRecord()) {
            ${"ID_" . $key} = $db->getField("ID", "Number", true);
    } else 
    {
        $sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_collection_" . $category . "_" . $key . "
                    (	
                            ID
                            , name
                            , smart_url
                    ) VALUES
                    (
                            null
                            , " . $db->toSql($value) . "
                            , " . $db->toSql(ffCommon_url_rewrite($value)) . "
                    )";
        $db->execute($sSQL);
        ${"ID_" . $key} = $db->getInsertID(true);
    }
    return ${"ID_" . $key};
}

function check_collection_permission($check_group = null) {
    if(!MOD_SEC_GROUPS) 
        return true;

    $db = ffDB_Sql::factory();

    $user_permission = get_session("user_permission");
    $userID = get_session("UserID");

    if(is_array($user_permission) && count($user_permission) 
        && is_array($user_permission["groups"]) && count($user_permission["groups"])
        && $userID != "guest"
    ) {
        if(!array_key_exists("permissions_custom", $user_permission))
            $user_permission["permissions_custom"] = array();

        if(!(array_key_exists("collection", $user_permission["permissions_custom"]) && count($user_permission["permissions_custom"]["collection"]))) {
            $user_permission["permissions_custom"]["collection"] = array();
            
            $strGroups = implode(",", $user_permission["groups"]);
            $strPermission = $db->toSql(global_settings("MOD_COLLECTION_GROUP_ADMIN"), "Text") 
                            . "," . $db->toSql(global_settings("MOD_COLLECTION_GROUP_USER"), "Text"); 

            $user_permission["permissions_custom"]["collection"][global_settings("MOD_COLLECTION_GROUP_ADMIN")] = false;
            $user_permission["permissions_custom"]["collection"][global_settings("MOD_COLLECTION_GROUP_USER")] = false;
            $user_permission["permissions_custom"]["collection"]["primary_group"] = "";
            
            $sSQL = "SELECT DISTINCT " . CM_TABLE_PREFIX . "mod_security_groups.name
                        , (SELECT GROUP_CONCAT(anagraph.ID) FROM anagraph WHERE anagraph.uid = " . $db->toSql(get_session("UserNID"), "Number") . ") AS anagraph
                    FROM " . CM_TABLE_PREFIX . "mod_security_groups
                      INNER JOIN " . CM_TABLE_PREFIX . "mod_security_users_rel_groups ON " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid = " . CM_TABLE_PREFIX . "mod_security_groups.gid
                    WHERE " . CM_TABLE_PREFIX . "mod_security_users_rel_groups.gid IN ( " . $db->toSql($strGroups, "Text", false) . " )
                      AND " . CM_TABLE_PREFIX . "mod_security_groups.name IN ( " . $strPermission . " )";
            $db->query($sSQL);
            if($db->nextRecord()) {
                do {
                    $user_permission["permissions_custom"]["collection"][$db->getField("name", "Text", true)] = true;
                    $user_permission["permissions_custom"]["collection"]["primary_group"] = $db->getField("name", "Text", true);
                } while($db->nextRecord());
            }
            
            set_session("user_permission", $user_permission);
        }
        if($check_group === null) { 
            return $user_permission["permissions_custom"]["collection"];
        } else {
            return $user_permission["permissions_custom"]["collection"]["primary_group"];
        }
    }    
    return null;
}