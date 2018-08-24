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

    /**
     * filemanagerPhp constructor.
     * @param $filemanager
     * @param null $data
     * @param null $config
     */
    public function __construct($filemanager, $data = null, $config = null)
    {
        $this->filemanager                                      = $filemanager;
        $this->setConfig($config);
    }

    /**
     * @return null
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @return null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param null $config
     */
    private function setConfig($config = null)
    {
        $this->config                                           = $config;
    }

    /**
     * @param null $keys
     * @param null $flags
     * @return array|mixed
     */
    public function read($keys = null, $flags = null)
    {
        $res                                                    = array();
        $path                                                   = $this->filemanager->getPath($this::EXT);
        $var                                                    = $this->filemanager->getParam("var");
        //$keys                                                   = $this->filemanager->getParam("keys");
        if(!$flags)
            $flags                                              = Filemanager::SEARCH_DEFAULT;

        $output                                                 = exec("php -l " . addslashes($path));

        if(is_file($path)) {
            if(strpos($output, "No syntax errors") === 0) {
                $include = include($path);

                if($include === 1) {
					if(!$var) {
						$arrDefVars = get_defined_vars();
						end($arrDefVars);
						$var                                            = key($arrDefVars);
						if($var == "output")
							$var                                        = null;
						else
							$this->filemanager->setParam("var", $var);
					}
					$return = ${$var};
				} else {
                	$return = $include;
                	if($var)
						$return = $return[$var];
				}

				if($return) {
                    if($keys)
					{
						if(!is_array($keys))
							$keys = array($keys);

						foreach($keys AS $key)
						{
							if($flags == Filemanager::SEARCH_IN_KEY || $flags == Filemanager::SEARCH_IN_BOTH && isset($return[$key]))
								$res[$key]                      = $return[$key];

							if($flags == Filemanager::SEARCH_IN_VALUE || $flags == Filemanager::SEARCH_IN_BOTH) {
								$arrToAdd                       = array_flip(array_keys($return, $key));
								$res                            = array_replace($res, array_intersect_key($return, $arrToAdd));
							}
						}
					} else {
						$res                                    = $return;
					}
				} else {
                    $res                                        = null;
					//$this->filemanager->isError("Return Empty");
				}
            } else {
                @unlink($this->filemanager->getParam("path"));
                $this->filemanager->isError("syntax errors into file" . (Filemanager::DEBUG ? ": " . $path : ""));
            }
        }

        return $res;
    }

    /**
     * @param $data
     * @param null $var
     * @return mixed
     */
    public function write($data, $var = null)
    {
		$path 													= $this->filemanager->getPath($this::EXT);
        if(!$var)
            $var                                                = $this->filemanager->getParam("var");
        //$data                                                   = $this->filemanager->getParam("data");
        $expires                                                = $this->filemanager->getParam("expires");
        if($var)
            $return = '$'. $var . ' = ';
        else
			$return = 'return ';

        return $this->filemanager->save("<?php\n" . ' '. $return . var_export($data, true) . ";", $expires, $path);
    }

    /**
     * @param $data
     * @param null $var
     * @return mixed
     */
    public function update($data, $var = null)
    {
        //$data                                                   = $this->filemanager->getParam("data");
        //$expires                                                = $this->filemanager->getParam("expires");

        if(is_array($data))
        {
            $res                                                = array_replace($this->read(), $data);
        } else
            $res                                                = $data;

        return $this->write($res, $var);
    }

    /**
     * @param $keys
     * @param null $flags
     * @return mixed
     */
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

		return $this->write($res);
    }
}