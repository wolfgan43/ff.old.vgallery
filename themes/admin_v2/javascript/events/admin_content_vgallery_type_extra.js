/*jQuery(document).on("change", ".shortcut", function(){
	var array_info = jQuery(this).attr("id").replace(/\]/g, '').split("[");
	var name = array_info[0].split("_");
	var params = {
		"name" : array_info[2]
		, "value" : jQuery(this).val()
		, "type_id" : ""
		, "field_id" : jQuery("#" + jQuery.fn.escape(name[0] + "_displayed_keys[" + array_info[1] + "][ID]")).val()
	};
	updateDb(params);
});

jQuery(document).on("change", ".shortcut-checkbox", function(){
	if(jQuery(this).is(":checked"))
		value = 1;
	else
		value = 0;
	
	var array_info = jQuery(this).attr("id").replace(/\]/g, '').split("[");
	var name = array_info[0].split("_");
	var params = {
		"name" : array_info[2]
		, "value" : value
		, "type_id" : ""
		, "field_id" : jQuery("#" + jQuery.fn.escape(name[0] + "_displayed_keys[" + array_info[1] + "][ID]")).val()
	};
	updateDb(params);
});

function updateDb(params)
{
	params["type_id"] = jQuery("#" + jQuery.fn.escape("VGalleryTypeModify_keys[ID]")).val();
	 
	ff.ajax.doRequest({'action': 'updateRecord', fields: [], 'url' : '/admin/content/vgallery/type/modify?keys[ID]=' + params["type_id"] + '&keys[ID_field]=' + params["field_id"] + '&value=' + params["value"] + '&field-name=' + params["name"]});
	
}*/

jQuery(function() {
	
/*
			if(jQuery("td:first", this).hasClass("order")) {
				jQuery("td:first", this).text(fieldOrder);
			} else {
				jQuery(this).prepend('<td class="order hidden">' + fieldOrder + '</td>');
			}
*/

		
});