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


class anagraphLegal
{
	const TYPE                                              = "sql";

	private $device                                         = null;
	private $config                                         = null;
	private $storage                                        = null;

	public function __construct($storage)
	{
		$this->storage = $storage;
		$this->setConfig();

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
	private function setConfig()
	{
		$this->config = $this->storage->getConfig($this::TYPE);
		if(!$this->config["name"]) {
			$prefix = ($this->config["prefix"] && defined($this->config["prefix"] . "NAME") && constant($this->config["prefix"] . "NAME")
				? $this->config["prefix"]
				: vgCommon::getPrefix($this::TYPE)
			);

			if (is_file($this->stats->getAbsPathPHP("/config")))
			{
				require_once($this->stats->getAbsPathPHP("/config"));
				$this->config["prefix"] = $prefix;
				$this->config["host"] = (defined($prefix . "HOST")
					? constant($prefix . "HOST")
					: "localhost"
				);
				$this->config["name"] = (defined($prefix . "NAME")
					? constant($prefix . "NAME")
					:  ""
				);
				$this->config["username"] = (defined($prefix . "USER")
					? constant($prefix . "USER")
					: ""
				);
				$this->config["password"] = (defined($prefix . "PASSWORD")
					? constant($prefix . "PASSWORD")
					: ""
				);
			}
		}
	}
}