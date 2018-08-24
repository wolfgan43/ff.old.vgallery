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

class cmsRouter
{
	const TYPE                                              = "router";

    const PRIORITY_TOP 			                            = 0;
    const PRIORITY_VERY_HIGH	                            = 1;
    const PRIORITY_HIGH			                            = 2;
    const PRIORITY_NORMAL 		                            = 3;
    const PRIORITY_LOW			                            = 4;
    const PRIORITY_VERY_LOW		                            = 5;
    const PRIORITY_BOTTOM 		                            = 6;
    const PRIORITY_DEFAULT 		                            = cmsRouter::PRIORITY_NORMAL;

	private $rules                                          = array();
	private $cms                                        	= null;
    protected $controllers                                  = array(
    );
    protected $controllers_rev                              = null;

    /**
     * cmsSchemaorg constructor.
     * @param $cms
     */
    public function __construct($cms, $params = null)
	{
		$this->cms = $cms;

        //$this->stats->setConfig($this->connectors, $this->services);
    }

    /**
     * @return null
     */
    public function getRouter()
	{
		return $this->router;
	}

	public function check($path, $source = null) {
        if($source) {
            $res = preg_match($this->regexp($source), $path);
        } else {
            $res = $this->find($path);
        }

        return $res;
    }
    public function run($path = null) {
        $rule                                               = $this->check($path);

        $destination                                        = $rule["destination"];
        if($destination) {
            if(is_array($rule["matches"])) {
                foreach($rule["matches"] AS $key => $match) {
                    $destination                            = str_replace('$' . $key, $match, $destination);
                }
            }
            if($rule["redirect"]) {
                Cms::redirect($destination, $rule["redirect"]);
            } else {
                require($this->cms->getDiskPath($destination));
                exit;
            }

        }
    }
	public function addRules($rules) {
        if(is_array($rules) && count($rules)) {
            foreach($rules AS $rule => $params) {
                if(is_array($params)) {
                    $this->addRule(array("source" => $rule) + $params);
                } else {
                    $this->addRule($rule, $params);
                }
            }
        }

    }
    public function addRule($source, $destination = null, $priority = cmsRouter::PRIORITY_DEFAULT, $redirect = false) {
        if(is_array($source) && !$destination) {
            $destination                = $source["destination"];
            $priority                   = $source["priority"];
            $redirect                   = $source["redirect"];

            $source                     = $source["source"];
        }

        if($source && $destination) {
            $this->rules[$priority . "-" . $source] = array(
                "source"                => $source
                , "destination"         => $destination
                , "redirect"            => $redirect
            );
        }
    }
    private function find($path) {
        $matches = array();
        ksort($this->rules);

        foreach ($this->rules as $source => $rule) {
            if(preg_match($this->regexp($rule["source"]), $path, $matches)) {
                $res = $rule;
                $res["matches"] = $matches;
                break;
            }
        }
        return $res;
    }
    private function regexp($rule) {
        return "#" . (strpos($rule, "[") === false
                ? str_replace("\*", "(.*)", preg_quote($rule, "#"))
                : $rule
            ) . "#i";
    }

}