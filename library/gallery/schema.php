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

$schema["db"]["data_source"]["module_maps_marker"] = array(
    "key" => "ID_node"
    , "label" => null
    , "record_url" => "/restricted/modules/maps/extra/modify"
    , "record_id" => "MapsMarkerModify"
    , "record_key" => "mapsmrk-ID"
    , "record_params" => array(
		"node" => null
		, "src" => array(
			"field" => "tbl_src"
			, "request" => "src"
		)
    )
    , "fields" => array(
		"coords" => array(
			"multi" => array(
				"coords_title"
				, "coords_lat"
				, "coords_lng"
				, "coords_zoom"
			)
		    , "extended_type" => "GMap"
		)
		, "smart_url" => array(
		    "hide" => true
		    , "normalize" => true
		    , "value" => "coords_title"
		)
		, "tbl_src" => array(
		    "hide" => true
		    , "value" => "src"
		    , "require" => true
		)
		, "ID_lang" => array(
		    "hide" => true
		    , "value" => array("user_vars" => "ID_lang")
		)
    )
    , "field_default" => array(
    	"coords"
		, "description"
	)
    , "order_by" => "coords"
    , "data_type" => array("selection")
);

$schema["db"]["selection_data_source"]["anagraph_role"] = array( 
    "key" => "ID"
    , "label" => "Titoli anagrafici"
    , "query" => "SELECT anagraph_role.ID, [DISPLAY_VALUE] AS name
                    FROM anagraph_role
                    WHERE 1
                    ORDER BY name"
    , "limit" => array("anagraph") 
);

$service_schema["anagraph"]["table"] = "anagraph";
$service_schema["anagraph"]["operations"]["GET"] = array(
    "parameters" => array(
	"category" => array(
	    "allowMultiple" => true
	)
    )
    , "errorResponses" => array()
);
$service_schema["anagraph"]["struct_data"] = array(
    "type" => "person"
    , "microdata" => array(
	"scope" => "http://schema.org/Person"
    )
    , "microformat" => array(
	"scope" => array()
    )
);
$service_schema["anagraph"]["field"] = array(
    "avatar" => array(
	    "type" => "image"
	    , "noimg" => array(
	        "icon" => "noimg"
	        , "url" => null
	        , "thumb" => "4x" 
	    )
	    , "thumb" => "80x80"
	    , "struct_data" => array(
	        "type" => "person"
	        , "microdata" => array(
		    "prop" => "image"
		    , "scope" => "http://schema.org/Person"
	        )
	        , "microformat" => array(
		    "prop" => array(
		        "class" => "photo"
		    )
		    , "scope" => array(
		    )
	        )
	    )
    )
    , "categories_name" => array(
	    "type" => "comma-to-div"
    )
    , "permalink" => array(
	    "type" => "link"
	    , "struct_data" => array(
	        "type" => "person"
	        , "microdata" => array(
		    "prop" => "name"
		    , "scope" => "http://schema.org/Person"
	        )
	        , "microformat" => array(
		    "proplink" => array(
		        "class" => "url"
		        , "rel" => "author"
		    )
		    , "prop" => array(
		        "class" => "fn"
		    )
		    , "scope" => array(
		    )
	        )
	    )
    )
    , "tags" => array(
	    "struct_data" => array(
	        "type" => "person"
	        , "microdata" => array(
		    "prop" => "jobTitle"
		    , "scope" => "http://schema.org/Person"
	        )
	        , "microformat" => array(
		    "prop" => array(
		        "class" => "role"
		    )
		    , "scope" => array(
		    )
	        )
	        , "override" => array("name", "permalink")
	    )
	    , "data_source" => array(
	        "tbl" => "search_tags"
	        , "fields" => array("name", "permalink")
	    )
    )
    , "degree" => array(
        "data_source" => array(
            "tbl" => "anagraph_role"
            , "fields" => array("name")
        )
    )
    , "categories" => array(
	    "data_source" => array(
	        "tbl" => "anagraph_categories"
	        , "fields" => array("name", "permalink")
	    )
    )
);
$service_schema["anagraph"]["action"] = array(
    "default" => array("ID" => "keys[anagraph-ID]"
	, "ID_value" => "[ID]"
    )
    , "addnew" => array("path" => "modify"
	, "action" => "insert"
	, "params" => array()
    )
    , "edit" => array("ID" => "keys[anagraph-ID]"
	, "ID_value" => "[ID]"
	, "path" => "modify"
	, "action" => "update"
	, "params" => array()
    )
    , "delete" => array("ID" => "keys[anagraph-ID]"
	, "ID_value" => "[ID]"
	, "path" => "modify"
	, "action" => "delete"
	, "source_action" => "confirmdelete"
	, "component" => null
	, "params" => array()
    )
);
$service_schema["anagraph"]["relationship"] = array();
$service_schema["anagraph"]["relationship"]["operation"]["key"] = "ID_anagraph";
$service_schema["anagraph"]["relationship"]["operation"]["rel_key"] = "ID";
$service_schema["anagraph"]["relationship"]["operation"]["multi"] = true;

