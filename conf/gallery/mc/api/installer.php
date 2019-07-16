<?php
/**
 * VGallery: CMS based on FormsFramework
 * Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  @package VGallery
 *  @subpackage core
 *  @author Alessandro Stucchi <wolfgan@gmail.com>
 *  @copyright Copyright (c) 2004, Alessandro Stucchi
 *  @license http://opensource.org/licenses/gpl-3.0.html
 *  @link https://github.com/wolfgan43/vgallery
 */
$referer 		= $_SERVER["HTTP_REFERER"];
$user_agent 	= $_SERVER["HTTP_USER_AGENT"];
$auth_user		= $_REQUEST["auth_name"];
$auth_password  = $_REQUEST["auth_value"];

if(strpos($user_agent, "FFCMS-") === 0 && $referer) {
    $db = ffDB_Sql::factory();

    $sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.* 
			FROM " . CM_TABLE_PREFIX . "mod_security_domains 
			WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db->toSql(str_replace("www.", "", $referer));
    $db->query($sSQL);
    if($db->nextRecord()) {
        //$ID_domain = $db->getField("ID", "Number",true);
        $ftp_ip = ($db->getField("ip_address", "Text", true)
            ? $db->getField("ip_address", "Text", true)
            : null
        );
        $ftp_host = $db->getField("nome", "Text", true);
        $ftp_user = $db->getField("ftp_user", "Text", true);
        $ftp_password = $db->getField("ftp_password", "Text", true);
        $ftp_path = $db->getField("ftp_path", "Text", true);

        $token = $db->getField("token", "Text", true);
        if($user_agent == $token) {
            echo ffCommon_jsonenc(updater_installer($ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip, $auth_user, $auth_password, $token, "execute"), true);
        } else {
            http_response_code("401");
        }
    }
}
exit;

