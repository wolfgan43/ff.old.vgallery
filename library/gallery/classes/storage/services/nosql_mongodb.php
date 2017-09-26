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

class storageMongodb {
    const TYPE                                              = "nosql";

    private $device                                         = null;
    private $config                                         = null;
    private $data                                           = null;
    private $storage                                        = null;

    public function __construct($storage, $data = null, $config = null)
    {
        $this->storage = $storage;
        $this->setConfig($config);
        $this->setData($data);

        if (!class_exists("ffDB_MongoDB"))
            require_once($storage->getAbsPath("/ff/classes/ffDB_Mongo/ffDb_MongoDB.php"));

        $this->device = new ffDB_MongoDB();
        $this->device->on_error = "ignore";

        if ($this->device->connect($this->config["name"], $this->config["host"], $this->config["username"], $this->config["password"])) {
            $this->config["key"] = "_id";
        } else {
            $this->device = false;
        }
        $storage->convertData("ID", "_id");
        $storage->convertWhere("ID", "_id");
    }

    public function getDevice()
    {
        return $this->device;
    }
    public function getConfig()
    {
        return $this->config;
    }
    private function setConfig($config = null)
    {
        $this->config = $this->storage->getConfig($this::TYPE);

        if (!$this->config["name"])
        {
            if (is_file($this->storage->getAbsPath("/conf/gallery/config/db." . FF_PHP_EXT)))
            {
                require_once($this->storage->getAbsPath("/conf/gallery/config/db." . FF_PHP_EXT));

                $this->config["host"] = (defined("FF_NOSQL_HOST")
                    ? FF_NOSQL_HOST
                    : "localhost"
                );
                $this->config["name"] = (defined("FF_NOSQL_NAME")
                    ? FF_NOSQL_NAME
                    : ""
                );
                $this->config["username"] = (defined("FF_NOSQL_USER")
                    ? FF_NOSQL_USER
                    : ""
                );
                $this->config["password"] = (defined("FF_NOSQL_PASSWORD")
                    ? FF_NOSQL_PASSWORD
                    : ""
                );
            }
        }
    }
    private function setData($data = null)
    {
        $this->data = $this->storage->getData("result", $data);
    }
}