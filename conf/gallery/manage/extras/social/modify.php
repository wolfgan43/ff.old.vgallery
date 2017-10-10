<?php
require_once(FF_DISK_PATH . "/conf/index." . FF_PHP_EXT);

if (!AREA_PROPERTIES_SHOW_MODIFY) {
    ffRedirect(FF_SITE_PATH . substr($cm->path_info, 0, strpos($cm->path_info . "/", "/", 1)) . "/login?ret_url=" . urlencode($cm->oPage->getRequestUri()) . "&relogin");
}

// -------------------------
//          RECORD
// -------------------------

$oRecord = ffRecord::factory($cm->oPage);
$oRecord->id = "ExtrasSocialModify";
$oRecord->resources[] = $oRecord->id;
$oRecord->title = ffTemplate::_get_word_by_code("extras_social_modify_title");
$oRecord->addEvent("on_done_action", "ExtrasSocialModify_on_done_action");

$oRecord->src_table = "settings_thumb_social";

//$oRecord->additional_fields = array("last_update" =>  new ffData(time(), "Number"));

$oField = ffField::factory($cm->oPage);
$oField->id = "ID";
$oField->base_type = "Number";
$oRecord->addKeyField($oField);

                              
$oField = ffField::factory($cm->oPage);
$oField->id = "name";
$oField->label = ffTemplate::_get_word_by_code("extras_social_modify_name");
$oField->required = true;
$oRecord->addContent($oField);



$oRecord->addTab("Facebook");
$oRecord->setTabTitle("Facebook", "Facebook");

$oRecord->addContent(null, true, "Facebook"); 
$oRecord->groups["Facebook"] = array(
                                 "title" => "Facebook"
                                 , "cols" => 1
                                 , "tab" => "Facebook"
                              );

$oField = ffField::factory($cm->oPage);
$oField->id = "og:image";
$oField->label = "og:image";
$oField->setWidthComponent(7);
$oRecord->addContent($oField, "Facebook");

$oField = ffField::factory($cm->oPage);
$oField->id = "og:image:mode";
$oField->label = ffTemplate::_get_word_by_code("extras_social_modify_image_thumb");
$oField->setWidthComponent(5);
$oRecord->addContent($oField, "Facebook");

$oField = ffField::factory($cm->oPage);
$oField->id = "og:video";
$oField->label = "og:video";
$oField->setWidthComponent(6);
$oRecord->addContent($oField, "Facebook");

$oField = ffField::factory($cm->oPage);
$oField->id = "og:video:width";
$oField->label = "og:video:width";
$oField->base_type = "Number";
$oField->widget = "slider";
$oField->min_val = "0";
$oField->max_val = "800";
$oField->step = "1";
$oField->setWidthComponent(3);
$oRecord->addContent($oField, "Facebook");

$oField = ffField::factory($cm->oPage);
$oField->id = "og:video:height";
$oField->label = "og:video:height";
$oField->base_type = "Number";
$oField->widget = "slider";
$oField->min_val = "0";
$oField->max_val = "600";
$oField->step = "1";
$oField->setWidthComponent(3);
$oRecord->addContent($oField, "Facebook");
/* video/mp4
$oField = ffField::factory($cm->oPage);
$oField->id = "video_type";
$oField->label = ffTemplate::_get_word_by_code("extras_social_modify_video_type");
$oField->default_value = new ffData("application/x-shockwave-flash", "Text");
$oRecord->addContent($oField, "video");*/

$oField = ffField::factory($cm->oPage);
$oField->id = "og:audio";
$oField->label = "og:audio";
$oField->base_type = "Number";
$oField->extended_type = "Boolean";
$oField->control_type = "checkbox";
$oField->checked_value = new ffData("1", "Number", FF_SYSTEM_LOCALE);
$oField->unchecked_value = new ffData("0", "Number", FF_SYSTEM_LOCALE);
$oRecord->addContent($oField, "Facebook");

