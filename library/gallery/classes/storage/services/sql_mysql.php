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


class storageMysql
{
    const TYPE                                              = "sql";

    private $device                                         = null;
    private $config                                         = null;
    private $data                                           = null;
    private $storage                                        = null;

    public function __construct($storage, $data = null, $config = null)
    {
        $this->storage = $storage;
        $this->setConfig($config);
        $this->setData($data);

        if (!class_exists("ffDB_Sql"))
            require_once($this->storage->getAbsPathPHP("/ff/classes/ffDb_Sql/ffDb_Sql_mysqli"));

        $this->device = new ffDB_Sql();
        //$this->device->on_error = "ignore";

        if ($this->device->connect($this->config["name"], $this->config["host"], $this->config["username"], $this->config["password"])) {
            $this->config["key"] = "ID";
        } else {
            $this->device = false;
        }
        $storage->convertData("_id", "ID");
        $storage->convertWhere("_id", "ID");
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
            if (is_file($this->storage->getAbsPathPHP("/config")))
            {
                require_once($this->storage->getAbsPathPHP("/config"));

                $this->config["host"] = (defined("FF_DATABASE_HOST")
                    ? FF_DATABASE_HOST
                    : "localhost"
                );
                $this->config["name"] = (defined("FF_DATABASE_NAME")
                    ? FF_DATABASE_NAME
                    : ""
                );
                $this->config["username"] = (defined("FF_DATABASE_USER")
                    ? FF_DATABASE_USER
                    : ""
                );
                $this->config["password"] = (defined("FF_DATABASE_PASSWORD")
                    ? FF_DATABASE_PASSWORD
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