<?php
return array(
	"jquery" => array(
		"all" => array(
			"js_defs" => array(
				"plugins" => array(
					"empty" => true,
					"js_defs" => array(
						"sliderrevolution" => array(
							"js_defs" => array(
								"observe" => array(
									"path" => FF_THEME_DIR . "/" . THEME_INSET . "/javascript/plugins/jquery.sliderrevolution"
									, "file" => "jquery.sliderrevolution.observe.js"
									, "type" => "slider"
									, "limit" => array("image", "nolink") //image content nolink
									, "tpl" => "ulli" //divimg ulli listdiv thumb
									, 'js_deps' => 
									array(
										"jquery.plugins.sliderrevolution" => null
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
