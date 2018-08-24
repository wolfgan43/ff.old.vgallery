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

spl_autoload_register(function ($class) {
    switch ($class) {
        case "Api":
        case "Auth":
        case "Anagraph":
        case "Cache":
        case "Cms":
        case "Filemanager":
        case "Mailer":
        case "Notifier":
        case "Stats":
        case "Storage":
        case "Jobs":
        case "Util":
            require(__CMS_DIR__ . "/library/gallery/models/" . strtolower($class) . "/" . $class . ".php");
            break;
        case "vgCommon":
            require(__CMS_DIR__ . "/library/gallery/models/" . $class . ".php");
            break;
        case "ffDB_Sql";
        case "ffDb_Sql":
            require(__TOP_DIR__  . "/ff/classes/ffDb_Sql/ffDb_Sql_mysqli.php");
            break;
        case "ffDB_MongoDB";
        case "ffDb_MongoDB";
            require_once(__TOP_DIR__ . "/ff/classes/ffDB_Mongo/ffDb_MongoDB.php");
            break;
        case "ffTemplate";
            require(__TOP_DIR__  . "/ff/classes/ffTemplate.php");
            break;
        case "ffMedia":
            require(__TOP_DIR__  . "/ff/classes/ffMedia.php");
            break;
        default:
    }

    if(!(defined("COMPOSER_PATH") && COMPOSER_PATH)) {
        switch ($class) {
            case "PHPMailer":
            case "phpmailer":
                require(__TOP_DIR__ . "/library/phpmailer/class.phpmailer.php");
                require(__TOP_DIR__ . "/library/phpmailer/class.phpmaileroauth.php");
                require(__TOP_DIR__ . "/library/phpmailer/class.phpmaileroauthgoogle.php");
                require(__TOP_DIR__ . "/library/phpmailer/class.smtp.php");
                require(__TOP_DIR__ . "/library/phpmailer/class.pop3.php");
                require(__TOP_DIR__ . "/library/phpmailer/extras/EasyPeasyICS.php");
                require(__TOP_DIR__ . "/library/phpmailer/extras/ntlm_sasl_client.php");
                break;
            default:
        }
    }
});

