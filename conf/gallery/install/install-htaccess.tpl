RewriteEngine on

AddDefaultCharset UTF-8

RewriteCond   %{REQUEST_URI}  	!^[FF_SITE_PATH]/conf/gallery/install
RewriteCond   %{REQUEST_URI}  	!^[FF_SITE_PATH]/conf/gallery/updater
RewriteRule   ^(.*)             [FF_SITE_PATH]/conf/gallery/install/index\.php/$0 [L,QSA]