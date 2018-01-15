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
	$def["default"] = array(
        "table"										=> null //(is_array($type) ? null : $type)
        , "type" 									=> null //(is_array($type) ? null : $type)
        , "field" => array(
        	"lang" 									=> "ID_lang"
        	, "permalink" 							=> "permalink"
        	, "smart_url" 							=> "smart_url"
        	, "title" 								=> "meta_title"
        	, "header" 								=> "meta_title_alt"
        	, "description" 						=> "meta_description"
            
            , "robots"                              => "meta_robots"
            , "canonical"                           => "meta_canonical"
            , "meta"                                => "meta"
            , "referer"                             => "referer"
            , "httpstatus"                          => "httpstatus"
            
        	, "keywords" 							=> "keywords"
        	, "permalink_parent" 					=> "permalink_parent"
			, "visible" 							=> "visible"
			, "alt_url" 							=> "alt_url"
			, "isbn" 								=> false

        	, "parent" 								=> "permalink_parent"
        	, "name" 								=> "smart_url"
        	, "clone" 								=> false
        	, "public" 								=> false
        	, "is_dir" 								=> false
        	, "last_update" 						=> "last_update"
        	, "created"								=> "created"
        	, "owner" 								=> "owner"
        	, "tags" 								=> "tags"
        	, "place" 								=> "place"
        	, "ID_place" 							=> "ID_place"
        	, "priority" 							=> "priority"
        	, "cats" 								=> "cats"
        	, "order" 								=> "order"
        	, "ID_type" 							=> false
        	, "ID_category" 						=> false
        	, "ajax" 								=> false
        )
        , "sql" => array( 
        	"select" => array(
        		"ID_vgallery" 						=> ""
        		, "vgallery_name" 					=> ""
        		, "is_dir" 							=> "0"
			)
        	, "where" => array(
        		"public" 							=> ""
        	)
        )
        , "seo" => array(
        	"table" 								=> null //(is_array($type) ? null : $type . "_rel_languages")
        	, "primary_table" 						=> null //(is_array($type) ? null : $type)
        	, "primary_parent" 						=> "parent"
        	, "primary_permalink" 					=> false
        	, "rel_key" 							=> null //"ID_" . (is_array($type) ? null : $type)
        	, "rel_lang" 							=> "ID_lang"
        	, "rel_field" 							=> false
        	
        	, "permalink" 							=> "permalink"
        	, "smart_url" 							=> "smart_url"
        	, "title" 								=> "meta_title"
        	, "header" 								=> "meta_title_alt"
        	, "description" 						=> "meta_description"

            , "robots"                              => "meta_robots"
            , "canonical"                           => "meta_canonical"
            , "meta"                                => "meta"
            , "httpstatus"                          => "httpstatus"
            
        	, "keywords" 							=> "keywords"
        	, "permalink_parent" 					=> "permalink_parent"
        	, "visible" 							=> "visible"
        	, "alt_url" 							=> "alt_url"
        ) 
        , "settings" => false
	);
  
  	$def["anagraph"] = array(
		"table" 							=> "anagraph"
		, "type" 							=> "anagraph"
		, "icon" 							=> "vg-gallery"
		, "class" 							=> "content"
		, "label" 							=> ffTemplate::_get_word_by_code("modify_gallery_seo")
		, "cache" => array(
			"type" 							=> "A"
		)
		, "field" => array(
			"lang" 							=> false
			, "permalink" 					=> "permalink"
			, "smart_url" 					=> "smart_url"
			, "title" 						=> "meta_title"
			, "header" 						=> false
			, "description" 				=> "meta_description"

			, "robots"                      => "meta_robots"
			, "canonical"                   => "meta_canonical"
			, "meta"                        => "meta"
			, "referer"                     => "referer"
			, "httpstatus"                  => "httpstatus"

			, "keywords" 					=> "keywords"
			, "permalink_parent" 			=> "parent"
			, "visible" 					=> "visible"
			, "alt_url" 					=> false
			, "isbn" 						=> false

			, "parent" 						=> "parent"
			, "name"						=> "smart_url"
			, "clone" 						=> false
			, "public" 						=> false
			, "is_dir" 						=> false
			, "last_update" 				=> "last_update"
			, "created" 					=> "created"
			, "owner" 						=> "owner"
			, "tags" 						=> "tags"
			, "place" 						=> false
			, "ID_place" 					=> false
			, "priority" 					=> "priority"
			, "cats" 						=> false
			, "order" 						=> "order"
			, "ID_type" 					=> "ID_type"
			, "ID_category" 				=> false
			, "ajax" 						=> false
		)
		, "sql" => array(
			"select" => array(
				"ID_vgallery" 				=> 0
				, "vgallery_name" 			=> "'anagraph'"
				, "is_dir" 					=> "0"
			)
			, "where" => array(
				"public" 					=> ""
			)
		)
		, "seo" => array(
			"table" 						=> "anagraph"
			, "primary_table" 				=> "anagraph"
			, "primary_parent" 				=> "parent"
			, "primary_permalink" 			=> "permalink"
			, "rel_key"						=> "ID"
			, "rel_lang" 					=> false
			, "rel_field" 					=> false

			, "permalink" 					=> "permalink"
			, "smart_url" 					=> "smart_url"
			, "title" 						=> "meta_title"
			, "header" 						=> false
			, "description" 				=> "meta_description"

			, "robots"                      => "meta_robots"
			, "canonical"                   => "meta_canonical"
			, "meta"                        => "meta"
			, "httpstatus"                  => "httpstatus"

			, "keywords" 					=> "keywords"
			, "permalink_parent" 			=> false
			, "visible" 					=> "visible"
			, "alt_url" 					=> false
		)
		, "settings" => array(
			"limit_type" 					=> false
			, "limit_level" 				=> 1
			, "insert_on_lastlevel" 		=> true
			, "ID_vgallery" 				=> 0
			, "enable_highlight" 			=> true
			, "show_owner_by_categories" 	=> false
			, "show_isbn" 					=> false
			, "enable_multilang" 			=> false
			, "drag_sort_node_enabled" 		=> false
			, "drag_sort_dir_enabled" 		=> false
			, "enable_tag" 					=> -1
			, "enable_tab" 					=> false
			, "prefix_file_system" 			=> ""
			, "enable_model" 				=> false
			, "enable_adv_group" 			=> false
			, "enable_adv_visible" 			=> false
			, "enable_seo" 					=> true
		)
	);
  	$def["files"] = array( //DA FINIRE
        		"table" 							=> "files"
        		, "type" 							=> "files"
				, "icon" 							=> "vg-gallery"
				, "class" 							=> "content"
				, "label" 							=> ffTemplate::_get_word_by_code("modify_gallery_seo")
                , "cache" => array(
					"type" 							=> "G"
				)
        		, "field" => array(
        			"lang" 							=> "ID_languages"
        			, "permalink" 					=> false
        			, "smart_url" 					=> "name"
        			, "title" 						=> "alias"
        			, "header" 						=> "description"
        			, "description" 				=> "description"
                    
                    , "robots"                      => false
                    , "canonical"                   => false
                    , "meta"                        => false
                    , "referer"                     => false
                    , "httpstatus"                  => false

                    , "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> "parent"
        			, "visible" 					=> false
        			, "alt_url" 					=> false
        			, "isbn" 						=> false
        			
        			, "parent"						=> "parent"
        			, "name" 						=> "name"
        			, "clone" 						=> false
        			, "public" 						=> false
        			, "is_dir" 						=> "is_dir"
        			, "last_update" 				=> "last_update"
        			, "created" 					=> "created"
        			, "owner" 						=> "owner"
        			, "tags" 						=> "tags"
        			, "place" 						=> false
        			, "ID_place" 					=> false
        			, "priority" 					=> false
        			, "cats" 						=> false
        			, "order" 						=> "order"
        			, "ID_type" 					=> false
        			, "ID_category" 				=> false
        			, "ajax" 						=> false
        		)
        		, "sql" => array(
        			"select" => array(
        				"ID_vgallery" 				=> 0
        				, "vgallery_name" 			=> "gallery"
        				, "is_dir" 					=> "is_dir"
        			)
        			, "where" => array(
        				"public" 					=> ""
        			)
        		)
        		, "seo" => array(
        			"table" 						=> "files_rel_languages"
        			, "primary_table" 				=> "files"
        			, "primary_parent" 				=> "parent"
        			, "primary_permalink" 			=> "CONCAT(files.parent, '/', files.name) AS primary_permalink"
        			, "rel_key"						=> "ID_files"
        			, "rel_lang" 					=> "ID_languages"
        			, "rel_field" 					=> false

        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "smart_url"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "meta_title_alt"
        			, "description" 				=> "meta_description"
                    
                    , "robots"                      => false
                    , "canonical"                   => false
                    , "meta"                        => false
                    , "httpstatus"                  => false

                    , "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> "permalink_parent"
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> false
        		)        	
        		, "settings" => array(
        			"limit_type" 					=> false
        			, "limit_level" 				=> 1
        			, "insert_on_lastlevel" 		=> true
        			, "ID_vgallery" 				=> 0
        			, "enable_highlight" 			=> true
        			, "show_owner_by_categories" 	=> -1
        			, "show_isbn" 					=> -1
        			, "enable_multilang" 			=> false
        			, "drag_sort_node_enabled" 		=> false
        			, "drag_sort_dir_enabled" 		=> false
        			, "enable_tag" 					=> -1
        			, "enable_tab" 					=> false
        			, "prefix_file_system" 			=> ""
        			, "enable_model" 				=> false
        			, "enable_adv_group" 			=> false
        			, "enable_adv_visible" 			=> true
        			, "enable_seo" 					=> true
        		)
	        );
  	$def["page"] = array(
        		"table"								=> "static_pages"
        		, "type" 							=> "page"
				, "icon" 							=> "vg-static-menu"
				, "class" 							=> "content"
				, "label" 							=> ffTemplate::_get_word_by_code("modify_static_seo")
				, "parent_editable"					=> true
                , "cache" => array(
					"type" 							=> "S"
				)
        		, "field" => array(
        			"lang" 							=> false
        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "name"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "meta_title_alt"
        			, "description" 				=> "meta_description"

                    , "robots"                      => "meta_robots"
                    , "canonical"                   => "meta_canonical"
                    , "meta"                        => "meta"
                    , "referer"                     => "referer"
                    , "httpstatus"                  => "httpstatus"
                    
        			, "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> "parent"
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> "alt_url"
        			, "isbn" 						=> false

        			, "parent" 						=> "parent"
        			, "name" 						=> "name"
        			, "clone" 						=> false
        			, "public" 						=> false
        			, "is_dir" 						=> false
        			, "last_update" 				=> "last_update"
        			, "published_at" 				=> false
        			, "created" 					=> false
        			, "owner" 						=> "owner"
        			, "tags" 						=> "tags"
        			, "place" 						=> false
        			, "ID_place" 					=> false
        			, "priority" 					=> false
        			, "cats" 						=> false
        			, "order" 						=> "sort"
        			, "ID_type" 					=> false
        			, "ID_category" 				=> false
        			, "ajax" => array(
        				"enable" 					=> "use_ajax"
        				, "event" 					=> "ajax_on_event"
        			)
        		)
        		, "sql" => array(
        		)
        		, "seo" => array(
        			"table" 						=> "static_pages_rel_languages"
        			, "primary_table" 				=> "static_pages"
        			, "primary_parent" 				=> "parent"
        			, "primary_permalink" 			=> "permalink"
        			, "rel_key" 					=> "ID_static_pages"
        			, "rel_lang" 					=> "ID_languages"
        			, "rel_field" 					=> false

        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "smart_url"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "meta_title_alt"
        			, "description" 				=> "meta_description"

                    , "robots"                      => "meta_robots"
                    , "canonical"                   => "meta_canonical"
                    , "meta"                        => "meta"
                    , "httpstatus"                  => "httpstatus"
                    
        			, "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> "permalink_parent"
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> "alt_url"
        		)    
        		, "settings" => array()
		    );
	$def["tag"] = array(
        		"table"								=> "search_tags_page"
        		, "key"								=> "code"
        		, "type" 							=> "tag"
				, "icon" 							=> "tag"
				, "class" 							=> "content"
				, "label" 							=> ffTemplate::_get_word_by_code("modify_tag_seo")
				, "parent_editable"					=> false
                , "cache" => array(
					"type" 							=> "LT"
				)
        		, "field" 							=> false
        		, "sql" => array(
        		)
        		, "seo" => array(
        			"table" 						=> "search_tags_page"
        			, "primary_table" 				=> "search_tags_page"
        			, "primary_parent" 				=> "parent"
        			, "primary_permalink" 			=> "permalink"
        			, "rel_key" 					=> "code"
        			, "rel_lang" 					=> "ID_lang"
        			, "rel_field" 					=> false

        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "smart_url"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "h1"
        			, "description" 				=> "meta_description"

                    , "robots"                      => "meta_robots"
                    , "canonical"                   => "meta_canonical"
                    , "meta"                        => "meta"
                    , "httpstatus"                  => "httpstatus"
                    
        			, "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> false
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> false
        		)    
        		, "settings" => array()
		    );
	$def["city"] = array(
        		"table"								=> FF_SUPPORT_PREFIX . "city"
        		, "type" 							=> "place"
				, "icon" 							=> "map"
				, "class" 							=> "content"
				, "label" 							=> ffTemplate::_get_word_by_code("modify_support_city")
				, "parent_editable"					=> false
                , "cache" => array(
					"type" 							=> "LP"
				)
        		, "field" 							=> false
        		, "sql" => array(
        		)
        		, "seo" => array(
        			"table" 						=> FF_SUPPORT_PREFIX . "city"
        			, "primary_table" 				=> FF_SUPPORT_PREFIX . "city"
        			, "primary_parent" 				=> false
        			, "primary_permalink" 			=> "permalink"
        			, "rel_key" 					=> "ID"
        			, "rel_lang" 					=> false
        			, "rel_field" 					=> false

        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "smart_url"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "h1"
        			, "description" 				=> "meta_description"

                    , "robots"                      => "meta_robots"
                    , "canonical"                   => "meta_canonical"
                    , "meta"                        => "meta"
                    , "httpstatus"                  => "httpstatus"
                    
        			, "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> false
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> false
        		)    
        		, "settings" => array()
		    );	
	$def["province"] = array(
        		"table"								=> FF_SUPPORT_PREFIX . "province"
        		, "type" 							=> "place"
				, "icon" 							=> "map"
				, "class" 							=> "content"
				, "label" 							=> ffTemplate::_get_word_by_code("modify_support_province")
				, "parent_editable"					=> false
                , "cache" => array(
					"type" 							=> "LP"
				)
        		, "field" 							=> false
        		, "sql" => array(
        		)
        		, "seo" => array(
        			"table" 						=> FF_SUPPORT_PREFIX . "province"
        			, "primary_table" 				=> FF_SUPPORT_PREFIX . "province"
        			, "primary_parent" 				=> false
        			, "primary_permalink" 			=> "permalink"
        			, "rel_key" 					=> "ID"
        			, "rel_lang" 					=> false
        			, "rel_field" 					=> false

        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "smart_url"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "h1"
        			, "description" 				=> "meta_description"

                    , "robots"                      => "meta_robots"
                    , "canonical"                   => "meta_canonical"
                    , "meta"                        => "meta"
                    , "httpstatus"                  => "httpstatus"
                    
        			, "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> false
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> false
        		)    
        		, "settings" => array()
		    );
	$def["region"] = array(
        		"table"								=> FF_SUPPORT_PREFIX . "region"
        		, "type" 							=> "place"
				, "icon" 							=> "map"
				, "class" 							=> "content"
				, "label" 							=> ffTemplate::_get_word_by_code("modify_support_region")
				, "parent_editable"					=> false
                , "cache" => array(
					"type" 							=> "LP"
				)
        		, "field" 							=> false
        		, "sql" => array(
        		)
        		, "seo" => array(
        			"table" 						=> FF_SUPPORT_PREFIX . "region"
        			, "primary_table" 				=> FF_SUPPORT_PREFIX . "region"
        			, "primary_parent" 				=> false
        			, "primary_permalink" 			=> "permalink"
        			, "rel_key" 					=> "ID"
        			, "rel_lang" 					=> false
        			, "rel_field" 					=> false

        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "smart_url"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "h1"
        			, "description" 				=> "meta_description"

                    , "robots"                      => "meta_robots"
                    , "canonical"                   => "meta_canonical"
                    , "meta"                        => "meta"
                    , "httpstatus"                  => "httpstatus"
                    
        			, "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> false
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> false
        		)    
        		, "settings" => array()
		    );
	$def["state"] = array(
        		"table"								=> FF_SUPPORT_PREFIX . "state"
        		, "type" 							=> "place"
				, "icon" 							=> "map"
				, "class" 							=> "content"
				, "label" 							=> ffTemplate::_get_word_by_code("modify_support_state")
				, "parent_editable"					=> false
                , "cache" => array(
					"type" 							=> "LP"
				)
        		, "field" 							=> false
        		, "sql" => array(
        		)
        		, "seo" => array(
        			"table" 						=> FF_SUPPORT_PREFIX . "state"
        			, "primary_table" 				=> FF_SUPPORT_PREFIX . "state"
        			, "primary_parent" 				=> false
        			, "primary_permalink" 			=> "permalink"
        			, "rel_key" 					=> "ID"
        			, "rel_lang" 					=> false
        			, "rel_field" 					=> false

        			, "permalink" 					=> "permalink"
        			, "smart_url" 					=> "smart_url"
        			, "title" 						=> "meta_title"
        			, "header" 						=> "h1"
        			, "description" 				=> "meta_description"

                    , "robots"                      => "meta_robots"
                    , "canonical"                   => "meta_canonical"
                    , "meta"                        => "meta"
                    , "httpstatus"                  => "httpstatus"
                    
        			, "keywords" 					=> "keywords"
        			, "permalink_parent" 			=> false
        			, "visible" 					=> "visible"
        			, "alt_url" 					=> false
        		)    
        		, "settings" => array()
		    );
	if(OLD_VGALLERY) {
		$def["vgallery"] = array(
        			"table" 						=> "vgallery_nodes"
        			, "type" 						=> "vgallery"
					, "icon" 						=> "vg-virtual-gallery"
					, "class" 						=> "content"
					, "label" 						=> ffTemplate::_get_word_by_code("modify_vgallery_seo")
					, "cache" => array(
						"type" 						=> "V"
					)
        			, "field" => array(
        				"lang" 						=> "ID_lang"
        				, "permalink" 				=> false
        				, "smart_url" 				=> "name"
        				, "title" 					=> "meta_title"
        				, "header" 					=> "meta_title_alt"
        				, "description" 			=> "meta_description"

                        , "robots"                  => "meta_robots"
                        , "canonical"               => "meta_canonical"
                        , "meta"                    => "meta"
                        , "referer"                 => "referer"
                        , "httpstatus"              => "httpstatus"
                        
        				, "keywords" 				=> "keywords"
        				, "permalink_parent" 		=> "parent"
        				, "visible" 				=> "visible"
        				, "alt_url" 				=> "alt_url"
        				, "isbn" 					=> "isbn"

        				, "parent" 					=> "parent"
        				, "name" 					=> "name"
        				, "clone" 					=> "is_clone"
        				, "public" 					=> "public"
        				, "is_dir" 					=> "is_dir"
        				, "last_update" 			=> "last_update"
        				, "published_at"			=> "published_at"
        				, "created" 				=> "created"
        				, "owner" 					=> "owner"
        				, "tags" 					=> "tags"
        				, "place" 					=> "place"
        				, "ID_place" 				=> "ID_place"
        				, "priority" 				=> "priority"
        				, "cats" 					=> "cats"
        				, "order" 					=> "order"
        				, "ID_type" 				=> "ID_type"
        				, "ID_category" 			=> "ID_vgallery"
        				, "ajax" => array(
        					"enable" 				=> "use_ajax"
        					, "event" 				=> "ajax_on_event"
        				)
        			)
        			, "sql" => array(
        				"select" => array(
        					"ID_vgallery" 			=> "(SELECT vgallery.ID FROM vgallery WHERE vgallery.ID = vgallery_nodes.ID_vgallery)"
        					, "vgallery_name" 		=> "(SELECT vgallery.name FROM vgallery WHERE vgallery.ID = vgallery_nodes.ID_vgallery)"
        					, "is_dir" 				=> "is_dir"
        				)
        				, "where" => array(
        					"public" 				=> " AND (SELECT vgallery.public FROM vgallery WHERE vgallery.ID = vgallery_nodes.ID_vgallery) "
        					, "settings" 			=> " AND vgallery.public = 0 "
        				)
        			)
        			, "seo" => array(
        				"table" 					=> "vgallery_rel_nodes_fields"
        				, "primary_table" 			=> "vgallery_nodes"
        				, "primary_parent" 			=> "parent"
        				, "primary_permalink" 		=> "permalink"
        				, "rel_key" 				=> "ID_nodes"
        				, "rel_lang" 				=> "ID_lang"
        				, "rel_field" 				=> "ID_fields"
        				, "permalink" 				=> false
        				, "smart_url" => array(
        					"field" 				=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'smart_url' AND vgallery_type.name = 'System')"
        				)
        				, "title" => array(
        					"field"					=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'meta_title' AND vgallery_type.name = 'System')"
        				)
        				, "header" => array(
        					"field" 				=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'meta_title_alt' AND vgallery_type.name = 'System')"
        				)
        				, "description" => array(
        					"field" 				=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'meta_description' AND vgallery_type.name = 'System')"
        				)

                        , "robots"                  => false
                        , "canonical"               => false
                        , "meta"                    => false
                        , "httpstatus"              => false
                        
        				, "keywords" => array(
        					"field" 				=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'keywords' AND vgallery_type.name = 'System')"
        				)
        				, "permalink_parent" => array(
        					"field" 				=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'permalink_parent' AND vgallery_type.name = 'System')"
        				)
						, "visible" => array(
        					"field" 				=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'visible' AND vgallery_type.name = 'System')"
        				)
        				, "alt_url" => array(
        					"field" 				=> "description"
        					, "where" 				=> "(SELECT vgallery_fields.ID FROM vgallery_fields INNER JOIN vgallery_type ON vgallery_type.ID = vgallery_fields.ID_type WHERE vgallery_fields.name = 'alt_url' AND vgallery_type.name = 'System')"
        				)        			
        			)    
        			, "settings" 					=> "vgallery"
		        );
	} else {
		$def["vgallery"] = array(
        			"table" 						=> "vgallery_nodes"
        			, "type" 						=> "vgallery"
        			, "field" => array(
        				"lang" 						=> "ID_lang"
        				, "permalink" 				=> "permalink"
        				, "smart_url" 				=> "name"
        				, "title" 					=> "meta_title"
        				, "header" 					=> "meta_title_alt"
        				, "description" 			=> "meta_description"

                        , "robots"                  => "meta_robots"
                        , "canonical"               => "meta_canonical"
                        , "meta"                    => "meta"
                        , "referer"                 => "referer"
                        , "httpstatus"              => "httpstatus"
                        
        				, "keywords" 				=> "keywords"
        				, "permalink_parent" 		=> "parent"
        				, "visible" 				=> "visible"
        				, "alt_url" 				=> "alt_url"
        				, "isbn" 					=> "isbn"

        				, "parent" 					=> "parent"
        				, "name" 					=> "name"
        				, "clone" 					=> "is_clone"
        				, "public"					=> "public"
        				, "is_dir" 					=> "is_dir"
        				, "last_update" 			=> "last_update"
        				, "published_at" 			=> "published_at"
        				, "created" 				=> "created"
        				, "owner" 					=> "owner"
        				, "tags" 					=> "tags"
        				, "place" 					=> "place"
        				, "ID_place" 				=> "ID_place"
        				, "priority" 				=> "priority"
        				, "cats" 					=> "cats"
        				, "order" 					=> "order"
        				, "ID_type" 				=> "ID_type"
        				, "ID_category" 			=> "ID_vgallery"
        				, "ajax" => array(
        					"enable"				=> "use_ajax"
        					, "event" 				=> "ajax_on_event"
        				)
        			)
        			, "sql" => array(
        				"select" => array(
        					"ID_vgallery" 			=> "(SELECT vgallery.ID FROM vgallery WHERE vgallery.ID = vgallery_nodes.ID_vgallery)"
        					, "vgallery_name" 		=> "(SELECT vgallery.name FROM vgallery WHERE vgallery.ID = vgallery_nodes.ID_vgallery)"
        					, "is_dir" 				=> "is_dir"
        				)
        				, "where" => array(
        					"public" 				=> " AND (SELECT vgallery.public FROM vgallery WHERE vgallery.ID = vgallery_nodes.ID_vgallery) "
        					, "settings" 			=> " AND vgallery.public = 0 "
        				)
        			)
        			, "seo" => array(
        				"table" 					=> "vgallery_nodes_rel_languages"
        				, "primary_table" 			=> "vgallery_nodes"
        				, "primary_parent" 			=> "parent"
        				, "primary_permalink" 		=> "permalink"
        				, "rel_key" 				=> "ID_nodes"
        				, "rel_lang" 				=> "ID_lang"
        				, "rel_field" 				=> false

        				, "permalink" 				=> "permalink"
        				, "smart_url" 				=> "smart_url"
        				, "title" 					=> "meta_title"
        				, "header" 					=> "meta_title_alt"
        				, "description" 			=> "meta_description"

                        , "robots"                  => "meta_robots"
                        , "canonical"               => "meta_canonical"
                        , "meta"                    => "meta"
                        , "httpstatus"              => "httpstatus"
                        
        				, "keywords" 				=> "keywords"
        				, "permalink_parent" 		=> "permalink_parent"
        				, "visible" 				=> "visible"
        				, "alt_url" 				=> "alt_url"
        			)    
        			, "settings" 					=> "vgallery"
		        );
	}