<?php
$permission = check_collection_permission();
if($permission !== true && !(is_array($permission) && count($permission))) {
    ffRedirect(FF_SITE_PATH . "/login" . "?ret_url=" . urlencode($_SERVER["REQUEST_URI"]) . "&relogin");
}

$cm = cm::getInstance();
$db = ffDB_Sql::factory();

if(isset($_REQUEST["exec"]) && $_REQUEST["exec"])
{
	if(isset($_POST["book"]))
	{
		$db = ffDB_Sql::factory();
		$book = json_decode($_POST["book"], true);
		if(is_array($book) && count($book)) {
                    foreach($book AS $key => $value)
			{
                                $list_ean = $value["list_ean"];
                                $list_title = $value["list_title"];
				$title = trim($value["title"]);
				$url = trim($value["url"]);
				
				$cover = trim($value["detail"][0]["copertina"]);
				$isbn10 = trim($value["detail"][0]["isbn10"]);
				$isbn13 = trim($value["detail"][0]["isbn13"]);
				$year = trim($value["detail"][0]["year"]);
				$length = trim($value["detail"][0]["length"]);
                                if(strpos($length, " ") > 0) {
                                    $length = substr($length, 0, strlen($length - strpos($length, " ")));
                                }
                                        
				$description = trim($value["detail"][0]["description"]);
				
				if(strlen(trim($value["detail"][0]["author"])))
				{
                                    $author = trim($value["detail"][0]["author"]);
                                    $arrIDAuthor = array();
                                    if(strpos($author,", "))
                                            $arrAuthor = explode(", ", $author);
                                    else
                                            $arrAuthor[] = $author;
				    foreach($arrAuthor AS $author_key => $author_value) 
                                    {
					$sSQL = "SELECT cm_mod_collection_book_author.*
								FROM cm_mod_collection_book_author
								WHERE cm_mod_collection_book_author.name = " . $db->toSql($author_value);
					$db->query($sSQL);
					if($db->nextRecord()) {
						$ID_author = $db->getField("ID", "Number", true);
					} else {
						$sSQL = "INSERT INTO cm_mod_collection_book_author
									(	
										ID
										, name
										, smart_url
									) VALUES
									(
										null
										, " . $db->toSql($author_value) . "
										, " . $db->toSql(ffCommon_url_rewrite($author_value)) . "
									)";
						$db->execute($sSQL);
						$ID_author = $db->getInsertID(true);
					}
                                        if(!array_key_exists($ID_author, $arrIDAuthor))
                                            $arrIDAuthor[$ID_author] = $ID_author;
                                    }
				}
				if(is_array($arrIDAuthor) && count($arrIDAuthor))
                                    $stringAuthor = implode(",", $arrIDAuthor);
                                
				if(strlen(trim($value["detail"][0]["edizione"])))
				{
					$edizione = trim($value["detail"][0]["edizione"]);
					$sSQL = "SELECT cm_mod_collection_book_editore.*
								FROM cm_mod_collection_book_editore
								WHERE cm_mod_collection_book_editore.name = " . $db->toSql($edizione);
					$db->query($sSQL);
					if($db->nextRecord()) {
						$ID_edizione = $db->getField("ID", "Number", true);
					} else {
						$sSQL = "INSERT INTO cm_mod_collection_book_editore
									(	
										ID
										, name
										, smart_url
									) VALUES
									(
										null
										, " . $db->toSql($edizione) . "
										, " . $db->toSql(ffCommon_url_rewrite($edizione)) . "
									)";
						$db->execute($sSQL);
						$ID_edizione = $db->getInsertID(true);
					}
				}
                                
                                if(strlen($cover)) {
                                    $saved_images = save_image($cover, ffCommon_url_rewrite($title), "/collector/book/");
                                } else {
                                    $saved_images = "";
                                }
                                
				$sSQL = "INSERT INTO cm_mod_collection_book
						(	
							ID
							, title
							, smart_url
							, cover
							, isbn10
							, isbn13
							, year
							, page
							, description
							, author
							, edizione
						) VALUES
						(
							null
							, " . $db->toSql($title) . "
							, " . $db->toSql(ffCommon_url_rewrite($title)) . "
							, " . $db->toSql($saved_images) . "
							, " . $db->toSql($isbn10) . "
							, " . $db->toSql($isbn13) . "
							, " . $db->toSql($year) . "
							, " . $db->toSql($length) . "
							, " . $db->toSql($description) . "
							, " . $db->toSql($stringAuthor) . "
							, " . $db->toSql($ID_edizione) . "
						)";
			$db->execute($sSQL);
			$ID_book = $db->getInsertID(true);
			}
                        $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse);
                        if(strlen($list_title))
                        {
                            $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse . '/modify/list-title?list_title=' . $list_title);
                        } elseif(strlen($list_ean))
                        {
                            $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse . '/modify/list-ean?list_ean=' . $list_ean);
                        }
                        if (isset($_REQUEST["XHR_DIALOG_ID"])) {
                            die(ffCommon_jsonenc(array("url" => $cm->router->named_rules["collection_book"]->reverse . '/modify?keys[ID]=' . $ID_book . $ret_url, "close" => true, "refresh" => true, "doredirects" => true), true));
                        } else {
                            ffRedirect($cm->router->named_rules["collection_book"]->reverse . '/modify?keys[ID]=' . $ID_book . $ret_url);
                        }
                        
		}
	}
	if(isset($_POST["obj"]))
	{
		$res = array();
		$obj = json_decode($_POST["obj"], true);
		$tplDetail = html_entity_decode(utf8_encode(file_get_contents(key($obj))));
		if(strlen($tplDetail))
			echo ffCommon_jsonenc(array("tpl" => $tplDetail), true);
	}
        if(isset($_POST["choose"]))
	{
            $choose = json_decode($_POST["choose"], true);
            $filename = cm_cascadeFindTemplate("/contents/template-decision.html", "collection");
            //$filename = cm_moduleCascadeFindTemplate(CM_MODULES_ROOT . "/collection/themes", "/contents/template-decision.html", $cm->oPage->theme);

            $tpl = ffTemplate::factory(ffCommon_dirname($filename));
            $tpl->load_file("template-decision.html", "main"); 
            
            $max = 8;
            if(count($choose) < 8)
                $max = count($choose);
            $i = 0; 
            $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse);
            
            foreach($choose AS $key => $value)
            {
                if(strlen($value["list_title"])) {
                    $params = "&list-title=" . $value["list_title"];
                    $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse . '/modify/list-title?list_title=' . $value["list_title"]);
                } elseif(strlen($value["list_ean"])) {
                    $params = "&list-ean=" . $value["list_ean"];
                    $ret_url = "&ret_url=" . urlencode($cm->router->named_rules["collection_book"]->reverse . '/modify/list-ean?list_ean=' . $value["list_ean"]);
                }
                if($i < $max) {
                    $tpl->set_var("choose_elem_link", $cm->router->named_rules["collection_book"]->reverse . "/scraping/google/?params=" . $value["params"] . $params . $ret_url);
                    $tpl->set_var("choose_elem_title", $value["title"]);
                    if(strlen($value["author"]))
                    {
                        $tpl->set_var("choose_elem_detail", $value["author"]);
                        $tpl->parse("SezElemDetail", false);
                    }
                    
                    
                    $tpl->parse("SezElemChoose", true);
                } else {
                    break;
                }
                $i++;
            }
            $tpl->set_var("choose_title_modify_already_stored", ffTemplate::_get_word_by_code("mod_collection_title_source_db"));
            $tpl->set_var("choose_manually_link", $cm->router->named_rules["collection_book"]->reverse . "/modify/manually?" . $params . $ret_url);
            $tpl->set_var("media_type", "book");
            $buffer = $tpl->rpparse("main", false);
            echo ffCommon_jsonenc(array("tpl" => $buffer), true);
	}
	if(isset($_POST["params"]))
	{
            $tplListBody = array();
            $params = json_decode($_POST["params"], true);
            $tplList = file_get_contents($params);
            if(strlen($tplList))
                echo ffCommon_jsonenc(array("tpl" => $tplList), true);
	}
	
	
	exit;
}

$filename = CM_MODULES_ROOT . "/collection/contents" . $cm->router->named_rules["collection"]->reverse . "/book/scraping/google/template.html";

$tpl = ffTemplate::factory(ffCommon_dirname($filename));
$tpl->load_file("template.html", "main");
if(isset($_REQUEST["list-title"]))
    $tpl->set_var("list_title", ffCommon_specialchars($_REQUEST["list-title"]));
elseif(isset($_REQUEST["list-ean"]))
    $tpl->set_var("list_ean", ffCommon_specialchars($_REQUEST["list-ean"]));
if(isset($_REQUEST["title"]))
	$tpl->set_var("url", "https://www.google.it/search?q=" . str_replace("-", "+",$_REQUEST["title"]) . "&tbm=bks");
elseif(isset($_REQUEST["ean"]))
	$tpl->set_var("url", "https://www.google.it/search?q=" . $_REQUEST["ean"] . "&tbm=bks");
else
    $tpl->set_var("params", "https://www.google.it/books?id=" . $_REQUEST["params"]);
    
$buffer = $tpl->rpparse("main", false);

$cm->oPage->addContent($buffer);