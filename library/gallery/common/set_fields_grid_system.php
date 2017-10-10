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
	function set_fields_grid_system(&$component, $params = null, $framework_css = null) {
		$cm = cm::getInstance();
		 
		static $js;
		static $event_loaded = null;
		
		$enable_slider = is_subclass_of($component, "ffRecord_base");
		$group_class = ($params["group"] ? $params["group"] : null);

        if($framework_css === null) {
            $db = ffDB_Sql::factory();

            $sSQL = "SELECT cm_layout.* 
                    FROM cm_layout 
                    WHERE cm_layout.path = " . $db->toSql("/");
            $db->query($sSQL);
            if($db->nextRecord()) {
                $framework_css = cm_getFrameworkCss($db->getField("framework_css", "Text", true));
            }         
        }
                
	    $template_framework = $framework_css["name"];
		if($template_framework)
		{
			$wrap_columns = null;
			if($params === null || !isset($params["fluid"]) || $params["fluid"]) {
				$grid_columns = false;
				if($params === null || !isset($params["fluid"]) || !isset($params["fluid"]["choice"]) || $params["fluid"]["choice"]) {
		            if(!$params["fluid"]["full_row"]) {
		            	if($params["fluid"]["col"] === false) {
		            		$fluid_columns = array(6,6,6,6);
		            		if($params["class"] === false)
		            			$wrap_columns = array(6,6,6,6);
		            	} else {
				            if($template_framework == "bootstrap") {
				                $fluid_columns = array(4,4,4,4);
				                $grid_columns = array(2,2,2,2);     
							} elseif($template_framework == "foundation") {
								$fluid_columns = array(3,3,3);
				                $grid_columns = array(3,3,3);
							} else {
								$fluid_columns = null;
				                $grid_columns = null;
							}
						}
					}
					$oField = ffField::factory($cm->oPage);
					$oField->id = (isset($params["fluid"]["name"]) ? $params["fluid"]["name"] : "fluid");
					$oField->container_class = "fluid-def";
					$oField->label = (isset($params["fluid"]["label"]) ? $params["fluid"]["label"] : ffTemplate::_get_word_by_code("grid_modify_fluid"));
					$oField->base_type = "Number";
					$oField->extended_type = "Selection"; 
					//$oField->widget = "actex";
					//$oField->actex_update_from_db = true;
					if($params === null || !isset($params["fluid"]) || !isset($params["fluid"]["hide"]) || $params["fluid"]["hide"])
	                    $oField->multi_pairs[] = array(new ffData("-3", "Number"), new ffData(!isset($params["fluid"]["hide"]) || is_bool($params["fluid"]["hide"]) ? ffTemplate::_get_word_by_code("grid_hide") : $params["fluid"]["hide"]));
					$oField->multi_pairs[] = array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("grid_skip_all")));  
					if($params === null || !isset($params["fluid"]) || !isset($params["fluid"]["col"]) || $params["fluid"]["col"]) {
					    $oField->multi_pairs[] = array(new ffData("0", "Number"), new ffData(ffTemplate::_get_word_by_code("grid_column") . ": DIV" . (cm_getClassByFrameworkCss(array(3,4,5,6), "col" . ($framework_css["is_fluid"] ? "-fluid" : ""), null, $framework_css) ? "." . str_replace(array(" ", "3", "4", "5", "6"), array(".", "X", "X", "X", "X"), cm_getClassByFrameworkCss(array(3,4,5,6), "col" . ($framework_css["is_fluid"] ? "-fluid" : ""), null, $framework_css)) : "") . ""));
						if($params === null || !isset($params["fluid"]) || !isset($params["fluid"]["skip-prepost"]) || $params["fluid"]["skip-prepost"])    					
    						$oField->multi_pairs[] = array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("grid_column") . ": DIV" . (cm_getClassByFrameworkCss(array(3,4,5,6), "col" . ($framework_css["is_fluid"] ? "-fluid" : ""), null, $framework_css) ? "." . str_replace(array(" ", "3", "4", "5", "6"), array(".", "X", "X", "X", "X"), cm_getClassByFrameworkCss(array(3,4,5,6), "col" . ($framework_css["is_fluid"] ? "-fluid" : ""), array("skip-prepost" => true), $framework_css)) : "") . ""));
					}
					if($params === null || !isset($params["fluid"]) || !isset($params["fluid"]["row"]) || $params["fluid"]["row"]) {
    					$oField->multi_pairs[] = array(new ffData("-1", "Number"), new ffData(ffTemplate::_get_word_by_code("grid_row") . ": DIV" . (cm_getClassByFrameworkCss("", "row", null, $framework_css) ? "." . cm_getClassByFrameworkCss("", "row", null, $framework_css) : "") . ""));
					    $oField->multi_pairs[] = array(new ffData("-2", "Number"), new ffData(ffTemplate::_get_word_by_code("grid_row") . ": DIV" . (cm_getClassByFrameworkCss("", "row-fluid", null, $framework_css) ? "." . cm_getClassByFrameworkCss("", "row-fluid", null, $framework_css) : "") . ""));
					}
					if($enable_slider && !$params["fluid"]["full_row"]) {
						$oField->setWidthComponent($fluid_columns);
					}
					if(isset($params["fluid"]["default_value"])) {
						$oField->default_value = $params["fluid"]["default_value"];
					} else {
						$oField->default_value = new ffData("0", "Number");
					}
					$oField->multi_select_one = false;
					$oField->properties["onchange"] = "javascript:ff.cms.admin.checkFieldTypeGridSystem(this);";
					$oField->setWidthLabel(3);
					$component->addContent($oField, $group_class);
				}		

				if($grid_columns === false) {
					if($template_framework == "bootstrap") {
		                $grid_columns = array(3,3,3,3);     
					} elseif($template_framework == "foundation") {
		                $grid_columns = array(4,4,4);
					} else {
		                $grid_columns = null;
					}			
				}
				
				if($params === null || !isset($params["fluid"]) || !isset($params["fluid"]["col"]) || $params["fluid"]["col"]) {
	                $prefix_grid = "grid";
	                if(isset($params["fluid"]["prefix"]))
	                    $prefix_grid = $params["fluid"]["prefix"];
					
                    if(!isset($params["fluid"]["choice"]) || $params["fluid"]["choice"]) {
                        $component->user_vars["one_field"][$prefix_grid]["fluid"] = $oField->id;
                    } 

					$default_value = null;				
	                $arrDefaultValue = null;
	                if($params["fluid"] && array_key_exists("one_field", $params["fluid"])) {
                		if($event_loaded === null)
                			$event_loaded = false;

                		if(is_array($params["fluid"]["one_field"]) || is_numeric($params["fluid"]["one_field"])) 
                    		$arrDefaultValue = $params["fluid"]["one_field"];
	                }

					if($arrDefaultValue === null && $default_value === null) {
						if(isset($params["fluid"]["col"]["default_value"]))
							$default_value = $params["fluid"]["col"]["default_value"];
						else
	                		$default_value = 12;
					}

					$oField = ffField::factory($cm->oPage);
					$oField->id = ($prefix_grid == "grid" ? "default_" : "") . $prefix_grid;
					$oField->container_class = "col-dep";
					$oField->label = ffTemplate::_get_word_by_code("grid_" . $template_framework . "_default_grid");
					$oField->base_type = "Number";
					$oField->min_val = "0";
					$oField->max_val = "12";
					$oField->step = "1";
	                if($params["fluid"] && array_key_exists("one_field", $params["fluid"])) {
	                    $oField->data_type = "";
	                    $oField->store_in_db = false;
	                    $component->user_vars["one_field"][$prefix_grid]["fields"][] = $oField->id;
	                }
					if($enable_slider) {
						$oField->widget = "slider";
						$oField->setWidthComponent($grid_columns);
					} else {
						$oField->fixed_post_content = "/12";
						$oField->framework_css["fixed_post_content"] = "6";						
					}

	                if(isset($arrDefaultValue[3])) {
	                	$default_value = $arrDefaultValue[3];
						$oField->user_vars["on_field"]["loaded"] = true;
					}
	                $oField->default_value = new ffData($default_value, "Number");
					$component->addContent($oField, $group_class);

					if($template_framework == "bootstrap" || $template_framework == "foundation") 
					{
						$oField = ffField::factory($cm->oPage);
						$oField->id = $prefix_grid . "_md";
						$oField->container_class = "col-dep";
						$oField->label = ffTemplate::_get_word_by_code("grid_" . $template_framework . "_grid_md");
						$oField->base_type = "Number";
						$oField->min_val = "0";
						$oField->max_val = "12";
						$oField->step = "1";
	                    if($params["fluid"] && array_key_exists("one_field", $params["fluid"])) {
	                        $oField->data_type = "";
	                        $oField->store_in_db = false;
	                        $component->user_vars["one_field"][$prefix_grid]["fields"][] = $oField->id;
	                    }
						if($enable_slider) {
							$oField->widget = "slider";
							$oField->setWidthComponent($grid_columns);
						} else {
							$oField->fixed_post_content = "/12";
							$oField->framework_css["fixed_post_content"] = "6";						
						}

		                if(isset($arrDefaultValue[2])) {
	                		$default_value = $arrDefaultValue[2];
							$oField->user_vars["on_field"]["loaded"] = true;
						}
	                    $oField->default_value = new ffData($default_value, "Number");
						$component->addContent($oField, $group_class);

						$oField = ffField::factory($cm->oPage);
						$oField->id = $prefix_grid . "_sm";
						$oField->container_class = "col-dep";
						$oField->label = ffTemplate::_get_word_by_code("grid_" . $template_framework . "_grid_sm");
						$oField->base_type = "Number";
						$oField->min_val = "0";
						$oField->max_val = "12";
						$oField->step = "1";
	                    if($params["fluid"] && array_key_exists("one_field", $params["fluid"])) {
	                        $oField->data_type = "";
	                        $oField->store_in_db = false;
	                        $component->user_vars["one_field"][$prefix_grid]["fields"][] = $oField->id;
	                    }
						if($enable_slider) {
							$oField->widget = "slider";
							$oField->setWidthComponent($grid_columns);
						} else {
							$oField->fixed_post_content = "/12";
							$oField->framework_css["fixed_post_content"] = "6";						
						}

						if(isset($arrDefaultValue[1])) {
	                		$default_value = $arrDefaultValue[1];
							$oField->user_vars["on_field"]["loaded"] = true;
						}	                    
	                    $oField->default_value = new ffData($default_value, "Number");
	                    $component->addContent($oField, $group_class);
						
						if($template_framework == "bootstrap")
						{
							$oField = ffField::factory($cm->oPage);
							$oField->id = $prefix_grid . "_xs";
							$oField->container_class = "col-dep";
							$oField->label = ffTemplate::_get_word_by_code("grid_" . $template_framework . "_grid_xs");
							$oField->base_type = "Number";
							$oField->min_val = "0";
							$oField->max_val = "12";
							$oField->step = "1";
	                        if($params["fluid"] && array_key_exists("one_field", $params["fluid"])) {
	                            $oField->data_type = "";
	                            $oField->store_in_db = false;
	                            $component->user_vars["one_field"][$prefix_grid]["fields"][] = $oField->id;
	                        }
							if($enable_slider) {
								$oField->widget = "slider";
								$oField->setWidthComponent($grid_columns);
							} else {
								$oField->fixed_post_content = "/12";
								$oField->framework_css["fixed_post_content"] = "6";						
							}

							if(isset($arrDefaultValue[0])) {
	                			$default_value = $arrDefaultValue[0];
								$oField->user_vars["on_field"]["loaded"] = true;
							}
	                        $oField->default_value = new ffData($default_value, "Number");
	                        $component->addContent($oField, $group_class);
						}
					}
				}
			}

			if($params === null || !isset($params["class"]) || $params["class"]) {
				$oField = ffField::factory($cm->oPage);
				$oField->id = $oField->id = (isset($params["class"]["name"]) ? $params["class"]["name"] : "class");
				$oField->container_class = "fluid-dep";
				$oField->label = ffTemplate::_get_word_by_code("grid_default_class");
                if($params === null || !isset($params["wrap"]) || ($params["wrap"] && !isset($params["wrap"]["multi"]))) {
                	$oField->setWidthComponent(9,9,12);
                } else {
                	$oField->setWidthLabel(2);
                }
				$component->addContent($oField, $group_class);      
			}			

			if($params === null || !isset($params["wrap"]) || $params["wrap"]) {
                if(is_array($params["wrap"]["multi"]) && count($params["wrap"]["multi"])) {
                	if(isset($params["wrap"]["one_field"])) {
                	 	$component->user_vars["one_field"][(isset($params["wrap"]["name"]) ? $params["wrap"]["name"] : "wrap")]["nochange"] = true;
						if($event_loaded === null)
                			$event_loaded = false;                	
                	
                		$default_value = is_array($params["wrap"]["one_field"]) ? $params["wrap"]["one_field"] : array();
					}
					$count_wrap = 0;
                    foreach($params["wrap"]["multi"] AS $wrap_key => $wrap_value) {
                        $oField = ffField::factory($cm->oPage);
                        $oField->id = $oField->id = (isset($params["wrap"]["name"]) ? $params["wrap"]["name"] : "wrap") . "_" . $wrap_key;
                        $oField->label = ffTemplate::_get_word_by_code("grid_modify_wrap_" . $wrap_key);
                        $oField->base_type = "Number";
                        $oField->extended_type = "Selection";
                        $oField->multi_pairs = $wrap_value["multi_pairs"];
						if(isset($params["wrap"]["one_field"])) {
		                    $oField->data_type = "";
		                    $oField->store_in_db = false;
		                    $component->user_vars["one_field"][(isset($params["wrap"]["name"]) ? $params["wrap"]["name"] : "wrap")]["fields"][] = $oField->id;
	                	}                        
                        if(isset($params["wrap"]["select_one_label"])) { 
                            $oField->multi_select_one_label = $params["wrap"]["select_one_label"];
                        } else {
                            $oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
                        }
                        if(isset($wrap_value["default_value"])) {
                        	$oField->default_value = $wrap_value["default_value"]; 
						} else {
                        	$oField->default_value = new ffData($default_value[$count_wrap], "Number");
						}

                        if(count($params["wrap"]["multi"]) > 2) {
                        	$oField->setWidthComponent(floor(12 / count($params["wrap"]["multi"])));
                        } else {
                        	$oField->setWidthLabel(5); 
                        	$oField->setWidthComponent(6);
                        }
                        $component->addContent($oField, $group_class);
                        
                        $count_wrap++;   
                    }
                } else {
				    $oField = ffField::factory($cm->oPage);
				    $oField->id = $oField->id = (isset($params["wrap"]["name"]) ? $params["wrap"]["name"] : "wrap");
				    $oField->label = ffTemplate::_get_word_by_code("grid_modify_wrap");
				    $oField->base_type = "Number";
				    $oField->extended_type = "Selection";
                    if(is_array($params["wrap"]["multi_pairs"])) {
                        $oField->multi_pairs = $params["wrap"]["multi_pairs"];
                    } else {
				        $oField->multi_pairs = array(	
						                            array(new ffData("-1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": " . ffTemplate::_get_word_by_code("grid_skip_all"))),
						                            array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (cm_getClassByFrameworkCss("", "wrap", null, $framework_css) ? "." . cm_getClassByFrameworkCss("", "wrap", null, $framework_css) : "") . "")),
						                            array(new ffData("2", "Number"), new ffData(ffTemplate::_get_word_by_code("yes") . ": DIV" . (cm_getClassByFrameworkCss("", "wrap-fluid", null, $framework_css) ? "." . cm_getClassByFrameworkCss("", "wrap-fluid", null, $framework_css) : "") . ""))
											    );
                    }
                    if(isset($params["wrap"]["select_one_label"])) { 
				        $oField->multi_select_one_label = $params["wrap"]["select_one_label"];
                    } else {
                        $oField->multi_select_one_label = ffTemplate::_get_word_by_code("no");
                    }
                    if($params["class"] !== false) {
                	    $oField->setWidthComponent(3,3,12);
                    } elseif(is_array($wrap_columns))
                    	$oField->setWidthComponent($wrap_columns);

				    $component->addContent($oField, $group_class);	
                }
			}
            
            if($params["extra"]) 
            {
				if($template_framework == "bootstrap") {
	                $extra_columns = array(3,3,3,3);     
				} elseif($template_framework == "foundation") {
	                $extra_columns = array(4,4,4);
				} else {
	                $extra_columns = null;
				}			

                $prefix_extra = "extra";
                if(isset($params["extra"]["prefix"]))
                    $prefix_extra = $params["extra"]["prefix"];
				
				$default_value = null;
	            $arrDefaultValue = null;
	            if(isset($params["extra"]["one_field"])) {
                	if($event_loaded === null)
                		$event_loaded = false;

                	if(is_array($params["extra"]["one_field"]) || is_numeric($params["extra"]["one_field"]))
                    	$arrDefaultValue = $params["extra"]["one_field"];
	            }

				if($arrDefaultValue === null && $default_value === null) {
					if(isset($params["extra"]["default_value"]))
						$default_value = $params["extra"]["default_value"];
					else
	                	$default_value = 12;
				}
				if(!$default_value && !$arrDefaultValue) {
					$extra_class = " hidden";
				}
                $oField = ffField::factory($cm->oPage);
                $oField->id = $prefix_extra . "_container_grid";
                $oField->container_class = "check-container-grid";
                $oField->label = ffTemplate::_get_word_by_code("extra_container_grid");
				$oField->data_type = "";
				$oField->store_in_db = false;
                $oField->base_type = "Number";
				$oField->extended_type = "Boolean";
				$oField->control_type = "checkbox";
				$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
				$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
				$oField->default_value = new ffData(!$extra_class, "Number");
				$oField->setWidthComponent(5);
				$oField->properties["onclick"] = "
					jQuery(this).closest('fieldset').find('.dep-container-grid').toggleClass('hidden');
					if(jQuery(this).is(':checked')) {
						jQuery(this).closest('fieldset').find('.dep-container-grid INPUT[type=text]').each(function() {
							if(jQuery(this).attr('data-val')) {
								jQuery(this).val(jQuery(this).attr('data-val'));
								jQuery(this).keyup();
							}
						});
					} else {
						jQuery(this).closest('fieldset').find('.dep-container-grid INPUT[type=text]').each(function() {
							jQuery(this).attr('data-val', jQuery(this).val());
							jQuery(this).val('0');
							jQuery(this).keyup();
						});
					}
				";
                $component->addContent($oField, $group_class); 
                
                $oField = ffField::factory($cm->oPage);
                $oField->id = $prefix_extra . "_location";
                $oField->label = ffTemplate::_get_word_by_code("extra_location");
                $oField->container_class = "dep-container-grid" . $extra_class;
                $oField->base_type = "Number";
				$oField->extended_type = "Selection";
				$oField->multi_pairs = array(array(new ffData("1", "Number"), new ffData(ffTemplate::_get_word_by_code("bottom/right"))));
				$oField->setWidthLabel(8);
				$oField->setWidthComponent(7);
                $oField->multi_select_one_label = ffTemplate::_get_word_by_code("top/left");
                $oField->default_value = new ffData($params["extra"]["default_location"], "Number");
                $component->addContent($oField, $group_class);            
                
                $oField = ffField::factory($cm->oPage);
                $oField->id = $prefix_extra . "_class_left";
                $oField->label = ffTemplate::_get_word_by_code("extra_class_left");
                $oField->container_class = "dep-container-grid" . $extra_class;
                $oField->setWidthComponent(6);
                $oField->default_value = new ffData($params["extra"]["class_left"]);
                $component->addContent($oField, $group_class); 

                $oField = ffField::factory($cm->oPage);
                $oField->id = $prefix_extra . "_class_right";
                $oField->label = ffTemplate::_get_word_by_code("extra_class_right");
                $oField->container_class = "dep-container-grid" . $extra_class;
				$oField->setWidthComponent(6);
                $oField->default_value = new ffData($params["extra"]["class_left"]);
                $component->addContent($oField, $group_class);           
                
                
                $oField = ffField::factory($cm->oPage);
                $oField->id = ($prefix_extra == "extra" ? "default_" : "") . $prefix_extra;                
                $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_default_grid");
                $oField->container_class = "dep-container-grid" . $extra_class;
                $oField->base_type = "Number";
                $oField->min_val = "0";
                $oField->max_val = "12";
                $oField->step = "1";
                if(isset($params["extra"]["one_field"])) {
                    $oField->data_type = "";
                    $oField->store_in_db = false;
                    $component->user_vars["one_field"][$prefix_extra]["fields"][] = $oField->id;
                }
                if($enable_slider) {
                    $oField->widget = "slider";
                    $oField->setWidthComponent($extra_columns);
                } else {
                    $oField->fixed_post_content = "/12";
                    $oField->framework_css["fixed_post_content"] = "6";                        
                }

				if(isset($arrDefaultValue[3])) {
	                $default_value = $arrDefaultValue[3];
					$oField->user_vars["on_field"]["loaded"] = true;
				}
                $oField->default_value = new ffData($default_value, "Number");
                $component->addContent($oField, $group_class);

                if($template_framework == "bootstrap" || $template_framework == "foundation") 
                {
                    $oField = ffField::factory($cm->oPage);
                    $oField->id = $prefix_extra . "_md";
                    $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_grid_md");
                    $oField->container_class = "dep-container-grid" . $extra_class;
                    $oField->base_type = "Number";
                    $oField->min_val = "0";
                    $oField->max_val = "12";
                    $oField->step = "1";
                    if(isset($params["extra"]["one_field"])) {
                        $oField->data_type = "";
                        $oField->store_in_db = false;
                        $component->user_vars["one_field"][$prefix_extra]["fields"][] = $oField->id;
                    }
                    if($enable_slider) {
                        $oField->widget = "slider";
                        $oField->setWidthComponent($extra_columns);
                    } else {
                        $oField->fixed_post_content = "/12";
                        $oField->framework_css["fixed_post_content"] = "6";                        
                    }

					if(isset($arrDefaultValue[2])) {
	                	$default_value = $arrDefaultValue[2];
						$oField->user_vars["on_field"]["loaded"] = true;
					}
                    $oField->default_value = new ffData($default_value, "Number");
                    $component->addContent($oField, $group_class);

                    $oField = ffField::factory($cm->oPage);
                    $oField->id = $prefix_extra . "_sm";
                    $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_grid_sm");
                    $oField->container_class = "dep-container-grid" . $extra_class;
                    $oField->base_type = "Number";
                    $oField->min_val = "0";
                    $oField->max_val = "12";
                    $oField->step = "1";
                    if(isset($params["extra"]["one_field"])) {
                        $oField->data_type = "";
                        $oField->store_in_db = false;
                        $component->user_vars["one_field"][$prefix_extra]["fields"][] = $oField->id;
                    }
                    if($enable_slider) {
                        $oField->widget = "slider";
                        $oField->setWidthComponent($extra_columns);
                    } else {
                        $oField->fixed_post_content = "/12";
                        $oField->framework_css["fixed_post_content"] = "6";                        
                    }

					if(isset($arrDefaultValue[1])) {
	                	$default_value = $arrDefaultValue[1];
						$oField->user_vars["on_field"]["loaded"] = true;
					}
                    $oField->default_value = new ffData($default_value, "Number");
                    $component->addContent($oField, $group_class);
                    
                    if($template_framework == "bootstrap")
                    {
                        $oField = ffField::factory($cm->oPage);
                        $oField->id = $prefix_extra . "_xs";
                        $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_grid_xs");
                        $oField->container_class = "dep-container-grid" . $extra_class;
                        $oField->base_type = "Number";
                        $oField->min_val = "0";
                        $oField->max_val = "12";
                        $oField->step = "1";
                        if(isset($params["extra"]["one_field"])) {
                            $oField->data_type = "";
                            $oField->store_in_db = false;
                            $component->user_vars["one_field"][$prefix_extra]["fields"][] = $oField->id;
                        }
                        if($enable_slider) {
                            $oField->widget = "slider";
                            $oField->setWidthComponent($extra_columns); 
                        } else {
                            $oField->fixed_post_content = "/12";
                            $oField->framework_css["fixed_post_content"] = "6";                        
                        }

						if(isset($arrDefaultValue[0])) {
	                		$default_value = $arrDefaultValue[0];
							$oField->user_vars["on_field"]["loaded"] = true;
						}
                        $oField->default_value = new ffData($default_value, "Number");
                        $component->addContent($oField, $group_class);
                    }
                }
            }
            
			if(isset($params["image"])) 
            {
				if($template_framework == "bootstrap") {
	                $image_columns = array(3,3,3,3);     
				} elseif($template_framework == "foundation") {
	                $image_columns = array(4,4,4);
				} else {
	                $image_columns = array(4);
				}			

                $prefix_image = "ID_image";
                if(isset($params["image"]["prefix"]))
                    $prefix_image = $params["image"]["prefix"];

				$default_value = null;
	            $arrDefaultValue = $params["image"]["default_value"];
	            if(!is_array($arrDefaultValue))
	            	$arrDefaultValue = array_fill(0, count($image_columns), $arrDefaultValue);

	            if(!$params["image"]["source_SQL"])
	            	$params["image"]["source_SQL"] = "SELECT ID
												, name 
											FROM " . CM_TABLE_PREFIX . "showfiles_modes 
											ORDER BY name";
	            if(!$params["image"]["dialog_url"])
	            	$params["image"]["dialog_url"] = $cm->oPage->site_path . VG_SITE_ADMINGALLERY . "/layout/extras/image/modify";
	            if(!$params["image"]["dialog_edit_params"])
	            	$params["image"]["dialog_edit_params"] =  array("keys[ID]" => null);
	            if(!$params["image"]["dialog_delete_url"])
	            	$params["image"]["dialog_delete_url"] = $oField->actex_dialog_url . "?frmAction=ExtrasImageModify_confirmdelete";
				if(!$params["image"]["resources"])
					$params["image"]["resources"] = "ExtrasImageModify";
						            	
                $oField = ffField::factory($cm->oPage);
                $oField->id = $prefix_image;                
                $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_default_image");
                $oField->base_type = "Number";
				$oField->widget = "actex";
			    $oField->actex_update_from_db 		= !$params["image"]["multi_pairs"];
			    $oField->actex_dialog_url 			= $params["image"]["dialog_url"];
			    $oField->actex_dialog_edit_params 	= $params["image"]["dialog_edit_params"];
			    $oField->actex_dialog_delete_url 	= $params["image"]["dialog_delete_url"];
			    $oField->resources[] 				= $params["image"]["resources"];
			    $oField->source_SQL 				= $params["image"]["source_SQL"];
			    $oField->multi_pairs 				= $params["image"]["multi_pairs"];
				$oField->actex_father 				= $params["image"]["father"];
				$oField->actex_child 				= $params["image"]["child"];
				$oField->actex_related_field 		= $params["image"]["related_field"];
			    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("original_size");
			    $oField->actex_hide_empty = "all";
				if(isset($arrDefaultValue[3])) {
	                $default_value = $arrDefaultValue[3];
				}
                $oField->default_value = new ffData($default_value, "Number");
                $oField->setWidthComponent($image_columns);
                $component->addContent($oField, $group_class);

                if($template_framework == "bootstrap" || $template_framework == "foundation") 
                {
                    $oField = ffField::factory($cm->oPage);
                    $oField->id = $prefix_image . "_md";
                    $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_image_md");
                    $oField->base_type = "Number";
					$oField->widget = "actex";
				    $oField->actex_update_from_db 		= !$params["image"]["multi_pairs"];
				    $oField->actex_dialog_url 			= $params["image"]["dialog_url"];
				    $oField->actex_dialog_edit_params 	= $params["image"]["dialog_edit_params"];
				    $oField->actex_dialog_delete_url 	= $params["image"]["dialog_delete_url"];
				    $oField->resources[] 				= $params["image"]["resources"];
				    $oField->source_SQL 				= $params["image"]["source_SQL"];
				    $oField->multi_pairs 				= $params["image"]["multi_pairs"];
					$oField->actex_father 				= $params["image"]["father"];
					$oField->actex_child 				= $params["image"]["child"];
					$oField->actex_related_field 		= $params["image"]["related_field"];
				    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("follow_default_size");
				    $oField->actex_hide_empty = "all";
					if(isset($arrDefaultValue[2])) {
	                	$default_value = $arrDefaultValue[2];
					}
                    $oField->default_value = new ffData($default_value, "Number");
                    $oField->setWidthComponent($image_columns);
                    $component->addContent($oField, $group_class);

                    $oField = ffField::factory($cm->oPage);
                    $oField->id = $prefix_image . "_sm";
                    $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_image_sm");
                    $oField->base_type = "Number";
					$oField->widget = "actex";
				    $oField->actex_update_from_db 		= !$params["image"]["multi_pairs"];
				    $oField->actex_dialog_url 			= $params["image"]["dialog_url"];
				    $oField->actex_dialog_edit_params 	= $params["image"]["dialog_edit_params"];
				    $oField->actex_dialog_delete_url 	= $params["image"]["dialog_delete_url"];
				    $oField->resources[] 				= $params["image"]["resources"];
				    $oField->source_SQL 				= $params["image"]["source_SQL"];
				    $oField->multi_pairs 				= $params["image"]["multi_pairs"];
					$oField->actex_father 				= $params["image"]["father"];
					$oField->actex_child 				= $params["image"]["child"];
					$oField->actex_related_field 		= $params["image"]["related_field"];
				    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("follow_default_size");
				    $oField->actex_hide_empty = "all";
					if(isset($arrDefaultValue[1])) {
	                	$default_value = $arrDefaultValue[1];
					}
                    $oField->default_value = new ffData($default_value, "Number");
                    $oField->setWidthComponent($image_columns);
                    $component->addContent($oField, $group_class);
                    
                    if($template_framework == "bootstrap")
                    {
                        $oField = ffField::factory($cm->oPage);
                        $oField->id = $prefix_image . "_xs";
                        $oField->label = ffTemplate::_get_word_by_code("extra_" . $template_framework . "_image_xs");
                        $oField->base_type = "Number";
						$oField->widget = "actex";
					    $oField->actex_update_from_db 		= !$params["image"]["multi_pairs"];
					    $oField->actex_dialog_url 			= $params["image"]["dialog_url"];
					    $oField->actex_dialog_edit_params 	= $params["image"]["dialog_edit_params"];
					    $oField->actex_dialog_delete_url 	= $params["image"]["dialog_delete_url"];
					    $oField->resources[] 				= $params["image"]["resources"];
					    $oField->source_SQL 				= $params["image"]["source_SQL"];
					    $oField->multi_pairs 				= $params["image"]["multi_pairs"];
						$oField->actex_father 				= $params["image"]["father"];
						$oField->actex_child 				= $params["image"]["child"];
						$oField->actex_related_field 		= $params["image"]["related_field"];
					    $oField->multi_select_one_label = ffTemplate::_get_word_by_code("follow_default_size");
					    $oField->actex_hide_empty = "all";
						if(isset($arrDefaultValue[0])) {
	                		$default_value = $arrDefaultValue[0];
						}
                        $oField->default_value = new ffData($default_value, "Number");
                        $oField->setWidthComponent($image_columns);
                        $component->addContent($oField, $group_class);
                    }
                }
            }            
			
		} 
		else
		{
			if($params === null || !isset($params["width"]) || $params["width"]) {
				$oField = ffField::factory($cm->oPage);
				$oField->id = (isset($params["width"]["name"]) ? $params["width"]["name"] : "width");
				$oField->label = ffTemplate::_get_word_by_code("grid_modify_width");
				$oField->required = true;
				$component->addContent($oField, $group_class);
			}

			if($params === null || !isset($params["class"]) || $params["class"]) {
				$oField = ffField::factory($cm->oPage);
				$oField->id = $oField->id = (isset($params["class"]["name"]) ? $params["class"]["name"] : "class");
				$oField->container_class = "fluid-dep";
				$oField->label = ffTemplate::_get_word_by_code("grid_default_class");
				$component->addContent($oField, $group_class);      
			}			
			
		}

		if($event_loaded === false) {
        	$component->addEvent("on_done_action", "SetFieldsGridSystem_on_done_action");
			if(is_subclass_of($component, "ffRecord_base")) {
				$component->addEvent("on_loaded_data", "SetFieldsGridSystem_on_loaded_data");
			}
			if(is_subclass_of($component, "ffDetails_base")) {
				$component->addEvent("on_loaded_row", "SetFieldsGridSystem_on_loaded_row");
			}
        	$event_loaded = true;
		}
		if(!$js) {
			$js = '$(function() {
							ff.pluginLoad("ff.cms.admin", "' . FF_THEME_DIR . "/" . THEME_INSET . '/javascript/tools/ff.cms.admin.js", function() {
								var container = jQuery(\'.dep-container-grid INPUT[type="hidden"][value!="0"]\').closest("fieldset");
								jQuery(container).each(function() {
									//jQuery(this).find(".check-container-grid INPUT").click();
								});
								
								jQuery(".fluid-def SELECT").change(); 
							});
						});';
			$cm->oPage->tplAddJs(null,null,null, false, $cm->isXHR(), $js);
		}
		
		return null;
	}
	
	function SetFieldsGridSystem_on_loaded_data($component) {
		if($component->record_exist && $component->first_access) {
			if(is_array($component->user_vars["one_field"])) {
        		foreach($component->user_vars["one_field"] AS $dst => $struct) {
        			$arrDstValue = array(); 
					$dst_value = $component->db[0]->getField($dst, "Text", true);
					
					if($dst_value) {
						$arrDstValue = explode(",", $dst_value);
					} elseif($struct["fluid"] 
                        && $component->form_fields[$struct["fluid"]]->getValue($component->form_fields[$struct["fluid"]]->base_type) == "0"
                        && $component->form_fields[$struct["fluid"]]->default_value->getValue($component->form_fields[$struct["fluid"]]->base_type) == "1"
                    ) {        
                        $component->form_fields[$struct["fluid"]]->value_ori = new ffData("1", $component->form_fields[$struct["fluid"]]->base_type);
                        $component->form_fields[$struct["fluid"]]->value = new ffData("1", $component->form_fields[$struct["fluid"]]->base_type);
                    }
 
					if(count($arrDstValue)) {
						if(count($arrDstValue) < count($struct["fields"]))
							$arrDstValue = array_fill(count($arrDstValue), count($struct["fields"]) - count($arrDstValue), $arrDstValue[count($arrDstValue) - 1]); 
					}

					if(is_array($struct["fields"]) && count($struct["fields"])) {
                        if($struct["nochange"]) {
                            $i = 0;
                        } else {
						    if(count($arrDstValue)) {
							    $i = count($arrDstValue) - 1;
						    }
                        }
					    foreach($struct["fields"] AS $field_key) {
							$value = $component->form_fields[$field_key]->default_value->getValue();
							if($struct["nochange"]) {
								$value = $arrDstValue[$i];
                                $i++;
                            } else {
                                if(array_key_exists($i, $arrDstValue)) {
								    $value = $arrDstValue[$i];
								    $i--;
							    }
                            }
							if(!$component->form_fields[$field_key]->user_vars["one_field"]["loaded"]) {
						        $component->form_fields[$field_key]->value_ori = new ffData($value, $component->form_fields[$field_key]->base_type);
						        $component->form_fields[$field_key]->value = new ffData($value, $component->form_fields[$field_key]->base_type);
							}
						}
					}
				}
			}
		}
	}
	function SetFieldsGridSystem_on_loaded_row($component, $source, $row) {
		if($source == "populate_edit" 
			|| $source == "edit" 
		) {
			if(is_array($component->user_vars["one_field"])) {
        		foreach($component->user_vars["one_field"] AS $dst => $struct) {
        			$arrDstValue = array();
					$dst_value = $component->db[0]->getField($dst, "Text", true);
					if($dst_value) {
						$arrDstValue = explode(",", $dst_value);
                    } elseif($struct["fluid"] 
                        && $component->recordset_ori[$row][$struct["fluid"]]->getValue($component->form_fields[$struct["fluid"]]->base_type) == "0"
                        && $component->recordset_ori[$row][$struct["fluid"]]->default_value->getValue($component->form_fields[$struct["fluid"]]->base_type) == "1"
                    ) {
                        $component->recordset_ori[$row][$struct["fluid"]]->value_ori = new ffData("1", $component->form_fields[$struct["fluid"]]->base_type);
                        $component->recordset_ori[$row][$struct["fluid"]]->value = new ffData("1", $component->form_fields[$struct["fluid"]]->base_type);
					}
					if(count($arrDstValue)) {
						if(count($arrDstValue) < count($struct["fields"]))
							$arrDstValue = array_fill(count($arrDstValue) - 1, count($arrDstValue) - count($struct["fields"]), $arrDstValue[count($arrDstValue)]);
					}

					if(is_array($struct["fields"]) && count($struct["fields"])) {
						if(count($arrDstValue)) {
							$i = count($arrDstValue) - 1;
						}

				        foreach($struct["fields"] AS $field_key) {
							$value = $component->form_fields[$field_key]->default_value->getValue();
							if(array_key_exists($i, $arrDstValue)) {
								$value = $arrDstValue[$i];
								$i--;
							}
							if(!$component->form_fields[$field_key]->user_vars["one_field"]["loaded"]) {
					            $component->recordset_ori[$row][$field_key] = new ffData($value, $component->form_fields[$field_key]->base_type);
					            $component->recordset[$row][$field_key] = new ffData($value, $component->form_fields[$field_key]->base_type);
							}							
						}
					}
				}
			}
		}
		//ffErrorHandler::raise("ASD", E_USER_ERROR, null, get_defined_vars());
	}
	function SetFieldsGridSystem_on_done_action($component, $action) {
        $db = ffDB_Sql::factory();

		if(strlen($action)) {
			switch ($action) {
				case "insert":
				case "update":
					if(is_array($component->user_vars["one_field"])) {
                        foreach($component->user_vars["one_field"] AS $dst => $struct) {
                            if(is_subclass_of($component, "ffRecord_base")) {
	                            $arrOnField = null;
	                            $count_zero = 0;
	                            if(is_array($struct["fields"]) && count($struct["fields"])) {
	                                foreach($struct["fields"] AS $field_key) {
	                                    $field_value = $component->form_fields[$field_key]->getValue();
	                                    $arrOnField[] = $field_value;
	                                    
	                                    if(!$field_value)
	                                        $count_zero++;
	                                }

	                                if($count_zero == count($arrOnField)) {
	                                    $arrOnField = array();
	                                } else {
										if(!isset($struct["nochange"])) {
		                                    if(count($arrOnField) < 4)
		                                        $arrOnField = array_merge($arrOnField, array_fill(count($arrOnField), 4 - count($arrOnField), $arrOnField[count($arrOnField) - 1]));

		                                    $arrOnField = array_reverse($arrOnField);
										}
	                                }
	                            }
	                            if(is_array($arrOnField)) {
						            $sSQL = "UPDATE `" . $component->src_table . "`
									        SET `" . $dst . "` = " . $db->toSql(implode(",", $arrOnField)) . "
	                                        WHERE ID = " . $db->toSql($component->key_fields["ID"]->value);
	                                $db->execute($sSQL);
	                            }
							} elseif(is_subclass_of($component, "ffDetails_base")) {
								if(is_array($component->recordset) && count(is_array($component->recordset))) {
									foreach($component->recordset AS $rst_key => $rst_fields) {
			                            $arrOnField = null;
			                            $count_zero = 0;
			                            if(is_array($struct["fields"]) && count($struct["fields"])) {
			                                foreach($struct["fields"] AS $field_key) {
			                                    $field_value = $rst_fields[$field_key]->getValue();
			                                    $arrOnField[] = $field_value;
			                                    
			                                    if(!$field_value)
			                                        $count_zero++;
			                                }

			                                if($count_zero == count($arrOnField)) {
			                                    $arrOnField = array();
			                                } else {
												if(!isset($struct["nochange"])) {
				                                    if(count($arrOnField) < 4)
				                                        $arrOnField = array_merge($arrOnField, array_fill(count($arrOnField), 4 - count($arrOnField), $arrOnField[count($arrOnField) - 1]));

				                                    $arrOnField = array_reverse($arrOnField);
												}
			                                }
			                            }
			                            if(is_array($arrOnField)) {
								            $sSQL = "UPDATE `" . $component->src_table . "`
											        SET `" . $dst . "` = " . $db->toSql(implode(",", $arrOnField)) . "
			                                        WHERE ID = " . $db->toSql($rst_fields["ID"]);
			                                $db->execute($sSQL);
			                            }
									}
								}								
							}
                        }
                    }
                    break;
				default:
			}
		}
	}
