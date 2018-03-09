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
 * @subpackage connector
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/gpl-3.0.html
 * @link https://github.com/wolfgan43/vgallery
 */ 

	// $globals : globals settings
    // $actual_srv = params defined by system
    if($actual_srv["enable"] && strlen($actual_srv["c1"]) && strlen($actual_srv["c2"]) && strlen($actual_srv["cv"]) && strlen($actual_srv["cj"])) 
    {
        $globals->fixed_post["body"][] = 
            '<!-- Begin comScore Tag -->
                <script defer="defer">
                    var _comscore = _comscore || [];
                    _comscore.push({ c1: "' . $actual_srv["c1"] . '", c2: "' . $actual_srv["c2"] . '" });
                    (function() {
                      var s = document.createElement("script"), el = document.getElementsByTagName("script")[0]; s.async = true;
                      s.src = (document.location.protocol == "https:" ? "https://sb" : "http://b") + ".scorecardresearch.com/beacon.js";
                      el.parentNode.insertBefore(s, el);
                    })();
                </script>
                <noscript>
                    <img src="http://b.scorecardresearch.com/p?c1=' . $actual_srv["c1"] . '&c2=' . $actual_srv["c2"] . '&cv=' . $actual_srv["cv"] . '&cj=' . $actual_srv["cj"] . '" />
                </noscript>
            <!-- End comScore Tag -->';
    }