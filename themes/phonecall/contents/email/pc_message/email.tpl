<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">


<style type="text/css">
body {
padding:0;
font-family:Verdana, arial, sans-serif;
border:0;
background:#E5E5E5;
}
a {
color:#3c9607;
font-weight:bold;
text-decoration: underline;
}
a:hover {
text-decoration: none;
color:#FFDAAE;
}
.group {
text-align:left;
padding: 0 30px 10px 10px;
}
p {
padding:5px;
}
H4 {
font-size:12px;
line-height:13px;
}
</style>

</head>
<body bgcolor="#E5E5E5" leftmargin="0" topmargin="0" marginwidth="0" style="font-family:arial, helvetica, sans-serif;" marginheight="0" align="center">
<table bgcolor="#E5E5E5" width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
	<tr>
		<td width="100%" >

<table id="Table_01" width="600" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:5px auto; border-collapse: collapse;">
	<tr>
		<td>


<!--BeginSezHeaders-->
	<div class="explanation">{_email_template_explanation}</div>
	<div class="mail-header">
	<!--BeginSezHeadersGroups-->
	<div class="{real_name}">
	<h3>{group_name}</h3>
	<!--BeginSezHeader-->
	<div class="{real_name}">
	<label>{headers_label}</label>
	<span><strong>{headers_value}</strong></span>
	</div>
	<!--EndSezHeader-->
	</div>
	<!--EndSezHeadersGroups-->
	</div>
<!--EndSezHeaders-->
<div class="mail-body" style="background:#FFF; padding:4px 4px 25px; border:1px solid #DDDDDD; -webkit-box-shadow: 0px 5px 12px -8px #555; -moz-box-shadow: 0px 5px 12px -8px #555555; box-shadow: 0px 5px 12px -8px #555; ">
	<div style="text-align:center; border-bottom:1px solid #f7f7f7; margin:15px; padding-bottom:10px;">
<a href="http://{domain_inset}{site_path}/restricted/phonecall"><img src="http://{domain_inset}{site_path}/themes/{theme}/images/logo-mail.jpg" /></a>
	</div>
{pre_body}
<!--BeginSezOwner-->
<div class="owner">
{owner}
</div>
<!--EndSezOwner-->
<!--BeginSezFields-->
<!--BeginSezGroups-->
<div class="{real_name} group" style="padding:0 15px;">
<h3 style="color:#3c9607;">{group_name}</h3>
<!--BeginSezStyle-->
<!--BeginSezField-->
<div class="{real_name}" style="line-height:18px;">
<label><strong>{fields_label}</strong></label>
<span>{fields_value}</span>
</div>
<!--EndSezField-->
<!--EndSezStyle-->
<!--BeginSezStyleTable-->
<table>
<thead>
<tr>
<!--BeginSezTableFieldLabel-->
<th class="{real_name}">{fields_label}</th>
<!--EndSezTableFieldLabel-->
</tr>
</thead>
<tbody>
<!--BeginSezTableRow-->
<tr>
<!--BeginSezTableField-->
<td class="{real_name}">{fields_value}</td>
<!--EndSezTableField-->
</tr>
<!--EndSezTableRow-->
</tbody>
</table>
<!--EndSezStyleTable-->
</div>
<!--EndSezGroups-->
<!--EndSezFields-->
{post_body}
<a href="http://{domain_inset}{site_path}/restricted/phonecall" style="display:block; margin:10px 200px; padding:4px; text-align:center; font-size:14px; color: white; border: 1px solid #237CB8; background-color: #0588BB; text-decoration:none;"> collegati a CloudCalls</a>
</div>  
			</td>
		</tr>
	</table>
<p style="text-align:center; font-size:11px;"><strong>{_mail_footer_site_owner}</strong> <a href="{_mail_footer_site_address}">{_mail_footer_site_copyright}</a></p>
	</td>
	</tr>
</table>
</body>
</html>

