<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<style type="text/css">
body {
	padding:0;
	font-family:Verdana, arial, sans-serif;
	border:0;
	margin:10%;
	background:#DDD;
}
a {
	color:#333;
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
<body>
<div class="mail-body" style="background:#FFF; padding:5px; border:4px solid #fff">
	<img src="cid:logo-mail.png" width="200" height="200" />
		{pre_body}
		<!--BeginSezOwner-->
			<div class="owner">
				<!--BeginSezOwnerLabel-->
				<label>{owner_label}</label>
				<!--EndSezOwnerLabel-->
				<span>{owner}</span>
			</div>
		<!--EndSezOwner-->
	  	<!--BeginSezFields-->
            <!--BeginSezGroups-->
            <div class="{real_name} group">
                <h3>{group_name}</h3>
                <!--BeginSezStyle-->
                    <!--BeginSezField-->
                        <div class="{real_name}">
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
	<hr />
    	<p style="text-align:center; font-style:italic;">{_mail_thanks_message}</p>
	<hr />
	<p style="font-size:10px; color:#666">
		<em style="font-weight:bold;">{_mail_footer_privacy_title}</em><br />
    	{_mail_footer_privacy_text}
	</p>
	<p style="text-align:center;"><strong>{_mail_footer_site_owner}</strong> <a href="{_mail_footer_site_address}">{_mail_footer_site_copyright}</a></p>
</div>  
</body>
</html>