$oField = ffField::factory($cm->oPage);
$oField->id = "og:type";
$oField->label = "og:type";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
	array(new ffData("article")					, new ffData("article: This object represents an article on a website. It is the preferred type for blog posts and news stories.")),
	array(new ffData("books.author")			, new ffData("books.author: This object type represents a single author of a book.")),
	array(new ffData("books.book")				, new ffData("books.book: This object type represents a book or publication. This is an appropriate type for ebooks, as well as traditional paperback or hardback books")),
	array(new ffData("books.genre")				, new ffData("books.genre: This object type represents the genre of a book or publication.")),
	array(new ffData("business.business")		, new ffData("business.business: This object type represents a place of business that has a location, operating hours and contact information.")),
	array(new ffData("fitness.course")			, new ffData("fitness.course: This object type represents the user's activity contributing to a particular run, walk, or bike course.")),
	array(new ffData("game.achievement")		, new ffData("game.achievement: This object type represents a specific achievement in a game. An app must be in the 'Games' category in App Dashboard to be able to use this object type. Every achievement has a `game:points` value associate with it. This is not related to the points the user has scored in the game, but is a way for the app to indicate the relative importance and scarcity of different achievements: * Each game gets a total of 1,000 points to distribute across its achievements * Each game gets a maximum of 1,000 achievements * Achievements which are scarcer and have higher point values will receive more distribution in Facebook's social channels. For example, achievements which have point values of less than 10 will get almost no distribution. Apps should aim for between 50-100 achievements consisting of a mix of 50 (difficult), 25 (medium), and 10 (easy) point value achievements Read more on how to use achievements in [this guide](/docs/howtos/achievements/).")),
	array(new ffData("music.album")				, new ffData("music.album: This object type represents a music album; in other words, an ordered collection of songs from an artist or a collection of artists. An album can comprise multiple discs.")),
	array(new ffData("music.playlist")			, new ffData("music.playlist: This object type represents a music playlist, an ordered collection of songs from a collection of artists.")),
	array(new ffData("music.radio_station")		, new ffData("music.radio_station: This object type represents a 'radio' station of a stream of audio. The audio properties should be used to identify the location of the stream itself.")),
	array(new ffData("music.song")				, new ffData("music.song: This object type represents a single song.")),
	array(new ffData("place")					, new ffData("place: This object type represents a place - such as a venue, a business, a landmark, or any other location which can be identified by longitude and latitude.")),
	array(new ffData("product")					, new ffData("product: This object type represents a product. This includes both virtual and physical products, but it typically represents items that are available in an online store.")),
	array(new ffData("product.group")			, new ffData("product.group: This object type represents a group of product items.")),
	array(new ffData("product.item")			, new ffData("product.item: This object type represents a product item.")),
	array(new ffData("profile")					, new ffData("profile: This object type represents a person. While appropriate for celebrities, artists, or musicians, this object type can be used for the profile of any individual. The `fb:profile_id` field associates the object with a Facebook user.")),
	array(new ffData("restaurant.menu")			, new ffData("restaurant.menu: This object type represents a restaurant's menu. A restaurant can have multiple menus, and each menu has multiple sections.")),
	array(new ffData("restaurant.menu_item")	, new ffData("restaurant.menu_item: This object type represents a single item on a restaurant's menu. Every item belongs within a menu section.")),
	array(new ffData("restaurant.menu_section")	, new ffData("restaurant.menu_section: This object type represents a section in a restaurant's menu. A section contains multiple menu items.")),
	array(new ffData("restaurant.restaurant")	, new ffData("restaurant.restaurant: This object type represents a restaurant at a specific location.")),
	array(new ffData("video.episode")			, new ffData("video.episode: This object type represents an episode of a TV show and contains references to the actors and other professionals involved in its production. An episode is defined by us as a full-length episode that is part of a series. This type must reference the series this it is part of.")),
	array(new ffData("video.movie")				, new ffData("video.movie: This object type represents a movie, and contains references to the actors and other professionals involved in its production. A movie is defined by us as a full-length feature or short film. Do not use this type to represent movie trailers, movie clips, user-generated video content, etc.")),
	array(new ffData("video.other")				, new ffData("video.other: This object type represents a generic video, and contains references to the actors and other professionals involved in its production. For specific types of video content, use the `video.movie` or `video.tv_show` object types. This type is for any other type of video content not represented elsewhere (eg. trailers, music videos, clips, news segments etc.)")),
	array(new ffData("video.tv_show")			, new ffData("video.tv_show: This object type represents a TV show, and contains references to the actors and other professionals involved in its production. For individual episodes of a series, use the `video.episode` object type. A TV show is defined by us as a series or set of episodes that are produced under the same title (eg. a television or online series)"))
);
$oField->multi_select_one_label = "website: Standard Website";
$oRecord->addContent($oField, "Facebook");
//da inserire tutti gli og per tipo di appartenenza