$service_schema["operation"]["table"] = "ecommerce_documents_bill";
$service_schema["operation"]["operations"]["GET"] = array(
    "parameters" => array(
	"operation" => array(
	)
    )
    , "errorResponses" => array()
);
$service_schema["operation"]["field"] = array();
$service_schema["operation"]["relationship"] = array();
$service_schema["operation"]["relationship"]["anagraph"]["key"] = "ID";
$service_schema["operation"]["relationship"]["anagraph"]["rel_key"] = "ID_anagraph";
$service_schema["operation"]["relationship"]["anagraph"]["multi"] = false;
$service_schema["operation"]["relationship"]["payment"]["key"] = "ID_bill";
$service_schema["operation"]["relationship"]["payment"]["rel_key"] = "ID";
$service_schema["operation"]["relationship"]["payment"]["multi"] = true;

$service_schema["payment"]["table"] = "ecommerce_documents_payments";
$service_schema["payment"]["operations"]["GET"] = array(
    "parameters" => array(
	"operation" => array(
	)
    )
    , "errorResponses" => array()
);
$service_schema["payment"]["field"] = array(
	"date" => array(
		"type" => "date"
	)
	, "value" => array(
		"type" => "currency"
	)
	, "payed_value" => array(
		"type" => "currency"
	)
	, "tax_price" => array(
		"type" => "currency"
	)
	, "rebate" => array(
		"type" => "currency"
	)
);
	
$service_schema["payment"]["relationship"] = array();
$service_schema["payment"]["relationship"]["operation"]["key"] = "ID";
$service_schema["payment"]["relationship"]["operation"]["rel_key"] = "ID_bill";
$service_schema["payment"]["relationship"]["operation"]["multi"] = false;

