<?php
return array(
	"gallery" => array(
		"all" => array(
			"js_defs" => array(
				"jquery" => array(
					"js_defs" => array(
						"plugins" => array(
							"js_defs" => array(
								"fancybox" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/plugins/jquery.fancybox",
									"file" => "jquery.fancybox.observe.js"
									, "type" => "viewer"
									, "js_deps" => array(
										"jquery.plugins.fancybox" => null
									)
								)
							)
						)
					)
				)
			)
		)
	)
);
