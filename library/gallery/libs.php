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
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.bar.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
											, "file" => "cms-bar.css"
										)
									)
									, "js_deps" => array(
										"jquery.plugins.cookie" => null
										, "jquery.plugins.helperborder" => null
										, "jquery.plugins.hoverintent" => null
									)
									, "js_defs" => array(
										"block" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
											, "file" => "ff.cms.bar.block.js"
											, "css_deps" => array(
												".style" => array(
													"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
													, "file" => "cms-bar-block.css"
												)
											)
											, "js_deps" => array(
												"jquery.plugins.helperborder" => null
											)
										)
									)
									, "js_loads"  => array(
										"ff.cms.editor" => null
										, "ff.cms.seo" => null
										, "ff.cms.bar.block" => null
										, "ff.cms.bar.item" => null
									)
								)
								, "editor" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.editor.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
											, "file" => "cms-editor.css"
										)
										, "ff.cms.font-icon" => null
									)
									, "js_deps" => array(
										"jquery-ui" => null
										, "jquery.plugins.nicescroll" => null
									)
								)
								, "seo" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.seo.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
											, "file" => "cms-seo.css"
										)
										, "ff.cms.font-icon" => null
									)
									, "js_loads" => array(
										".stopwords" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools/stopwords"
											, "file" => "ff.cms.seo.stopwords." . strtolower(LANGUAGE_INSET) . ".js"
										
										)
									)
								)
								, "sitemap" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.sitemap.js"
									, "css_deps" => array(
										"ff.cms.font-icon" => null
									)
								)
								, "layout" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/tools"
									, "file" => "ff.cms.layout.js"
									, "css_deps" => array(
										".style" => array(
											"path" => FF_THEME_DIR . "/" . THEME_INSET . "/css"
											, "file" => "cms-layout.css"
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