$service_schema["form"]["table"] = "module_form_nodes";
$service_schema["form"]["operations"]["GET"] = array(
    "parameters" => array(
	"category" => array(
	    "allowMultiple" => true
	)
	, "revision" => array(
	)
    )
    , "errorResponses" => array()
);
$service_schema["form"]["field"] = array(
    "created" => array(
	"type" => "timestamp"
    )
    , "revision_last_created" => array(
	"type" => "timestamp"
    )
);
$service_schema["form"]["action"] = array(
    "default" => array("ID" => "keys[formnode-ID]"
	, "ID_value" => "[ID]"
    )
    , "addnew" => array("path" => "modify"
	, "action" => "insert"
	, "path" => "/restricted/modules/form/modify[PATHINFO]"
	, "params" => array()
    )
    , "edit" => array("ID" => "keys[formnode-ID]"
	, "ID_value" => "[ID]"
	, "path" => "/restricted/modules/form/modify"
	, "action" => "update"
	, "params" => array()
    )
    , "delete" => array("ID" => "keys[formnode-ID]"
	, "ID_value" => "[ID]"
	, "path" => "/restricted/modules/form/modify"
	, "action" => "delete"
	, "source_action" => "confirmdelete"
	, "component" => "FormManageModify"
	, "params" => array()
    )
    , "visible" => array("ID" => "keys[formnode-ID]"
	, "ID_value" => "[ID]"
	, "path" => "/restricted/modules/form/modify"
	, "action" => "update"
	, "component" => null
	, "params" => array("setvisible" => array("hide" => "[status]"
		, "type" => "NOT"
		, "extended_type" => "Boolean" //non gestito
	    )
	)
    )
);
$service_schema["form"]["relationship"] = array();
$service_schema["form"]["relationship"]["user"]["key"] = "ID";
$service_schema["form"]["relationship"]["user"]["rel_key"] = "owner";
$service_schema["form"]["relationship"]["user"]["multi"] = false;
$service_schema["form"]["export"]["exclude"] = array("ID_domain"
    , "ID_module"
    , "created"
    , "hide"
    , "ip_visitor"
    , "name"
    , "owner"
    , "uid"
    , "visible"
    , "ID_actual_revision"
    , "revision"
    , "revision_last_created"
    , "revision_tag"
);

$service_schema["form_revision"]["table"] = "module_form_revision";
$service_schema["form_revision"]["operations"]["GET"] = array(
    "parameters" => array(
    )
    , "errorResponses" => array()
);
$service_schema["form_revision"]["field"] = array(
    "created" => array(
	"type" => "timestamp-to-datetime"
    )
);
$service_schema["form_revision"]["relationship"] = array();
$service_schema["form_revision"]["relationship"]["user"]["key"] = "ID";
$service_schema["form_revision"]["relationship"]["user"]["rel_key"] = "owner";
$service_schema["form_revision"]["relationship"]["user"]["multi"] = false;

$service_schema["form_pricelist"]["table"] = "module_form_pricelist";
$service_schema["form_pricelist"]["operations"]["GET"] = array(
    "parameters" => array(
    )
    , "errorResponses" => array()
);
$service_schema["form_pricelist"]["field"] = array();
$service_schema["form_pricelist"]["relationship"] = array();
$service_schema["form_pricelist"]["relationship"]["form_fields"]["key"] = "ID";
$service_schema["form_pricelist"]["relationship"]["form_fields"]["rel_key"] = "ID_module";
$service_schema["form_pricelist"]["relationship"]["form"]["multi"] = false;


$service_schema["form_pricelist_detail"]["table"] = "module_form_pricelist_detail";
$service_schema["form_pricelist_detail"]["operations"]["GET"] = array(
    "parameters" => array(
    )
    , "errorResponses" => array()
);
$service_schema["form_pricelist_detail"]["field"] = array();
$service_schema["form_pricelist_detail"]["relationship"] = array();
$service_schema["form_pricelist_detail"]["relationship"]["form_pricelist"]["key"] = "ID";
$service_schema["form_pricelist_detail"]["relationship"]["form_pricelist"]["rel_key"] = "ID_form_pricelist";
$service_schema["form_pricelist_detail"]["relationship"]["form_pricelist"]["multi"] = false;
$service_schema["form_pricelist_detail"]["relationship"]["form_fields"]["key"] = "ID";
$service_schema["form_pricelist_detail"]["relationship"]["form_fields"]["rel_key"] = "ID_form_fields";
$service_schema["form_pricelist_detail"]["relationship"]["form_fields"]["multi"] = false;


$service_schema["form_fields"]["table"] = "module_form_fields";
$service_schema["form_fields"]["operations"]["GET"] = array(
    "parameters" => array(
    )
    , "errorResponses" => array()
);
$service_schema["form_fields"]["field"] = array(
);
$service_schema["form_fields"]["relationship"] = array();
$service_schema["form_fields"]["relationship"]["extended_type"]["key"] = "ID";
$service_schema["form_fields"]["relationship"]["extended_type"]["rel_key"] = "ID_extended_type";
$service_schema["form_fields"]["relationship"]["extended_type"]["multi"] = false;
$service_schema["form_fields"]["relationship"]["form_type"]["key"] = "ID";
$service_schema["form_fields"]["relationship"]["form_type"]["rel_key"] = "ID_module";
$service_schema["form_fields"]["relationship"]["form_type"]["multi"] = false;

