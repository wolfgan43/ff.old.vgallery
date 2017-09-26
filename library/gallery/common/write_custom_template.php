<?php
function write_custom_template($filename, $content, $action = "update", $base_path = null) {
	$arrError = array();
	if(!$base_path)
		$base_path = FF_THEME_DIR . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH;

	if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) 
	{
		$conn_id = @ftp_connect("localhost");
	    if($conn_id === false)
        	$conn_id = @ftp_connect("127.0.0.1");
		if($conn_id === false)
        	$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

		if($conn_id !== false) 
		{
		    // login with username and password
		    if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) 
			{
				$local_path = FF_DISK_PATH;
				$part_path = "";
				$real_ftp_path = NULL;
				foreach(explode("/", $local_path) AS $curr_path) 
				{
					if(strlen($curr_path)) 
					{
						$ftp_path = str_replace($part_path, "", $local_path);
						if(@ftp_chdir($conn_id, $ftp_path)) {
							$real_ftp_path = $ftp_path;
							break;
						} 

						$part_path .= "/" . $curr_path;
					}
				}
				if($real_ftp_path === NULL && defined("FTP_PATH") && strlen(FTP_PATH)) {
					if(@ftp_chdir($conn_id, FTP_PATH)) {
						$real_ftp_path = FTP_PATH;
					} 
				}

				if($real_ftp_path !== NULL) {
					$file_ext = "html";

					if($action == "delete") {
						if(is_file(FF_DISK_PATH . $base_path . "/" . $filename . "." . "html")) {
							if (!@ftp_delete($conn_id, $real_ftp_path . $base_path . "/" . $filename . "." . "html")) {
								$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_remove_file");
							}
						}
						$file_ext = "bkp";
					}
					if($content) {
						if(is_file(FF_DISK_PATH . $base_path . "/" . $filename . "." . "bkp")) {
							if (!@ftp_delete($conn_id, $real_ftp_path . $base_path . "/" . $filename . "." . "bkp")) {
								$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_remove_file_bkp");
							}
						}
					
						$handle = @tmpfile();
						@fwrite($handle, $content);
						@fseek($handle, 0);

						if(!ftp_fput($conn_id, $real_ftp_path . $base_path . "/" . $filename . "." . $file_ext, $handle, FTP_ASCII)) {
							$arrError[] = ffTemplate::_get_word_by_code("unable_write_file");
						} else {
							if(@ftp_chmod($conn_id, 0644, $real_ftp_path . $base_path . "/" . $filename . "." . $file_ext) === false) {
								if(@chmod(FF_DISK_PATH . $base_path . "/" . $filename . "." . "html", 0644) === false) {
									$arrError[] = ffTemplate::_get_word_by_code("unavailable_change_permission");
								}
							}
						}
						@fclose($handle);					
					}
				} else {
					$arrError[] = ffTemplate::_get_word_by_code("ftp_unavailable_root_dir");
				}
		    } else {
		        $arrError[] = ffTemplate::_get_word_by_code("ftp_access_denied");
		    }
		} else {
		    $arrError[] = ffTemplate::_get_word_by_code("ftp_connection_failure");
		}
		// close the connection and the file handler
		@ftp_close($conn_id);
	} else {
		$arrError[] = ffTemplate::_get_word_by_code("ftp_not_configutated");
	}

	return implode("<br />" , $arrError);
}