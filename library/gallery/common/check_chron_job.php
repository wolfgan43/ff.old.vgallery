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
 
 /* //da eliminare e mettere il mod notifier al posto
function check_chron_job($area = null) {
    $db = ffDB_Sql::factory();
    
    if(strlen($area)) {
        $sSQL_addit_where = " AND area = " . $db->toSql($area);
    }
    
    $sSQL = "SELECT * 
            FROM `notify_schedule` 
            WHERE 1
                $sSQL_addit_where
                AND 
                    TIMESTAMPDIFF(
                        DAY 
                        , TIMESTAMPADD(
                            DAY 
                            , `period`
                            , CONCAT(FROM_UNIXTIME(`last_update`, '%Y-%m-%d'), ' ', `hour`)
                        ) 
                        , CONCAT(CURDATE(), ' ', `hour`)
                    ) >= 0
                AND status > 0";
    $db->query($sSQL);
    if($db->nextRecord()) {
        $db_job = ffDB_Sql::factory();
        do {
            $job_path = FF_DISK_PATH . "/conf" . GALLERY_PATH_JOB . "/" . $db->getField("job", "Text", true);
            if(is_file($job_path . "/index." . FF_PHP_EXT) && filesize($job_path . "/index." . FF_PHP_EXT) > 0) {
                $last_job = $db->getField("last_job", "Text", true);
                
                require_once($job_path . "/index." . FF_PHP_EXT);

                $sSQL_job = "UPDATE 
                        `notify_schedule` 
                    SET 
                        `last_update` = " . $db->toSql(time()) . " 
                        , `last_job` = " . $db->toSql($last_job) . " 
                    WHERE 
                        `ID` = " . $db->toSql($db->getField("ID", "Number"));
                
                $db->execute($sSQL_job);
            } else {
                if(check_function("write_notification"))
                    write_notification("_chron_job", ffTemplate::_get_word_by_code("job_" . $db->getField("job", "Text", true) . "_not_exist"), "warning", $area);
            }

            break;
        } while($db->nextRecord());    
    }
}*/