$service_schema["form_type"]["table"] = "module_form";
$service_schema["form_type"]["operations"]["GET"] = array(
    "parameters" => array(
    )
    , "errorResponses" => array()
);
$service_schema["form_type"]["field"] = array();
$service_schema["form_type"]["relationship"] = array();

$service_schema["extended_type"]["table"] = "extended_type";
$service_schema["extended_type"]["operations"]["GET"] = array(
    "parameters" => array(
    )
    , "errorResponses" => array()
);
$service_schema["extended_type"]["field"] = array();
$service_schema["extended_type"]["relationship"] = array();

$service_schema["user"]["table"] = "cm_mod_security_users";
$service_schema["user"]["operations"]["GET"] = array(
    "parameters" => array(
    )
    , "errorResponses" => array()
);
$service_schema["user"]["field"] = array();
$service_schema["user"]["relationship"] = array();
$service_schema["user"]["relationship"]["anagraph"]["key"] = "uid";
$service_schema["user"]["relationship"]["anagraph"]["rel_key"] = "ID";
$service_schema["user"]["relationship"]["anagraph"]["multi"] = true;

$service_schema["vgallery"]["field"] = array(
    "public_cover" => array(
		"type" => "image"
		, "thumb" => "100x100"
    )
    , "name" => array(
    	"unic" => true
    )
);
$service_schema["vgallery"]["relationship"]["vgallery_type"]["key"] = "limit_type";
$service_schema["vgallery"]["relationship"]["vgallery_type"]["rel_key"] = "ID";
$service_schema["vgallery"]["relationship"]["vgallery_type"]["multi"] = false;
$service_schema["vgallery"]["sql"]["insert"][] = "INSERT INTO vgallery_nodes 
												(
													ID
													, name
													, parent
													, is_dir
													, visible
													, ID_type
													, ID_vgallery
												) VALUES (
													NULL
													, '[name]'
													, '/'
													, '1'
													, '1'
													, IFNULL((SELECT vgallery_type.ID FROM vgallery_type WHERE vgallery_type.ID IN([limit_type]) AND vgallery_type.is_dir_default = '1' LIMIT 1), 0)
													, [ID_result]
												)";

$service_schema["vgallery_type"]["field"] = array(
    "public_cover" => array(
	"type" => "image"
	, "thumb" => "100x100"
    )
);
$service_schema["vgallery_type"]["field_convert"] = array(
    "sort_default" => "vgallery_fields"
);
$service_schema["vgallery_type"]["relationship"]["vgallery_fields"]["key"] = "ID_type";
$service_schema["vgallery_type"]["relationship"]["vgallery_fields"]["rel_key"] = "ID";
$service_schema["vgallery_type"]["relationship"]["vgallery_fields"]["multi"] = true;

$service_schema["vgallery_fields"]["field"] = array();
$service_schema["vgallery_fields"]["relationship"]["vgallery_type"]["key"] = "ID_type";
$service_schema["vgallery_fields"]["relationship"]["vgallery_type"]["rel_key"] = "ID";
$service_schema["vgallery_fields"]["relationship"]["vgallery_type"]["multi"] = false;
$service_schema["vgallery_fields"]["relationship"]["extended_type"]["key"] = "ID_extended_type";
$service_schema["vgallery_fields"]["relationship"]["extended_type"]["rel_key"] = "ID";
$service_schema["vgallery_fields"]["relationship"]["extended_type"]["multi"] = false;

