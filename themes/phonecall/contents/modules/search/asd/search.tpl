<div id="{component}hidden">
  <input type="hidden" id="{component}detailaction" name="{component}detailaction" value="" />
  <!--BeginSectHiddenField-->
  <input type="hidden" id="{component}{id}" name="{component}{id}" value="{value}" />
  <!--EndSectHiddenField-->
</div>
 
<div id="{component}data" class="record">
<h2>{title}</h2>
{fixed_pre_content}
<!--BeginSectError-->
<div class="error">{strError}</div>
<!--EndSectError-->

<div id="{component_id}_discl_sect">
<!--BeginSectForm-->
<fieldset class="default">
  <!--BeginSectFormRow-->
    <div class="{container_class}">
    <!--BeginSectLabel--><label {label_properties} >{label}<!--BeginSectRequiredSymbol-->&nbsp; <span class="required">*</span><!--EndSectRequiredSymbol--><span>{description}</span></label><!--EndSectLabel-->
	{control}
    </div>
  <!--EndSectFormRow-->
</fieldset>
<!--EndSectForm-->

<!--BeginSectGroup-->
<fieldset class="{group_class}">
<legend>{GroupTitle}</legend>
	<!--BeginSectGroupTitle-->
	<!--EndSectGroupTitle-->
	  <!--BeginSectGroupRow-->
	  <div class="{container_class}">
		<!--BeginSectGroupCol-->
		<!--BeginSectLabel--><label {label_properties}>{label}<!--BeginSectRequiredSymbol-->&nbsp; <span class="required">*</span><!--EndSectRequiredSymbol--></label><!--EndSectLabel-->
		{control}
		<!--EndSectGroupCol-->
	  </div>
	  <!--EndSectGroupRow-->
</fieldset>
<!--EndSectGroup-->

<!--BeginSectRequiredNote-->
{_require_note}
<!--EndSectRequiredNote-->
</div>

{fixed_post_content}
<!--BeginSectDetails-->
{Details}
<!--EndSectDetails-->
<!--BeginSectControls-->
<div class="actions">{ActionButtons}</div>
<!--EndSectControls-->
</div>