function updater_installer($ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip = null, $auth_user, $auth_password, $token, $action = "check") {
	$strError = "";
	$count_check_file = 0;
	$arrBasicInstallFile = array();

	$directory = new RecursiveDirectoryIterator(FF_DISK_PATH . "/conf/gallery/install");
	$files = new RecursiveIteratorIterator($directory);
	$files = array_keys(iterator_to_array($files));

	foreach($files as $file) {
		if(basename($file) != "." && basename($file) != ".." && basename(ffCommon_dirname($file)) != "shield")
			$arrBasicInstallFile[] = str_replace(FF_DISK_PATH, "", $file);
	}

	$directory = new RecursiveDirectoryIterator(FF_DISK_PATH . "/conf/gallery/updater");
	$files = new RecursiveIteratorIterator($directory);
	$files = array_keys(iterator_to_array($files));

	foreach($files as $file) {
		if(basename($file) != "." && basename($file) != "..")
			$arrBasicInstallFile[] = str_replace(FF_DISK_PATH, "", $file);
	}

	if(!$ftp_ip)
		$ftp_ip = gethostbyname($ftp_host);

	if($ftp_ip === false && strpos($ftp_host, "www.") === false)
		gethostbyname("www." . $ftp_host);

	$server_ip = gethostbyname($_SERVER["HTTP_HOST"]);
	if($ftp_ip == $server_ip)
		$ftp_host = "localhost";

	if(strlen($ftp_host) && strlen($ftp_user) && strlen($ftp_password) && strlen($ftp_path)) {
		if($ftp_ip)
			$conn_id = @ftp_connect($ftp_ip);
		if($conn_id === false)
			$conn_id = @ftp_connect($ftp_host, 21, 3);

		if($conn_id === false && $ftp_host == "localhost")
			$conn_id = @ftp_connect("127.0.0.1");
		if($conn_id === false && $ftp_host == "localhost")
			$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

		if($conn_id === false && strpos($ftp_host, "www.") === false && $ftp_host != "localhost")
			$conn_id = @ftp_connect("www." . $ftp_host, 21, 3);

		if($conn_id !== false) {
			// login with username and password
			if(@ftp_login($conn_id, $ftp_user, $ftp_password)) {
				$local_path = $ftp_path;
				$part_path = "";
				$real_ftp_path = NULL;

				if(@ftp_chdir($conn_id, $local_path)) {
					$real_ftp_path = $local_path;
				}

				if($real_ftp_path !== NULL) {
					foreach($arrBasicInstallFile AS $arrBasicInstallFile_value) {
						if($action == "execute") {
							$part_path = "";
							foreach(explode("/", ffCommon_dirname($arrBasicInstallFile_value)) AS $tmp_path) {
								if(strlen($tmp_path)) {
									$part_path .= "/" . $tmp_path;

									if(!@ftp_chdir($conn_id, $real_ftp_path . $part_path)) {
										if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
											$strError .= ffTemplate::_get_word_by_code("creation_failure_directory") . " (" . $real_ftp_path . $part_path . ")" . "<br>";
									}
								}
							}

							if(@ftp_size($conn_id, $real_ftp_path . $arrBasicInstallFile_value) >= 0) {
								@ftp_delete($conn_id, $real_ftp_path . $arrBasicInstallFile_value);
							}
							$ret = @ftp_nb_put($conn_id
								, $real_ftp_path . $arrBasicInstallFile_value
								, FF_DISK_PATH . $arrBasicInstallFile_value
								, FTP_BINARY
								, FTP_AUTORESUME
							);

							while ($ret == FTP_MOREDATA) {

								// Do whatever you want
								// Continue uploading...
								$ret = @ftp_nb_continue($conn_id);
							}
							if ($ret != FTP_FINISHED) {
								$strError .= ffTemplate::_get_word_by_code("upload_failure_file") . " (" . $real_ftp_path . $arrBasicInstallFile_value . ")" . "<br>";
							} else {
								$count_check_file++;
							}
						} else {
							if(@ftp_size($conn_id, $real_ftp_path . $arrBasicInstallFile_value) >= 0) {
								$count_check_file++;
							}
						}
					}
					if($action == "execute") {
						$config_updater_path = "/themes/site/conf/config.updater.php";

						$part_path = "";
						foreach(explode("/", ffCommon_dirname($config_updater_path)) AS $tmp_path) {
							if(strlen($tmp_path)) {
								$part_path .= "/" . $tmp_path;

								if(!@ftp_chdir($conn_id, $real_ftp_path . $part_path)) {
									if(!@ftp_mkdir($conn_id, $real_ftp_path . $part_path))
										$strError .= ffTemplate::_get_word_by_code("creation_failure_directory") . " (" . $real_ftp_path . $part_path . ")" . "<br>";
								}
							}
						}

						$config_updater_content = file_get_contents(FF_DISK_PATH . "/conf/gallery/install/config-updater.tpl");
						$vars = array(
							"[FTP_USERNAME]"						=> $ftp_user
							, "[FTP_PASSWORD]"						=> $ftp_password
							, "[FTP_PATH]"							=> ($ftp_path ? $ftp_path : "/")
							, "[AUTH_USERNAME]"						=> $auth_user
							, "[AUTH_PASSWORD]"						=> $auth_password
							, "[MASTER_SITE]"						=> DOMAIN_INSET
							, "[MASTER_TOKEN]"						=> $token
						);
						$config_updater_content = str_replace(array_keys($vars), array_values($vars), $config_updater_content);

						$tempHandle = @tmpfile();
						@fwrite($tempHandle, $config_updater_content);
						@rewind($tempHandle);

						if(@ftp_size($conn_id, $real_ftp_path . $config_updater_path) >= 0) {
							@ftp_delete($conn_id, $real_ftp_path . $config_updater_path);
						}

						$ret = @ftp_nb_fput($conn_id
							, $real_ftp_path . $config_updater_path
							, $tempHandle
							, FTP_BINARY
							, FTP_AUTORESUME
						);
						while ($ret == FTP_MOREDATA) {
							// Do whatever you want
							// Continue upload...
							$ret = @ftp_nb_continue($conn_id);
						}
						if ($ret != FTP_FINISHED) {
							$strError .= ffTemplate::_get_word_by_code("upload_failure_file") . " (" . $real_ftp_path . $config_updater_path . ")" . "<br>";
						}
					}
				} else {
					$strError = ffTemplate::_get_word_by_code("ftp_unavaible_root_dir");
				}
			} else {
				$strError = ffTemplate::_get_word_by_code("ftp_access_denied");
			}
		} else {
			$strError = ffTemplate::_get_word_by_code("ftp_connection_failure");
		}
		// close the connection and the file handler
		@ftp_close($conn_id);
	} else {
		$strError = ffTemplate::_get_word_by_code("ftp_not_configutated");
	}

	return array("total" => count($arrBasicInstallFile), "count" => $count_check_file, "error" => $strError);
}


