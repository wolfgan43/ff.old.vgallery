<?php
function write($source, $type = "xml", $dest = null, $name = null) {
	switch($type)
	{
		case "nosql":
			write_array2MongoDB($source, $dest, $name);
			break;
		case "json":
			write_array2JSONfile($source, $dest, $name);
			break;
		case "php":
			write_array2PHPfile($source, $dest, $name);
			break;
		case "xml":
		default:
			write_array2xml($source, $dest, $name);
	}
}

function write_array2MongoDB($source, $tbl_name, $db_name = TRACE_MONGO_DATABASE_NAME, $db_host = TRACE_MONGO_DATABASE_HOST, $db_user = TRACE_MONGO_DATABASE_USER, $db_pw = TRACE_MONGO_DATABASE_PASSWORD) 
{
    if(!class_exists("ffDB_MongoDB"))
        require_once(TRACE_DISK_PATH . "/ff/classes/ffDB_Mongo/ffDb_MongoDB.php");

    $db = new ffDB_MongoDB();
    $db->on_error = "ignore";
    
    $db->connect($db_name, $db_host, $db_user, $db_pw);
    $db->insert($source, $tbl_name);
}

function write_array2JSONfile($source, $dest = null)
{
	if(!$dest)
		$dest = FF_DISK_PATH . "/cache/" . "default" . ".json";

	write_data2file(ffCommon_jsonenc($source, false), $dest);
}

function write_array2PHPfile($source, $dest = null, $var_name = null)
{
	if(!$dest)
		$dest = FF_DISK_PATH . "/cache/" . ($var_name ? $var_name : "default") . ".php";
	elseif(!$var_name)
		$var_name = "default";

	write_data2file("<?php\n\n" . '$' . $var_name . ' = ' . var_export($source, true) . ";\n\n", $dest);
}


function write_data2file($data, $dest)
{
	@file_put_contents($dest, $data, LOCK_EX);
}

function write_array2xml($source, $dest = null, $root = null) {
	
	if(!$dest)
		$dest = FF_THEME_DISK_PATH . "/" . FRONTEND_THEME . "/xml/" . ($root ? $root : "default") . ".xml";
	elseif(!$root)
		$root = ffGetFilename($dest);

	// create new instance of simplexml
	$xml = new SimpleXMLElement('<' . $root . '/>');

	// function callback
	array2xml($xml, $source);

	if(!is_dir(ffCommon_dirname($dest)))
		@mkdir(ffCommon_dirname($dest), 0777, true);

	@file_put_contents($dest, str_replace("><", ">\n<", $xml->asXML()), LOCK_EX);			
}

function array2xml($obj, $array)
{
    foreach ($array as $key => $value)
    {
        if(is_numeric($key))
            $key = 'item' . $key;

        if (is_array($value))
        {
            $node = $obj->addChild($key);
            array2xml($node, $value);
        }
        else
        {
            $obj->addChild($key, htmlspecialchars($value));
        }
    }
}


function xml2array ( $xmlObject, $out = "" )
{
    foreach ( (array) $xmlObject as $index => $node )
        $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;

    return $out;
}

