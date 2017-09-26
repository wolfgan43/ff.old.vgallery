<?php
/**
*   VGallery: CMS based on FormsFramework
    Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */
function write_chron_job_file($arrChronJob, $chron_job_file = null)
{
    $chron_job_error = false;
    if($chron_job_file === null) {
        $chron_job_file = "/conf" . GALLERY_PATH . "/config/chronjob." . FF_PHP_EXT;
    }
    if(is_file(FF_DISK_PATH . $chron_job_file)) {
        if(is_array($arrChronJob) && count($arrChronJob)) {
            $chron_job_content = "<?php\n";
            foreach($arrChronJob AS $key => $value) {
                if(is_array($value)) {
                    foreach($value AS $sub_key => $sub_value) {
                        if(strlen($sub_value))
                            $chron_job_content .= '$chronjob["' . $key . '"]["' . $sub_key . '"] = "' . addslashes($sub_value) . '";' . "\n";
                        else
                            $chron_job_content .= '$chronjob["' . $key . '"]["' . $sub_key . '"] = "";' . "\n";
                    }
                } else {
                    if(strlen($value))
                        $chron_job_content .= '$chronjob["' . $key . '"] = "' . addslashes($value) . '";' . "\n";
                    else
                        $chron_job_content .= '$chronjob["' . $key . '"] = "";' . "\n";
                }
            }                

            if(strlen($chron_job_content)) {
                if(defined("FTP_USERNAME") && strlen(FTP_USERNAME) && defined("FTP_PASSWORD") && strlen(FTP_PASSWORD)) {
                    // set up basic connection
                    /*$conn_id = @ftp_connect(DOMAIN_INSET);
                    if($conn_id === false && strpos(DOMAIN_INSET, "www.") === false) {
                        $conn_id = @ftp_connect("www." . DOMAIN_INSET);
                    }*/
                    $conn_id = @ftp_connect("localhost");
			        if($conn_id === false)
        				$conn_id = @ftp_connect("127.0.0.1");
					if($conn_id === false)
        				$conn_id = @ftp_connect($_SERVER["SERVER_ADDR"]);

                    if($conn_id !== false) {
                        // login with username and password
                        if(@ftp_login($conn_id, FTP_USERNAME, FTP_PASSWORD)) {
                            $local_path = FF_DISK_PATH;
                            $part_path = "";
                            $real_ftp_path = NULL;
                            
                            foreach(explode("/", $local_path) AS $curr_path) {
                                if(strlen($curr_path)) {
                                    $ftp_path = str_replace($part_path, "", $local_path);
                                    if(@ftp_chdir($conn_id, $ftp_path)) {
                                        $real_ftp_path = $ftp_path;
                                        break;
                                    } 

                                    $part_path .= "/" . $curr_path;
                                }
                            }
                            if($real_ftp_path !== NULL) {
                                $handle = @tmpfile();
                                @fwrite($handle, $chron_job_content);
                                @fseek($handle, 0);
                                if(!@ftp_fput($conn_id, $real_ftp_path . $chron_job_file, $handle, FTP_ASCII)) {
                                    $chron_job_error = ffTemplate::_get_word_by_code("unable_write_file");
                                } else {
                                    if(@ftp_chmod($conn_id, 0777, $real_ftp_path . $chron_job_file) === false) {
                                        if(@chmod(FF_DISK_PATH . $chron_job_file, 0777) === false) {
                                            $chron_job_error = ffTemplate::_get_word_by_code("unavailable_change_permission");
                                        }
                                    }
                                }
                                @fclose($handle);

                                $file_chmod = "644";
                                if(substr(decoct( @fileperms(FF_DISK_PATH . $chron_job_file)), 3) != $file_chmod) {
                                    $file_chmod = octdec(str_pad($file_chmod, 4, '0', STR_PAD_LEFT)); 
                                    if (@ftp_chmod($conn_id, $file_chmod, $real_ftp_path . $chron_job_file) === false) {
                                        if(@chmod(FF_DISK_PATH . $chron_job_file, $file_chmod) === false) {
                                            $chron_job_error = ffTemplate::_get_word_by_code("unavailable_change_permission");
                                        }
                                    }
                                }
                            } else {
                                $chron_job_error = true;
                            }
                        } else {
                            $chron_job_error = true;
                        }
                    } else {
                        $chron_job_error = true;
                    }
                } else {
                    $chron_job_error = true;
                }
            } else {
                $chron_job_error = true;
            }
        } else {
            $chron_job_error = true;
        }
        
        if($chron_job_error) {
            @unlink(FF_DISK_PATH . $chron_job_file);
        }
    }
    return $chron_job_error;
}


function check_chron_job_by_file($chron_job_file = null) {
    if($chron_job_file === null) {
        $chron_job_file = "/conf" . GALLERY_PATH . "/config/chronjob." . FF_PHP_EXT;
    }

    if(is_file(FF_DISK_PATH . $chron_job_file)) {
        require_once(FF_DISK_PATH . $chron_job_file);

        if(is_array($chronjob) && count($chronjob)
            && array_key_exists("job", $chronjob) && is_array($chronjob["job"]) && count($chronjob["job"])
            && array_key_exists("every_sec", $chronjob) && $chronjob["every_sec"] > 0
        ) {
            $actual_time = time();

            if($actual_time > $chronjob["lastupdate"] + $chronjob["every_sec"]) {
                if(is_array($chronjob["job"]) && count($chronjob["job"])) {
                    foreach($chronjob["job"] AS $job_key => $job_value) {
                        $job_path = FF_DISK_PATH . "/conf" . GALLERY_PATH_JOB . "/" . $job_key;
                        if(is_file($job_path . "/index." . FF_PHP_EXT) && filesize($job_path . "/index." . FF_PHP_EXT) > 0) {
                            require_once($job_path . "/index." . FF_PHP_EXT);
                        } else {
                            if(check_function("write_notification"))
                                write_notification("_chron_job_by_file", ffTemplate::_get_word_by_code("job_" . $job_key . "_not_exist"), "warning", "");
                        }
                    }
                }
                $chronjob["lastupdate"] = $actual_time;
                
                if(write_chron_job_file($chronjob, $chron_job_file) && check_function("write_notification")) {
                    write_notification("_chron_job_by_file", ffTemplate::_get_word_by_code("chron_job_by_file_removed"), "warning", "");
                }
            }
        }
    }
}
