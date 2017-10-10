<?php
	require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);
	
	if(check_function("set_field_gmap")) { 
		$gmap_params = set_field_gmap();
		if($gmap_params)
		{
			$db_gallery->query("SELECT module_maps.*
									FROM 
										module_maps
									WHERE 
										module_maps.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info))));
			if($db_gallery->nextRecord()) {
				$map_real_name = preg_replace('/[^a-zA-Z0-9]/', '', $db_gallery->getField("name")->getValue());  
				$map_name = $db_gallery->getField("name")->getValue();
				$contest = $db_gallery->getField("contest")->getValue();
				$relative_path = $db_gallery->getField("relative_path")->getValue();
				$enable_grid = $db_gallery->getField("enable_grid", "Number", true);
				$enable_grid_search = $db_gallery->getField("enable_grid_search", "Number", true);

				$coords_lat = $db_gallery->getField("coords_lat")->getValue();
				$coords_lng = $db_gallery->getField("coords_lng")->getValue();
				$coords_zoom = $db_gallery->getField("coords_zoom")->getValue();
				$coords_title = $db_gallery->getField("coords_title")->getValue();
				
				$layers = explode(",", $db_gallery->getField("layers")->getValue()); 
				$icon = $db_gallery->getField("icon")->getValue();
				$icon_width = $db_gallery->getField("icon_width")->getValue();
				$icon_height = $db_gallery->getField("icon_height")->getValue();
				
				if(is_array($layers) && count($layers)) {
					foreach($layers AS $layers_value) {
						if(strlen($layers_value)) {
							$tpl->set_var("layer", $layers_value);
							$tpl->parse("SezLayer", true);
						}
					}
				} else {
					$tpl->set_var("SezLayer", "");
				}
				
				if($gmap_params["is_gmap3"])
				{
					$enableMarkerCluster = $db_gallery->getField("enableMarkerCluster")->getValue();
					
					$enableZoomControl = $db_gallery->getField("enableZoomControl")->getValue();
					$ZoomControlStyle = $db_gallery->getField("ZoomControlStyle")->getValue();
					$ZoomControlPosition = $db_gallery->getField("ZoomControlPosition")->getValue();

					$enableMapTypeControl = $db_gallery->getField("enableMapTypeControl")->getValue();
					$MapTypeControlStyle = $db_gallery->getField("MapTypeControlStyle")->getValue();

					$enablePanControl = $db_gallery->getField("enablePanControl")->getValue();
					$PanControlPosition = $db_gallery->getField("PanControlPosition")->getValue();

					$enableScaleControl = $db_gallery->getField("enableScaleControl")->getValue();
					$ScaleControlPosition = $db_gallery->getField("ScaleControlPosition")->getValue();

					$enableStreetViewControl = $db_gallery->getField("enableStreetViewControl")->getValue();
					$StreetViewControlPosition = $db_gallery->getField("StreetViewControlPosition")->getValue();
                                        
                                        $enablePersonalColor = $db_gallery->getField("enablePersonalColor")->getValue();
                                        $PersonalColor = $db_gallery->getField("PersonalColor")->getValue();

                                        $disable_scroll = $db_gallery->getField("disableScroll")->getValue();
                                        $disable_drag = $db_gallery->getField("disableDrag")->getValue(); 
                    
					$tpl = ffTemplate::factory(ffCommon_dirname(ffCommon_dirname(__FILE__)));
					$tpl->load_file("maps3.html", "main");

					$tpl->set_var("site_path", FF_SITE_PATH);
					$tpl->set_var("theme_inset", THEME_INSET);
					$tpl->set_var("frontend_theme", FRONTEND_THEME);
					$tpl->set_var("domain_inset", DOMAIN_INSET);
					$tpl->set_var("language_inset", LANGUAGE_INSET);

					$tpl->set_var("gmap_key", $gmap_params["key"]);
					$tpl->set_var("gmap_region", ($gmap_params["region"] ? "&region=" . $gmap_params["region"] : ""));
					$tpl->set_var("gmap_lang", ($gmap_params["lang"] ? "&language=" . $gmap_params["lang"] : ""));

					
					$tpl->set_var("real_name", $map_real_name);
					$tpl->set_var("map_name", $map_name);

					$tpl->set_var("latitude", $coords_lat);
					$tpl->set_var("longitude", $coords_lng);
					$tpl->set_var("zoom", $coords_zoom);
					
					$tpl->set_var("zoom_control", ($enableZoomControl ? "true" : "false"));
					if($enableZoomControl) {
						$tpl->set_var("ZoomControlStyle", $ZoomControlStyle); 
						$tpl->set_var("ZoomControlPosition", $ZoomControlPosition); 
						$tpl->parse("SezZoomControlOptions", false); 
					} else {
						$tpl->set_var("SezZoomControlOptions", ""); 
					}

					$tpl->set_var("map_type_control", ($enableMapTypeControl ? "true" : "false"));
					if($enableMapTypeControl) {
                                            if($enablePersonalColor) {
                                                 $tpl->parse("SezMapTypeControlOptionsColor", false); 
                                            }
                                            $tpl->set_var("MapTypeControlStyle", $MapTypeControlStyle); 
                                            $tpl->parse("SezMapTypeControlOptions", false); 
					} else {
						$tpl->set_var("SezMapTypeControlControlOptions", ""); 
					}

					$tpl->set_var("pan_control", ($enablePanControl ? "true" : "false"));
					if($enablePanControl) {
						$tpl->set_var("PanControlPosition", $PanControlPosition);
						$tpl->parse("SezPanControlOptions", false); 
					} else {
						$tpl->set_var("SezPanControlOptions", ""); 
					}

					$tpl->set_var("scale_control", ($enableScaleControl ? "true" : "false"));
					if($enableScaleControl) {
						$tpl->set_var("ScaleControlPosition", $ScaleControlPosition);
						$tpl->parse("SezScaleControlOptions", false); 
					} else {
						$tpl->set_var("SezScaleControlOptions", "");  
					}
					
					$tpl->set_var("street_view_control", ($enableStreetViewControl ? "true" : "false"));
					if($enableStreetViewControl) {
						$tpl->set_var("StreetViewControlPosition", $StreetViewControlPosition);
						$tpl->parse("SezStreetViewControlOptions", false); 
					} else {
						$tpl->set_var("SezStreetViewControlOptions", ""); 
					}
                                        
					if($enablePersonalColor) {
						$tpl->set_var("PersonalColor", $PersonalColor);
						$tpl->parse("SezPersonalColor", false); 
						$tpl->parse("SezPersonalColorInfo", false); 
						$tpl->parse("SezPersonalColorDef", false);
					} else {
							$tpl->set_var("SezPersonalColor", ""); 
							$tpl->set_var("SezPersonalColorInfo", ""); 
							$tpl->set_var("SezPersonalColorDef", ""); 
					}
                                        
                                        if($disable_drag) {
                                            $tpl->set_var("drag_decision", false);
                                        } else {
                                            $tpl->set_var("drag_decision", true);
                                        }

                                        if($disable_scroll) {
                                            $tpl->set_var("scroll_decision", false);
                                        } else {
                                            $tpl->set_var("scroll_decision", true);
                                        }

					if(strlen($icon)) {
							$tpl->set_var("icon", $icon);
					}
					
					if($enableMarkerCluster)
					{
						$tpl->set_var("MarkerClusterMaxZoom", $db_gallery->getField("markerClusterMaxZoom")->getValue() ? $db_gallery->getField("markerClusterMaxZoom")->getValue() : 15);
						$tpl->set_var("MarkerClusterDim", $db_gallery->getField("markerClusterDim")->getValue() ? $db_gallery->getField("markerClusterDim")->getValue() : 50);
						$tpl->parse("SezMarkerCluster", false); 
					} else
					{
						$tpl->set_var("SezMarkerCluster", "");
					}
				} 
				else
				{
					$MapType = ($db_gallery->getField("MapType")->getValue() ? $db_gallery->getField("MapType")->getValue() : "G_NORMAL_MAP");


					$GLargeMapControl3D = $db_gallery->getField("GLargeMapControl3D")->getValue();
					$GMapTypeControl = $db_gallery->getField("GMapTypeControl")->getValue();
					$GScaleControl = $db_gallery->getField("GScaleControl")->getValue();
					$GOverviewMapControl = $db_gallery->getField("GOverviewMapControl")->getValue();

					$enableGooglePhysical = $db_gallery->getField("enableGooglePhysical")->getValue();
					$enableGoogleEarth = $db_gallery->getField("enableGoogleEarth")->getValue();
					$enableGoogleBar = $db_gallery->getField("enableGoogleBar")->getValue();
					$enableStreetView = $db_gallery->getField("enableStreetView")->getValue();

					$streetView_width = $db_gallery->getField("streetView_width")->getValue();
					$streetView_height = $db_gallery->getField("streetView_height")->getValue();
					$enableStreet_Overlay = $db_gallery->getField("enableStreetOverlay")->getValue();
					$enableStreet_Photo = $db_gallery->getField("enableStreetPhoto")->getValue();


					
					
					$shadow = $db_gallery->getField("shadow")->getValue();
					$shadow_width = $db_gallery->getField("shadow_width")->getValue();
					$shadow_height = $db_gallery->getField("shadow_height")->getValue();



					$tpl = ffTemplate::factory(ffCommon_dirname(ffCommon_dirname(__FILE__)));
					$tpl->load_file("maps.html", "main");

					$tpl->set_var("site_path", FF_SITE_PATH);
					$tpl->set_var("theme_inset", THEME_INSET);
					$tpl->set_var("frontend_theme", FRONTEND_THEME);
					$tpl->set_var("domain_inset", DOMAIN_INSET);
					$tpl->set_var("language_inset", LANGUAGE_INSET);

					$tpl->set_var("gmap_key", $gmap_params["key"]);
					$tpl->set_var("gmap_sensor", ($gmap_params["sensor"] ? "true" : "false"));
					$tpl->set_var("gmap_region", ($gmap_params["region"] ? "&region=" . $gmap_params["region"] : ""));
					$tpl->set_var("gmap_lang", ($gmap_params["lang"] ? "&language=" . $gmap_params["lang"] : ""));					
					
					$tpl->set_var("real_name", $map_real_name);
					$tpl->set_var("map_name", $map_name);

					$tpl->set_var("street_width", $streetView_width);
					$tpl->set_var("street_height", $streetView_height);

					$tpl->set_var("MapType", $MapType);


					$tpl->set_var("latitude", $coords_lat);
					$tpl->set_var("longitude", $coords_lng);
					$tpl->set_var("zoom", $coords_zoom);

					if($GLargeMapControl3D) {
						$tpl->parse("SezGLargeMapControl3D", false); 
					} else {
						$tpl->set_var("SezGLargeMapControl3D", ""); 
					}

					if($GMapTypeControl) {
						$tpl->parse("SezGMapTypeControl", false); 
					} else {
						$tpl->set_var("SezGMapTypeControl", ""); 
					}

					if($GScaleControl) {
						$tpl->parse("SezGScaleControl", false); 
					} else {
						$tpl->set_var("SezGScaleControl", ""); 
					}

					if($GOverviewMapControl) {
						$tpl->parse("SezGOverviewMapControl", false); 
					} else {
						$tpl->set_var("SezGOverviewMapControl", ""); 
					}

					if($enableGooglePhysical) {
						$tpl->parse("SezEnableGooglePhysical", false); 
					} else {
						$tpl->set_var("SezEnableGooglePhysical", ""); 
					}

					if($enableGoogleEarth) {
						$tpl->parse("SezEnableGoogleEarth", false); 
					} else {
						$tpl->set_var("SezEnableGoogleEarth", ""); 
					}

					if($enableGoogleBar) {
						$tpl->parse("SezEnableGoogleBar", false); 
					} else {
						$tpl->set_var("SezEnableGoogleBar", ""); 
					}

					if($enableStreetView) {
						if($enableStreet_Overlay) {
							$tpl->parse("SezStreetOverlay", false); 
						} else {
							$tpl->set_var("SezStreetOverlay", ""); 
						}

						if($enableStreet_Photo) {
							$tpl->set_var("enable_street_photo", "true"); 
						} else {
							$tpl->set_var("enable_street_photo", "false"); 
						}

						$tpl->parse("SezEnableStreetView", false); 
						$tpl->parse("SezPanoHtml", false); 
					} else {
						$tpl->set_var("SezEnableStreetView", ""); 
						$tpl->set_var("SezPanoHtml", ""); 
					}

					if(strlen($icon) && $icon_width > 0 && $icon_height > 0) {
						$tpl->set_var("icon", $icon);
						$tpl->set_var("icon_width", $icon_width);
						$tpl->set_var("icon_height", $icon_height);

						if(strlen($shadow) && $shadow_width > 0 && $shadow_height > 0) {
							$tpl->set_var("shadow", $shadow);
							$tpl->set_var("shadow_width", $shadow_width);
							$tpl->set_var("shadow_height", $shadow_height);
							$tpl->parse("SezShadow", false);
						} else {
							$tpl->set_var("SezShadow", "");
						}

						$tpl->parse("SezIcon", false);
					} else {
						$tpl->set_var("SezIcon", "");
					}
				}

				if($contest != "nomarker") {
					check_function("get_user_data");

					$db_gallery->query("SELECT DISTINCT module_maps_marker.*
											, ( IF(module_maps_marker.ID_node > 0
													, IF(module_maps_marker.ID_lang > 0
														, ( SELECT CONCAT(layout_path.path, SUBSTRING((CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)), LENGTH(CONCAT('/', vgallery.name, IF(layout.params = '/', '', layout.params))) + 1))
															FROM vgallery_nodes 
																INNER JOIN vgallery ON vgallery.ID = vgallery_nodes.ID_vgallery
																INNER JOIN layout ON layout.value = vgallery.name AND (CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name)) LIKE CONCAT('/', vgallery.name, layout.params, '%')
																INNER JOIN layout_path ON layout.ID = layout_path.ID_layout
																INNER JOIN layout_type ON layout_type.ID = layout.ID_type
															WHERE vgallery_nodes.ID = module_maps_marker.ID_node
																AND layout_path.visible = 1
																AND layout_path.cascading = 1
																AND layout_type.name = 'VIRTUAL_GALLERY'
															ORDER BY layout.`order` DESC, layout_path.ID DESC
															LIMIT 1
														)
														, ( SELECT CONCAT(anagraph.ID
																	, '-'
																	, " . (check_function("get_user_data")
				                                                        ? get_user_data("reference", "anagraph", null, false)
				                                                        : "''"
				                                                    ) . "
																)  
															FROM anagraph
															WHERE anagraph.ID = module_maps_marker.ID_node
														)
													)
													, ''
												)
											) AS ajax_description
											, (    IF(module_maps_marker.ID_node > 0
													, IF(module_maps_marker.ID_lang > 0
														, ( SELECT GROUP_CONCAT(DISTINCT CONCAT(vgallery_fields.name, '##', vgallery_rel_nodes_fields.description)
																				ORDER BY vgallery_fields.`order_thumb` SEPARATOR '@@')
																FROM vgallery_rel_nodes_fields    
																	INNER JOIN vgallery_fields ON vgallery_fields.ID = vgallery_rel_nodes_fields.ID_fields
																WHERE vgallery_fields.enable_in_grid = '1' 
																	AND vgallery_rel_nodes_fields.ID_nodes = module_maps_marker.ID_node
																	AND vgallery_rel_nodes_fields.ID_lang = module_maps_marker.ID_lang
																LIMIT 1
														)
														, ( SELECT GROUP_CONCAT(DISTINCT CONCAT(anagraph_fields.name, '##', anagraph_rel_nodes_fields.description)
																				ORDER BY anagraph_fields.`order_thumb` SEPARATOR '@@')
																FROM anagraph_rel_nodes_fields    
																	INNER JOIN anagraph_fields ON anagraph_fields.ID = anagraph_rel_nodes_fields.ID_fields 
																WHERE NOT(anagraph_fields.hide > 0)
																	AND anagraph_rel_nodes_fields.ID_nodes = module_maps_marker.ID_node
																LIMIT 1
														)
													)
													, module_maps_marker.description
												)
											) AS detail
										FROM 
											module_maps 
											INNER JOIN module_maps_marker ON 
												IF(module_maps.contest = 'all'
													, module_maps_marker.ID_module_maps = module_maps.ID
														OR module_maps_marker.ID_node > 0
													, IF(module_maps.contest = 'custom' OR module_maps.contest = ''
														, module_maps_marker.ID_module_maps = module_maps.ID
														, IF(module_maps.contest = 'anagraph'
															, module_maps_marker.ID_node IN ( SELECT DISTINCT ID 
																								FROM anagraph 
																								WHERE IF(module_maps.relative_path = ''
																										, 1
																										,  FIND_IN_SET(module_maps.relative_path, anagraph.categories)
																									)
																					)
																AND module_maps_marker.ID_lang = 0
															, module_maps_marker.ID_node IN ( SELECT DISTINCT ID 
																						FROM vgallery_nodes 
																						WHERE vgallery_nodes.ID_vgallery = (SELECT ID FROM vgallery WHERE name = module_maps.contest)
																							AND CONCAT(IF(vgallery_nodes.parent = '/', '', vgallery_nodes.parent), '/', vgallery_nodes.name) LIKE CONCAT('/', module_maps.contest, module_maps.relative_path, '%')
																					) 
														)
													)
												)
										WHERE 
											module_maps.name = " . $db_gallery->toSql(new ffData(basename($cm->real_path_info))));

					if($db_gallery->nextRecord()) {
						do {
							$ID_marker = $db_gallery->getField("ID", "Number")->getValue();
							$ID_marker_node = $db_gallery->getField("ID_node", "Number")->getValue();
							$ID_lang = $db_gallery->getField("ID_lang", "Number")->getValue();
							$marker_latitude = $db_gallery->getField("coords_lat")->getValue();
							$marker_longitude = $db_gallery->getField("coords_lng")->getValue();
							$marker_zoom = $db_gallery->getField("coords_zoom")->getValue();

							$marker_title = $db_gallery->getField("coords_title")->getValue();
							$marker_description = $db_gallery->getField("detail")->getValue();
							$marker_ajax_description = $db_gallery->getField("ajax_description")->getValue();

							if(!$marker_latitude && !$marker_longitude)
								continue;

							$arrMarker[$ID_marker]["ID_lang"] = $ID_lang;
							$arrMarker[$ID_marker]["ID_node"] = $ID_marker_node;
							$arrMarker[$ID_marker]["latitude"] = $marker_latitude;
							$arrMarker[$ID_marker]["longitude"] = $marker_longitude;
							$arrMarker[$ID_marker]["zoom"] = $marker_zoom;
							$arrMarker[$ID_marker]["address"] = $marker_title; 
							$arrMarker[$ID_marker]["description"] = $marker_description;
							$arrMarker[$ID_marker]["marker_ajax_description"] = $marker_ajax_description;
						} while($db_gallery->nextRecord());
					} elseif(!$content) {
						$arrMarker[] = array(
							"ID_lang" => LANGUAGE_INSET_ID
							, "ID_node" => 0
							, "latitude" => $coords_lat
							, "longitude" => $coords_lng
							, "zoom" => $coords_zoom
							, "address" => $coords_title
							, "description" => $coords_title
							, "marker_ajax_description" => ""
						);
					}
				}
				

				if(is_array($arrMarker) && count($arrMarker)) {
					foreach($arrMarker AS $ID_marker => $marker) {
						$tpl->set_var("id_marker", $ID_marker);
						$tpl->set_var("marker_latitude", $marker["latitude"]);
						$tpl->set_var("marker_longitude", $marker["longitude"]);			

						if($gmap_params["is_gmap3"])
						{
							if(strlen($marker["description"])) {
                                $tpl->set_var("marker_description", preg_replace(array("/\r(\s*)/", "/\n(\s*)/"), "", nl2br(htmlspecialchars_decode(htmlentities($marker["description"], ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES))));
								$tpl->parse("SezMarkerDescription", false);
							} else {
								$tpl->set_var("SezMarkerDescription", "");
							}
							
						} else
						{
							if($marker["ID_marker_node"] > 0) {
								if(strlen($marker_ajax_description)) {
									if($marker["ID_lang"] > 0) {
										if(check_function("get_vgallery_information_by_lang")) {
											$arrMarker[$ID_marker]["name"] = get_vgallery_information_by_lang(null, $marker["ID_marker_node"], array("meta_title_alt", "meta_title"), "System");
										}
										$tpl->set_var("marker_title_link", preg_replace(array("/\r(\s*)/", "/\n(\s*)/"), "", nl2br(htmlspecialchars($arrMarker[$ID_marker]["name"], ENT_QUOTES))));
										if(check_function("normalize_url")) {
											$tpl->set_var("marker_detail_link", "http://" . DOMAIN_INSET . FF_SITE_PATH  .  normalize_url($marker_ajax_description, HIDE_EXT, true, LANGUAGE_INSET));
										}
									} else {
										$arrAnagraph = explode("-", $marker_ajax_description);
										$arrMarker[$ID_marker]["name"] = $arrAnagraph[1];

										$tpl->set_var("marker_title_link", preg_replace(array("/\r(\s*)/", "/\n(\s*)/"), "", nl2br(htmlspecialchars($arrMarker[$ID_marker]["name"], ENT_QUOTES))));
										$tpl->set_var("marker_detail_link", "http://" . DOMAIN_INSET . FF_SITE_PATH  . VG_SITE_SERVICES . "/manage/anagraph/preview?key=" . $arrAnagraph[0] . "&reference=0");
									}

									$tpl->parse("SezMarkerDescriptionAjax", false);
								} else {
									$tpl->set_var("SezMarkerDescriptionAjax", "");    
								}
								$tpl->set_var("SezMarkerDescription", "");
							} else {
								if(strlen($marker["description"])) {
									$arrMarker[$ID_marker]["name"] = "";

                                    $tpl->set_var("marker_description", preg_replace(array("/\r(\s*)/", "/\n(\s*)/"), "", nl2br(htmlspecialchars_decode(htmlentities($marker["description"], ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES))));
									$tpl->parse("SezMarkerDescription", false);
								} else {
									$tpl->set_var("SezMarkerDescription", "");
								}
								$tpl->set_var("SezMarkerDescriptionAjax", "");
							}
						}

						if($gmap_params["is_gmap3"]) {
							if(strlen($icon)) {
								$tpl->set_var("icon_width", $icon_width);
								$tpl->set_var("icon_height", $icon_height);

								$tpl->set_var("icon", FF_SITE_PATH . constant("CM_SHOWFILES") . "/" . FRONTEND_THEME . "/" . GALLERY_TPL_PATH . "/modules/maps/" . $map_name . "/" . $icon);
								$tpl->parse("SezMarkerIcon", true); 
							} else {
								$tpl->parse("SezMarker", true);
							}
						} else {
							$tpl->parse("SezMarker", true);
						}
					} 
				} else {
					$tpl->set_var("SezMarker", "");
				}

				if($enable_grid) {
					$sSQL_grid = "";
					if(is_array($arrMarker) && count($arrMarker)) {
						$jsGrid = "";
						foreach($arrMarker AS $arrMarker_key => $arrMarker_value) {
							$strDetail = "";
							$arrDescription = explode("@@", $arrMarker_value["description"]);
							if(is_array($arrDescription) && count($arrDescription)) {
								foreach($arrDescription AS $arrDescription_value) {
									$arrDetail = explode("##", $arrDescription_value);
									if(strlen($arrDetail[1])) {
										$strDetail .= '<div class="' . preg_replace('/[^a-zA-Z0-9]/', '', $arrDetail[0]) . '">' . "<label>" . $arrDetail[0] . "</label>" . $arrDetail[1] .  "</div>";
									}
								}	
							}


							if(strlen($sSQL_grid))
								$sSQL_grid .= " UNION ";

							$sSQL_grid .= "(SELECT 
										" . $db_gallery->toSql($arrMarker_key, "Number") . " AS ID
										, " . $db_gallery->toSql($arrMarker_value["name"]) . " AS name
										, " . $db_gallery->toSql($arrMarker_value["address"]) . " AS address
										, " . $db_gallery->toSql($strDetail) . " AS description
									)";

							$tpl->set_var("marker_id", $arrMarker_key); 
							$tpl->set_var("marker_lat", $arrMarker_value["latitude"]);
							$tpl->set_var("marker_lng", $arrMarker_value["longitude"]);
							$tpl->set_var("marker_zoom", $arrMarker_value["zoom"]);
							$tpl->parse("SezGridCoord", true);
						}
						$tpl->parse("SezGrid", false);
					}


					//$oGrid = ffGrid::factory($cm->oPage, null, null, array("name" => "ffGrid_div"));
					$oGrid = ffGrid::factory($cm->oPage);
					$oGrid->full_ajax = true;
					$oGrid->id = "map" . $map_real_name . "grid";
					$oGrid->title = ffTemplate::_get_word_by_code("map" . $map_real_name . "grid" . "_title");
					$oGrid->source_SQL = "SELECT tbl_src.* 
											FROM (
												$sSQL_grid
											) AS tbl_src
											[WHERE] 
											[HAVING]
											[ORDER]";

					$oGrid->order_default = "name";
					$oGrid->use_search = $enable_grid_search;
					$oGrid->bt_edit_url = "javascript:centermap" . $map_real_name . "('[ID_VALUE]')";
					//$oGrid->record_url = $cm->oPage->site_path . $cm->oPage->page_path . "/modify/[name_VALUE]";
					$oGrid->record_id = "MapGrid";
					$oGrid->resources[] = $oGrid->record_id;
					$oGrid->display_new = false;
					$oGrid->display_edit_bt = false;
					$oGrid->display_delete_bt = false;
					if(check_function("MD_maps_on_before_parse_row"))
						$oGrid->addEvent("on_before_parse_row", "MD_maps_on_before_parse_row");

					$tmp = ffButton::factory($cm->oPage);
					$tmp->id             = "searched";
					$tmp->label         = ffTemplate::_get_word_by_code("ffGrid_search");
					$tmp->aspect         = "button";
					$tmp->action_type     = "submit";
					$tmp->frmAction        = "search";
					if  (strlen($tmp->class)) $tmp->class .= " ";
					$tmp->class .= "noactivebuttons";
					$tmp->jsaction = " ff.pluginLoad('ff.ajax', '/themes/library/ff/ajax.js', function() { ff.ajax.doRequest({'component' : '" . $oGrid->id . "','section' : 'GridData', 'callback' : loadmarkers" . $map_real_name . "}); });";
					$tmp->aspect = "link";
                                        $oGrid->buttons_options["search"]["obj"] = $tmp;

					// Campi chiave
					$oField = ffField::factory($cm->oPage);
					$oField->id = "ID";
					$oField->base_type = "Number";
					$oGrid->addKeyField($oField);

					// Campi visualizzati
					$oField = ffField::factory($cm->oPage);
					$oField->id = "name";
					$oField->container_class = "name";
					$oField->label = ffTemplate::_get_word_by_code("map" . $map_real_name . "grid_name");
					$oGrid->addContent($oField);

					$oField = ffField::factory($cm->oPage);
					$oField->id = "address";
					$oField->container_class = "address";
					$oField->label = ffTemplate::_get_word_by_code("map" . $map_real_name . "grid_address");
					$oGrid->addContent($oField);

					$oField = ffField::factory($cm->oPage);
					$oField->id = "description";
					$oField->label = ffTemplate::_get_word_by_code("map" . $map_real_name . "grid_description");
					$oField->encode_entities = false;
					$oGrid->addContent($oField);				

					$cm->oPage->addContent($oGrid, null, "MapsGrid"); 

				}

				$cm->oPage->addContent($tpl->rpparse("main", false), null, "GoogleMaps");
			}
		} else {
			$cm->oPage->addContent(ffTemplate::_get_word_by_code("google_map_key_notfound"));
		}
		
	}
$oButton = ffButton::factory($cm->oPage);
$oButton->id = "back";
$oButton->action_type = "gotourl";
$oButton->url = urldecode($_REQUEST["ret_url"]);
$oButton->aspect = "link";
$oButton->label = ffTemplate::_get_word_by_code("back");
$oButton->parent_page = array(&$cm->oPage);

$cm->oPage->addContent($oButton);

?>
