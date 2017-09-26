function onlyOneAnswer (field)
{
	if(jQuery(field).prop('checked'))
	{
		jQuery(".checkbox").each(function(field) 
		{
			if(jQuery(this) != field)
			{
				jQuery(this).prop('unchecked');
			}
		});
	};
};