<?php
return array(
	"jquery" => array(
		"all" => array(
			"js_defs" => array(
				"plugins" => array(
					"empty" => true,
					"js_defs" => array(
						"nivoslider" => array(
							"js_defs" => array(
								"observe" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/plugins/jquery.nivoslider"
									, "file" => "jquery.nivoslider.observe.js"
									, 'js_deps' => 
									array(
										"jquery.plugins.nivoslider" => null
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
