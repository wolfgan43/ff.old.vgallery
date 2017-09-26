<?php
	$db = ffDB_Sql::factory();
	
	$sSQL = get_session("mod_attendance_photo_sql");
	if(strlen($sSQL)) {
		$db->query($sSQL);
		if($db->nextRecord()) {
			$i = 1;
			do { 
				$arrFile[ffCommon_toggle_hypens($db->getField("office_name", "Text", true)) . "/" . ffCommon_toggle_hypens($db->getField("office_name", "Text", true)) . (strlen($db->getField("event_name", "Text", true)) ? " - " . ffCommon_toggle_hypens($db->getField("event_name", "Text", true)) : "") . (strlen($db->getField("argument_name", "Text", true)) ? " - " . ffCommon_toggle_hypens($db->getField("argument_name", "Text", true)) : "") . (strlen($db->getField("argument_detail_name", "Text", true)) ? " - " . ffCommon_toggle_hypens($db->getField("argument_detail_name", "Text", true)) : "")][] = $db->getField("path", "Text", true);
				
				$i++;
			} while($db->nextRecord());
		}

		if(is_array($arrFile) && count($arrFile)) {
			foreach($arrFile AS $arrFile_key => $arrFile_value) {
				if(is_array($arrFile_value) && count($arrFile_value)) {
					foreach($arrFile_value AS $count => $path) {
						$file_to_zip[$path] = $arrFile_key . " - " . $count . "." . ffGetFilename($path, false);
					}	
				}
			}
			$archive_file_name = md5(json_encode($file_to_zip)) . ".zip";
			
			@mkdir(DISK_UPDIR . "/attendance/archive", 0777);
			if(!file_exists(DISK_UPDIR . "/attendance/archive/" . $archive_file_name)) {
				create_zip($file_to_zip, "/attendance/archive/" . $archive_file_name, true);
			}

			header("Content-type: application/zip");
			header("Content-Disposition: attachment; filename=photo.zip");
			header("Pragma: no-cache");
			header("Expires: 0");
			readfile(DISK_UPDIR . "/attendance/archive/" . $archive_file_name);
			exit;
		}	
	}
	
/* creates a compressed zip file */
function create_zip($files = array(),$destination = '',$overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if(file_exists(DISK_UPDIR . $destination) && !$overwrite) { return false; }
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file => $fake_name) {
			//make sure the file exists
			if(file_exists(DISK_UPDIR . $file)) {
				$valid_files[$file] = $fake_name;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open(DISK_UPDIR . $destination, $overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
			return false;
		}
		//add the files
		foreach($valid_files as $file => $fake_name) {
			$zip->addFile(DISK_UPDIR . $file, $fake_name);
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		
		//check to make sure the file exists
		return file_exists(DISK_UPDIR . $destination);
	}
	else
	{
		return false;
	}
}

?>
