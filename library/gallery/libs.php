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
	return array(
			"ff" => array(
				"all" => array(
					"css_defs" => array(
						"cms" => array(
							"empty" => true
							, "css_defs" => array(
								"font-icon" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
									, "file" => "font-icon.css"
								)
								, "reset" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
									, "file" => "main.css"
									, "priority" => cm::LAYOUT_PRIORITY_TOPLEVEL
								)
							)
						)
					),
					"js_defs" => array(
						"cms" => array(
							"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript"
							, "file" => "main.js"
							/*, "css_deps" => array(
								"ff.cms.reset" => null
							)*/
							, "js_defs" => array(
								"admin" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.admin.js"
									, "css_deps" => array(
										"ff.cms.font-icon" => null
									)
									, "js_defs" => array(
										"process" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
											, "file" => "ff.cms.admin.process.js"
										)
										, "block" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "block.js"
											, "css_deps" => array(
												".style" => array(
													"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
													, "file" => "cms-block.css"
												)
											)
										)
										, "display-field" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "display-field.js"
										)
										, "field-gridsystem" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "field-gridsystem.js"
										)
										, "image-modify" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "image-modify.js"
										)
										, "mc-modify" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "mc-modify.js"
										)
										, "static-modify" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "static-modify.js"
										)
										, "sitemap-modify" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "sitemap-modify.js"
											, "js_deps" => array(
												"ff.cms.seo" => null
											)
										)
										, "vgallery-modify" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "vgallery-modify.js"
										)
										, "vgallery-type-extra" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "vgallery-type-extra.js"
										)
										, "vgallery-type-extra-modify" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/admin"
											, "file" => "vgallery-type-extra-modify.js"
										)
									
									)
								)
								, "bar" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
									, "file" => "admin.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
											, "file" => "admin.css"
										)
									)
									, "js_deps" => array(
										"jquery-ui" => null
										, "jquery.plugins.cookie" => null
										, "jquery.plugins.helperborder" => null
										, "jquery.plugins.hoverintent" => null

									)
									, "js_defs" => array(
									)
									, "js_loads"  => array(
										"ff.cms.block" => null
									)
								)
								, "block" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
									, "file" => "toolbar.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
										, "file" => "toolbar.css"
										)
									)
									, "js_deps" => array(
										"jquery.plugins.helperborder" => null
									)
								)
								, "editor" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
									, "file" => "editor.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
											, "file" => "editor.css"
										)
										, "ff.cms.font-icon" => null
									)
									, "js_deps" => array(
											"jquery-ui" => null
											, "jquery.plugins.nicescroll" => null
											, "jquery.plugins.colorpicker" => null
										)
									)
								, "seo" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.seo"
									, "file" => "seo.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.seo"
											, "file" => "seo.css"
										)
										, "ff.cms.font-icon" => null
									)
									, "js_loads" => array(
										".stopwords" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.seo/stopwords"
										, "file" => strtolower(LANGUAGE_INSET) . ".js"

										)
									)
								)
								, "sitemap" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
									, "file" => "sitemap.js"
									, "css_deps" => array(
										"ff.cms.font-icon" => null
									)
								)
								, "layout" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
									, "file" => "layout.js"
									, "css_deps" => array(
											".style" => array(
												"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/ff.cms.bar"
												, "file" => "layout.css"
											)
										, "ff.cms.font-icon" => null
									)
								)
								, "above-the-fold" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.above-the-fold.js" //forse da aggiungere sempre in admin?
								)
								, "doc" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.doc.js" //da aggingere le dipendenze dei plugin
								)
								, "landingpage" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.landingpage.js"
								)
								//parte dei moduli da tirare fuori
								, "form" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.form.js"
								)
								, "register" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.register.js"
								)
								, "search" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.search.js"
								)
							)
						)				
					)
				)
			)
	
	);
