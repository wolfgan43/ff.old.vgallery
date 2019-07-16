<?php
/**
 *   VGallery: CMS based on FormsFramework
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
 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @link https://bitbucket.org/cmsff/vgallery
 */

if (!Auth::env("AREA_UPDATER_SHOW_MODIFY")) {
	ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

require_once(FF_DISK_PATH . "/conf" . GALLERY_PATH . "/updater/check/manifesto." . FF_PHP_EXT);

$db = ffDB_Sql::factory();

$cm->oPage->form_method = "POST";

$valid_domain = false;

$sSQL = "SELECT " . CM_TABLE_PREFIX . "mod_security_domains.* 
        FROM " . CM_TABLE_PREFIX . "mod_security_domains 
        WHERE " . CM_TABLE_PREFIX . "mod_security_domains.nome = " . $db->toSql(basename($cm->real_path_info));
$db->query($sSQL);
if($db->nextRecord()) {
	$ID_domain = $db->getField("ID", "Number",true);
	$ftp_ip = ($db->getField("ip_address", "Text", true)
		? $db->getField("ip_address", "Text", true)
		: null
	);
	$ftp_host = $db->getField("nome", "Text", true);
	$ftp_user = $db->getField("ftp_user", "Text", true);
	$ftp_password = $db->getField("ftp_password", "Text", true);
	$ftp_path = $db->getField("ftp_path", "Text", true);

	$token = $db->getField("token", "Text", true);
	$valid_domain = true;
} else {
	if(basename($cm->real_path_info) == DOMAIN_NAME) {
		$token = "FFCMS-" . time();
		$sSQL = "INSERT INTO " . CM_TABLE_PREFIX . "mod_security_domains 
                (
                    `ID`
                    , `nome`
                    , `creation_date`
                    , `status`
                    , `ip_address`
                    , `ftp_user`
                    , `ftp_password`
                    , `ftp_path`
                    , `token`
                ) 
                VALUES 
                (
                    NULL
                    , " . $db->toSql(DOMAIN_NAME, "Text") . "
                    , CURDATE()
                    , '1' 
                    , " . $db->toSql($_SERVER["REMOTE_ADDR"], "Text") . " 
                    , " . $db->toSql(FTP_USERNAME, "Text") . " 
                    , " . $db->toSql(FTP_PASSWORD, "Text") . " 
                    , " . $db->toSql(FTP_PATH, "Text") . " 
                    , " . $db->toSql($token, "Text") . "
                )";
		$db->execute($sSQL);
		$ID_domain = $db->getInsertID(true);
		$ftp_ip = null;
		$ftp_host = DOMAIN_NAME;
		$ftp_user = FTP_USERNAME;
		$ftp_password = FTP_PASSWORD;
		$ftp_path = FTP_PATH;

		$valid_domain = true;
	}
}

$res = force_install($token, $ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip, "execute");

if(!$res["error"]) {
	$tpl = ffTemplate::factory(__DIR__);
	$tpl->load_file("installer.html", "main");
	$tpl->set_var("site_path", FF_SITE_PATH);
	$tpl->set_var("domain", "http://" . basename($cm->real_path_info) . "/index.php");

	$cm->oPage->addContent($tpl);
} else {
	$cm->oPage->addContent($res["error"]);
}

function force_install($token, $ftp_host, $ftp_user, $ftp_password, $ftp_path, $ftp_ip = null, $action = "check") {
	$strError = "";
	$count_check_file = 0;
	$arrBasicInstallFile = array();

	/* $files = glob(FF_DISK_PATH . "/conf/gallery/install/*");
	 if(is_array($files) && count($files)) {
		 foreach($files AS $file) {
			 $arrBasicInstallFile = str_replace(FF_DISK_PATH, "", $file);
		 }
	 }*/

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
						$config_updater_path = "/index.php";

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

						$config_updater_content = file_get_contents(__DIR__ . "/shield.php");
						$config_updater_content = str_replace(array(
							"[DOMAIN]"
						, "[TOKEN]"
						), array(
							"http" . ($_SERVER["HTTPS"] ? "s" : ""). "://" . DOMAIN_INSET
						, $token
						), $config_updater_content);

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