$service_schema["vgallery_nodes"]["category"] = array(
	"table" => "vgallery"
	, "field" => array(
		"select" => array(
			"limit_type" => "limit_type"
			, "ID" => "ID_vgallery"
		)
		, "where" => array(
			"name" => "name = [KEY]"
		)
		, "primary_rel" => "ID_vgallery"
	)	

);
$service_schema["vgallery_nodes"]["external_field"] = array(
	"primary" => array(
		"table" => "vgallery_fields"
		, "field" => array(
			"select" => array(
				"vgallery_fields.ID" => "ID"
				, "vgallery_fields.name"  => "name"
				, "extended_type.name" =>  "type"
			)
			, "join" => array(
				"extended_type" => array("ID" => "ID_extended_type")
				, "vgallery_type" => array("ID" => "ID_type")
			)
			, "where" => array(
				"limit_type" => "FIND_IN_SET(vgallery_type.ID, [VALUE])"
				, "limit_id" => "vgallery_fields.ID IN([VALUE])"
				, "limit_name" => "vgallery_fields.name IN([VALUE])"
			)
			, "order" => array(
				"order_thumb" => "desc"
			)
		)
	)
	, "storage" => array(
		"table" => "vgallery_rel_nodes_fields"
		, "field" => array(
			"select" => array(
				"description" => null
			)
			, "join" => null
			, "where" => array(
				"ID_node" => "vgallery_rel_nodes_fields.ID_nodes = vgallery_nodes.ID"
				, "ID_field" => "vgallery_rel_nodes_fields.ID_fields = [KEY]"
			)
		) 
	)
	, "field_default" => array(
	)
);
$service_schema["vgallery_nodes"]["struct_data"] = array(
    "type" => "article"
    , "microdata" => array(
	"scope" => "http://schema.org/Article"
    )
    , "microformat" => null
);
$service_schema["vgallery_nodes"]["field"] = array(
    "owner" => array(
	"struct_data" => array(
	    "type" => "person"
	    , "microdata" => array(
		"prop" => "name"
		, "scope" => "http://schema.org/Person"
	    )
	    , "microformat" => array(
		"prop" => array(
		    "class" => "fn"
		)
		, "scope" => array(
		)
	    )
	)
    )
    , "tags" => array(
	"struct_data" => array(
	    "type" => "article"
	    , "microdata" => array(
		"prop" => "keywords"
		, "scope" => "http://schema.org/Article"
	    )
	    , "microformat" => null
	)
    )
);
$service_schema["vgallery_nodes"]["field_default"] = array(
	"ID" => "ID"
	, "name" => "smart_url"
	, "permalink" => "url"
	, "created" => "created"
	, "last_update" => "last_update"
	, "meta_title" => "title"
	, "meta_description" => "description"
);
$service_schema["vgallery_nodes"]["relationship"]["vgallery_type"]["key"] = "ID_type";
$service_schema["vgallery_nodes"]["relationship"]["vgallery_type"]["rel_key"] = "ID";
$service_schema["vgallery_nodes"]["relationship"]["vgallery_type"]["multi"] = false;

$service_schema["search_tags"]["struct_data"] = array(
    "type" => "article"
    , "microdata" => array(
	"scope" => "http://schema.org/Article"
    )
    , "microformat" => null
);
$service_schema["search_tags"]["field"] = array(
    "name" => array(
	"struct_data" => array(
	    "type" => "article"
	    , "microdata" => array(
		"prop" => "keywords"
		, "scope" => "http://schema.org/Article"
	    )
	    , "microformat" => null
	)
    )
    , "permalink" => array(
	"type" => "link"
	, "struct_data" => array(
	    "type" => "article"
	    , "microdata" => array(
		"prop" => "keywords"
		, "scope" => "http://schema.org/Article"
	    )
	    , "microformat" => null
	)
    )
);
$service_schema["search_tags"]["relationship"]["search_tags_categories"]["key"] = "categories";
$service_schema["search_tags"]["relationship"]["search_tags_categories"]["rel_key"] = "ID";
$service_schema["search_tags"]["relationship"]["search_tags_categories"]["multi"] = true;