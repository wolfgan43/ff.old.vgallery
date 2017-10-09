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
require_once(__DIR__ . "/../vgCommon.php");

class Filemanager extends vgCommon
{
    static $singleton                                                   = null;

    const SEARCH_IN_KEY                                                 = 1;
    const SEARCH_IN_VALUE                                               = 2;
    const SEARCH_IN_BOTH                                                = 3;

    const SEARCH_DEFAULT                                                = Filemanager::SEARCH_IN_KEY;

    protected $services                                                 = array(                //servizi per la scrittura o lettura della notifica
        "fs"                                                            => false
    );
    protected $controllers                                              = array(
        "fs"                                                            => array(
            "default"                                                   => null
            , "services"                                                => null
        )
    );
    protected $controllers_rev                                          = array();
    protected $path                                                     = null;
    protected $var                                                      = null;
    protected $keys                                                     = null;
    protected $data                                                     = null;
    protected $expires                                                  = null;

    private $result                                                     = array();

    /**
     * @param null $service
     * @param null $path
     * @param null $var
     * @return Filemanager|null
     */
    public static function getInstance($service = null, $path = null, $var = null)
    {
        if (self::$singleton === null) {
            self::$singleton                                            = new Filemanager($path, $service);
        } else {
            self::$singleton->services["fs"]                            = $service;
            self::$singleton->path                                      = $path;
            self::$singleton->var                                       = $var;
        }
        return self::$singleton;
    }

    /**
     * Filemanager constructor.
     * @param null $service
     * @param null $path
     * @param null $var
     */
    public function __construct($service = null, $path = null, $var = null)
    {
        $this->services["fs"]                                           = $service;
        $this->path                                                     = $path;
        $this->var                                                      = $var;
    }

    /**
     * @param null $path
     * @return bool
     */
    public function makeDir($path = null)
    {
        $rc = true;
        if(!$path)
            $path                                                       = $this->path;

        if(!is_dir($path))
            $rc                                                         = @mkdir($path, 0777, ture);

        return $rc;
    }

    /**
     * @param null $keys
     * @param null $path
     * @param null $flags
     * @return array|bool|mixed|null
     */
    public function read($keys = null, $path = null, $flags = null)
    {
        $this->clearResult();
        if($this->isError())
            return $this->isError();
        else {
            $this->action                                               = "read";
            $this->keys                                                 = $keys;
            $this->controller($path, $flags);
        }

        return $this->getResult();
    }

    /**
     * @param null $data
     * @param null $var
     * @param null $expires
     * @param null $path
     * @return array|bool|mixed|null
     */
    public function write($data = null, $var = null, $expires = null, $path = null)
    {
        $this->clearResult();
        if($this->isError())
            return $this->isError();
        else {

            $this->action                                               = "write";
            $this->data                                                 = $data;
            $this->expires                                              = $expires;
            $this->controller($path, $var);
        }

        return $this->getResult();
    }

    /**
     * @param null $data
     * @param null $var
     * @param null $expires
     * @param null $path
     * @return array|bool|mixed|null
     */
    public function update($data = null, $var = null, $expires = null, $path = null)
    {
        $this->clearResult();
        if($this->isError())
            return $this->isError();
        else {
            $this->action                                               = "update";
            $this->data                                                 = $data;
            $this->expires                                              = $expires;
            $this->controller($path, $var);
        }

        return $this->getResult();
    }

    /**
     * @param null $keys
     * @param null $path
     * @param null $flags
     * @return array|bool|mixed|null
     */
    public function delete($keys = null, $path = null, $flags = null)
    {
        $this->clearResult();
        if($this->isError())
            return $this->isError();
        else {
            $this->action                                               = "delete";
            $this->keys                                                 = $keys;
            $this->controller($path, $flags);
        }

        return $this->getResult();
    }

    /**
     * @param $buffer
     * @param null $expires
     * @param null $path
     * @return bool|int
     */
    public function save($buffer, $expires = null, $path = null)
    {
        if(!$path)
            $path                                                       = $this->path;

        $rc = $this->makeDir(dirname($path));
        if ($rc)
        {
            if ($rc = @file_put_contents($path, $buffer, LOCK_EX))
                @chmod($path, 0777);

            if ($rc && $expires !== null)
            {
                $this->touch($expires, $path);
            }
        }

        return $rc;
    }

    /**
     * @param $expires
     * @param null $path
     * @return bool
     */
    public function touch($expires, $path = null)
    {
        if(!$path)
            $path                                                       = $this->path;

        $rc                                                             = @touch($path, $expires);
        //if (!$rc)
        //    @unlink($path);

        return $rc;
    }

    /**
     * @param null $path
     * @return bool
     */
    public function isExpired($path = null)
    {
        if(!$path)
            $path                                                       = $this->path;

        return (filemtime($path) >= filectime($path)
            ? false
            : true
        );
    }

    /**
     * @param null $path
     * @param null $flags
     */
    private function controller($path = null, $flags = null)
    {
        if($path)
            $this->path                                                = $path;

        foreach($this->services AS $controller => $services)
        {
            $this->isError("");

            $funcController = "controller_" . $controller;
            $this->$funcController((is_array($services)
                ? $services["service"]
                : $services
            ), $flags);

            if($this->action == "read" && $this->result)
                break;
        }
    }

    /**
     * @param null $service
     * @param null $flags
     */
    private function controller_fs($service = null, $flags = null)
    {
        if(!$service)
            $service                                                    = $this->controllers["fs"]["default"];

        if($service)
        {
            $type                                                       = "fs";
            $controller                                                 = "filemanager" . ucfirst($service);
            require_once($this->getAbsPathPHP("/filemanager/services/" . $type . "_" . $service, true));

            $driver                                                     = new $controller($this);
            //$db                                                         = $driver->getDevice();
           // $config                                                     = $driver->getConfig();


            if(!$this->isError()) {
                switch($this->action)
                {
                    case "read":
                        $this->result = $driver->read($this->keys, $flags);
                        break;
                    case "update":
                        $this->result = $driver->update($this->data, $flags);
                        break;
                    case "write":
                        $this->result = $driver->write($this->data, $flags);
                        break;
                    case "delete":
                        $this->result = $driver->delete($this->keys, $flags);
                        break;
                    default:
                }
            }


        }


    }

    /**
     *
     */
    private function clearResult()
    {
        $this->keys                                                     = null;
        $this->data                                                     = null;
        $this->expires                                                  = null;
        $this->result                                                   = array();

        $this->isError("");
    }

    /**
     * @return array|bool|mixed|null
     */
    private function getResult()
    {
        return ($this->isError()
            ? false
            : ($this->result
                ? (is_array($this->keys) || count($this->result) > 1
                    ? $this->result
                    : array_shift($this->result)
                )
                : null
            )
        );
    }
}