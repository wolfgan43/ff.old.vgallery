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
if (!ff.cms) ff.cms = {};

ff.cms.doc = (function () {

	var that = { /* publics*/
		__init : false
		, "init": function() {
			
		
		}
		, "api": function() {
			$(function () {
				ff.injectCSS("hightlight.default", "/themes/library/plugins/swagger/css/hightlight.default.css", function() {
					ff.injectCSS("screen", "/themes/library/plugins/swagger/css/screen.css", function() {
						ff.pluginLoad("require", "/themes/library/plugins/swagger/lib/shred.bundle.js", function() {
							ff.pluginLoad("jquery.fn.slideto", "/themes/library/plugins/jquery.slideto/jquery.slideto.js", function() {
								ff.pluginLoad("jquery.fn.wiggle", "/themes/library/plugins/swagger/lib/jquery.wiggle.min.js", function() {
									ff.pluginLoad("jquery.fn.ba-bbq", "/themes/library/plugins/swagger/lib/jquery.ba-bbq.min.js", function() {
										ff.pluginLoad("Handlebars", "/themes/library/plugins/swagger/lib/handlebars-1.0.rc.1.js", function() {
											ff.pluginLoad("Underscore", "/themes/library/plugins/swagger/lib/underscore-min.js", function() {
												ff.pluginLoad("Backbone", "/themes/library/plugins/swagger/lib/backbone-min.js", function() {
													ff.pluginLoad("SwaggerApi", "/themes/library/plugins/swagger/lib/swagger.js", function() {
														ff.pluginLoad("SwaggerUi", "/themes/library/plugins/swagger/swagger-ui.js", function() {
															ff.pluginLoad("hljs", "/themes/library/plugins/swagger/lib/highlight.7.3.pack.js", function() {
																window.swaggerUi = new SwaggerUi({
																	url: "/srv/api-docs",
																	dom_id: "swagger-ui-container",
																	supportedSubmitMethods: ['get', 'post', 'put', 'delete'],
																	onComplete: function(swaggerApi, swaggerUi){
																		if(console) {
																		console.log("Loaded SwaggerUI")
																		}
																		$('pre code').each(function(i, e) {hljs.highlightBlock(e)});
																	},
																	onFailure: function(data) {
																		if(console) {
																		console.log("Unable to Load SwaggerUI");
																		console.log(data);
																		}
																	},
																	docExpansion: "none"
																});

																$('#input_apiKey').change(function() {
																	var key = $('#input_apiKey')[0].value;
																	console.log("key: " + key);
																	if(key && key.trim() != "") {
																		console.log("added key " + key);
																		window.authorizations.add("key", new ApiKeyAuthorization("api_key", key, "query"));
																	}
																});
																window.swaggerUi.load();
															});
														});
													});
												});
											});
										});
									});
								});
							});
						});
					});
				});
			});
		}
	};

	return that;
})();