$oField = ffField::factory($cm->oPage);
$oField->id = "fb:admins";
$oField->label = "fb:admins";
$oRecord->addContent($oField, "Facebook");

$oField = ffField::factory($cm->oPage);
$oField->id = "fb:app_id";
$oField->label = "fb:app_id";
$oRecord->addContent($oField, "Facebook");

$oField = ffField::factory($cm->oPage);
$oField->id = "fb:profile_id";
$oField->label = "fb:profile_id";
$oRecord->addContent($oField, "Facebook");

/* audio/vnd.facebook.bridge
$oField = ffField::factory($cm->oPage);
$oField->id = "audio_type";
$oField->label = ffTemplate::_get_word_by_code("extras_social_modify_audio_type");
$oField->default_value = new ffData("application/mp3", "Text");
$oRecord->addContent($oField, "audio");
*/


$oRecord->addTab("Twitter");
$oRecord->setTabTitle("Twitter", "Twitter");

$oRecord->addContent(null, true, "Twitter"); 
$oRecord->groups["Twitter"] = array(
                                 "title" => "Twitter"
                                 , "cols" => 1
                                 , "tab" => "Twitter"
                              );
                              
$oField = ffField::factory($cm->oPage);
$oField->id = "twitter:image";
$oField->label = "twitter:image";
$oField->setWidthComponent(7);
$oRecord->addContent($oField, "Twitter");

$oField = ffField::factory($cm->oPage);
$oField->id = "twitter:image:mode";
$oField->label = ffTemplate::_get_word_by_code("extras_social_modify_image_thumb");
$oField->setWidthComponent(5);
$oRecord->addContent($oField, "Twitter");

$oField = ffField::factory($cm->oPage);
$oField->id = "twitter:card";
$oField->label = "twitter:card";
$oField->extended_type = "Selection";
$oField->multi_pairs = array (
	array(new ffData("summary")					, new ffData("Summary Card: Title, description, thumbnail, and Twitter account attribution.")),	
	array(new ffData("summary_large_image")		, new ffData("Summary Card with Large Image: Similar to a Summary Card, but with a prominently featured image.")),	
	array(new ffData("app")						, new ffData("App Card: A Card to detail a mobile app with direct download.")),	
	array(new ffData("player")					, new ffData("Player Card: A Card to provide video/audio/media."))
);
$oRecord->addContent($oField, "Twitter");

$oField = ffField::factory($cm->oPage);
$oField->id = "twitter:site";
$oField->label = "twitter:site";
$oRecord->addContent($oField, "Twitter");

$oField = ffField::factory($cm->oPage);
$oField->id = "twitter:creator";
$oField->label = "twitter:creator";
$oRecord->addContent($oField, "Twitter");

$cm->oPage->addContent($oRecord);



function ExtrasSocialModify_on_done_action($component, $action) {
    $db = ffDB_Sql::factory();
    
    if(strlen($action)) {
        $sSQL = "UPDATE 
                    `layout` 
                SET 
                    `layout`.`last_update` = (SELECT `settings_thumb_social`.last_update FROM settings_thumb_social WHERE settings_thumb_social.ID = " . $db->toSql($component->key_fields["ID"]->value) . ") 
                WHERE 
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("VIRTUAL_GALLERY") . ")
                    )
                    OR
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("GALLERY") . ")
                    )
                    OR
                    (
                        layout.ID_type = ( SELECT ID FROM layout_type WHERE  layout_type.name = " . $db->toSql("PUBLISHING") . ")
                    )
                    ";
        $db->execute($sSQL);
        
        if (FF_ENABLE_MEM_SHOWFILES_CACHING) {
			ffCache::getInstance(CM_CACHE_ADAPTER)->set("__vgallery_settings_thumb__");
		}
    }
}
?>
