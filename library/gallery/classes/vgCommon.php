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


abstract class vgCommon 
{
    private $error                      = null;
    private $debug                      = array();
    private $disk_path                  = null;

    public function getAbsPath($path, $use_class_path = false)
    {
        static $this_path = __DIR__;
        
        if(!$this->disk_path) {
            $this->disk_path = str_replace("/library/gallery/classes", "", __DIR__);
        }
        
        return ($use_class_path
            ? $this_path 
            : $this->disk_path
        ) . $path;
    }    
    public function addService($controller, $service = null) 
    {
        if($this->controllers[$controller])
        {
            if(!$service)
                $service = $this->controllers[$controller]["default"];

            $this->services[$controller] = $service;
        }
    }
    public function debug($note = null, $params = null)
    {
        if($note !== null)
            $this->debug[$note] = $params;

        if($this->debug)
            return $this->debug;
    }
    public function isError($error = null) 
    {
        if($error !== null)
        {
            $this->error = $error;
            $this->debug["error"][] = $error;
        }
        if($this->error)
            return $this->error;
    }
    public function setParams($params) 
    {
        if(is_array($params) && count($params))
        {
            foreach($params AS $name => $value)
            {
                $this->setParam($name, $value);
            }
        }
    }
    public function setParam($name, $value) 
    {
        $this->$name = $value;
    }
    public function getParam($name)
    {
        return $this->$name;
    }

    public function loadControllers($script_path) 
    {
        if(is_dir($script_path . "/services"))
        {
            $services = glob($script_path . "/services/*");
            if(is_array($services) && count($services)) {
                foreach($services AS $service) {
                    $arrService = explode("_", basename($service, "." . FF_PHP_EXT), 2);
                    if(isset($this->controllers[$arrService[0]]) && $this->controllers[$arrService[0]]["services"] !== false)
                    {
                        if(!is_array($this->controllers[$arrService[0]]["services"]))
                            $this->controllers[$arrService[0]]["default"] = $arrService[1];

                        $this->controllers[$arrService[0]]["services"][] = $arrService[1];
                        
                        $this->controllers_rev[$arrService[1]] = $arrService[0];
                    }
                }
            }            
        }
    } 
}