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

class filemanagerPhp
{
    const EXT                                                   = "php";

    private $device                                             = null;
    private $config                                             = null;
    private $filemanager                                        = null;

    public function __construct($filemanager, $data = null, $config = null)
    {
        $path = $filemanager->getParam("path");
        $filemanager->setParam("path", dirname($path) . "/" . basename($path, "." . $this::EXT) . "." . $this::EXT);

        $this->filemanager                                      = $filemanager;
        $this->setConfig($config);
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
        $this->config                                           = $config;
    }

    public function read($keys = null, $flags = null)
    {
        $res                                                    = array();
        $path                                                   = $this->filemanager->getParam("path");
        $var                                                    = $this->filemanager->getParam("var");
        //$keys                                                   = $this->filemanager->getParam("keys");
        if(!$flags)
            $flags                                              = Filemanager::SEARCH_DEFAULT;

        $output                                                 = exec("php -l " . addslashes($path));

        if(is_file($path)) {
            if(strpos($output, "No syntax errors") === 0) {
                $res = include($path);
				if(!$res) {
					if(!$var) {
						$arrDefVars = get_defined_vars();
						end($arrDefVars);
						$var                                            = key($arrDefVars);
						if($var == "output")
							$var                                        = null;
						else
							$this->filemanager->setParam("var", $var);
					}

					if($var) {
						if($keys)
						{
							if(!is_array($keys))
								$keys = array($keys);

							foreach($keys AS $key)
							{
								if($flags == Filemanager::SEARCH_IN_KEY || $flags == Filemanager::SEARCH_IN_BOTH && isset(${$var}[$key]))
									$res[$key]                      = ${$var}[$key];

								if($flags == Filemanager::SEARCH_IN_VALUE || $flags == Filemanager::SEARCH_IN_BOTH) {
									$arrToAdd                       = array_flip(array_keys(${$var}, $key));
									$res                            = array_replace($res, array_intersect_key(${$var}, $arrToAdd));
								}
							}
						} else {
							$res                                        = ${$var};
						}
					} else {
						$this->filemanager->isError("variable name needed");
					}
				}
            } else {
                @unlink($this->filemanager->getParam("path"));
                $this->filemanager->isError("syntax errors into file");
            }
        }

        return $res;
    }
    public function write($data, $var = null)
    {
        if(!$var)
            $var                                                = $this->filemanager->getParam("var");
        //$data                                                   = $this->filemanager->getParam("data");
        $expires                                                = $this->filemanager->getParam("expires");
        if($var)
            $return = '$'. $var;
        else
			$return = 'return';

        $this->filemanager->save("<?php\n" . ' '. $return . ' = ' . var_export($data, true) . ";", $expires);
    }

    public function update($data, $var = null)
    {
        //$data                                                   = $this->filemanager->getParam("data");
        //$expires                                                = $this->filemanager->getParam("expires");

        if(is_array($data))
        {
            $res                                                = array_replace($this->read(), $data);
        } else
            $res                                                = $data;

        $this->write($res, $var);
    }

    public function delete($keys, $flags = null)
    {
        if(!$flags)
            $flags                                              = Filemanager::SEARCH_DEFAULT;

        //$var                                                    = $this->filemanager->getParam("var");
        //$keys                                                   = $this->filemanager->getParam("keys");

        $res                                                    = $this->read();
        if($keys)
        {
            if(!is_array($keys))
                $keys = array($keys);

            foreach($keys AS $key)
            {
                if($flags == Filemanager::SEARCH_IN_KEY || $flags == Filemanager::SEARCH_IN_BOTH)
                    unset($res[$key]);

                if($flags == Filemanager::SEARCH_IN_VALUE || $flags == Filemanager::SEARCH_IN_BOTH) {
                    $arrToDel                                   = array_flip(array_keys($res, $key));
                    $res                                        = array_diff_key($res, $arrToDel);
                }
            }
        }

        $this->write($res);
    }
}