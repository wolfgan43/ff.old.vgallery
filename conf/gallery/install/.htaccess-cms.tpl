#enable rewrite rule
 RewriteEngine on
# Apache mimetype configuration
 AddType text/cache-manifest .manifest
 AddDefaultCharset UTF-8

#error
 ErrorDocument 404 [FF_SITE_PATH]/error-document
 ErrorDocument 403 [FF_SITE_PATH]/error-document

#redirect http non-www to https://www
 RewriteCond %{HTTPS} off
 RewriteCond %{HTTP_HOST} ^!(www\.)?[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 RewriteRule (.*) [DOMAIN_PROTOCOL]://www.[DOMAIN_NAME].[DOMAIN_EXT]/$1 [R=301,L,QSA]

#redirect http non-www to https://www
 RewriteCond %{HTTPS} on
 RewriteCond %{HTTP_HOST} ^[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 RewriteRule (.*) [DOMAIN_PROTOCOL]://www.[DOMAIN_NAME].[DOMAIN_EXT]/$1 [R=301,L,QSA]

#media
 RewriteCond %{HTTP_HOST}           ^media\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/cache/_thumb
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/error-document
 RewriteRule ^(.*)                  [FF_SITE_PATH]/cache/_thumb/$0 [L,QSA]

# if file notfound goto static
 RewriteCond %{HTTP_HOST}           ^media\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 RewriteCond %{REQUEST_FILENAME}    ^!-f
 RewriteRule ^cache/_thumb/(.*)     [DOMAIN_PROTOCOL]://static.[DOMAIN_NAME].[DOMAIN_EXT]/$1 [L,R=302,E=nocache:1]

#cm showfiles in static
 RewriteCond %{HTTP_HOST}           ^static\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/cm/showfiles\.php
 RewriteRule ^(.*)                  [FF_SITE_PATH]/cm/showfiles\.php/$0 [L,QSA]

#media: if not subdomains
#RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [OR,NC]
#RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.local$ [NC]
#RewriteCond %{REQUEST_URI}  	    ^[FF_SITE_PATH]/media
#RewriteRule ^media/(.*)            [FF_SITE_PATH]/cache/_thumb/$1 [L]

#assets
 RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [OR,NC]
 RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.local$ [NC]
 RewriteCond %{REQUEST_URI}  	    ^[FF_SITE_PATH]/asset
 RewriteRule ^asset/(.*)            [FF_SITE_PATH]/cache/$1 [L]

#modules
 RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [OR,NC]
 RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.local$ [NC]
 RewriteCond %{REQUEST_URI}	        ^[FF_SITE_PATH]/modules
 RewriteCond %{REQUEST_URI}	        !^[FF_SITE_PATH]/modules/([^/]+)/themes(.+)
 RewriteRule ^modules/([^/]+)(.+)   [FF_SITE_PATH]/modules/$1/themes$2 [L,QSA]

#primary page
 RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [OR,NC]
 RewriteCond %{HTTP_HOST}           ^[DOMAIN_SUB]\.[DOMAIN_NAME]\.local$ [NC]
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/index\.php
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/cm/main\.php
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/cm/showfiles\.php
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/themes
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/applets/.*/?themes
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/modules/.*/?themes
 RewriteCond %{REQUEST_URI}         !^[FF_SITE_PATH]/uploads
 RewriteCond %{REQUEST_URI}         !^[FF_SITE_PATH]/cache
 RewriteCond %{REQUEST_URI}         !^[FF_SITE_PATH]/asset
 RewriteCond %{REQUEST_URI}         !^[FF_SITE_PATH]/media
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/robots\.txt
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/favicon
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/conf/gallery/install
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/conf/gallery/updater
 RewriteCond %{REQUEST_URI}  	    !^[FF_SITE_PATH]/router\.php
 RewriteRule ^(.*)                  [FF_SITE_PATH]/index\.php/$0 [L,QSA]

#router
 RewriteCond %{HTTP_HOST}           !^[DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
 RewriteCond   %{REQUEST_URI}       !^[FF_SITE_PATH]/?router\.php
 RewriteRule   ^(.*)                [FF_SITE_PATH]/router\.php/$0 [L,QSA]

# RULES
<IfModule mod_deflate.c>
# force deflate for mangled headers
# developer.yahoo.com/blogs/ydn/posts/2010/12/pushing-beyond-gzipping/
  <IfModule mod_setenvif.c>
    <IfModule mod_headers.c>
      SetEnvIf Authorization .+ Authorization=$0
      SetEnvIfNoCase ^(Accept-EncodXng|X-cept-Encoding|X{15}|~{15}|-{15})$ ^((gzip|deflate)\s*,?\s*)+|[X~-]{4,13}$ HAVE_Accept-Encoding
      RequestHeader append Accept-Encoding "gzip,deflate" env=HAVE_Accept-Encoding
    </IfModule>
  </IfModule>
  # Legacy versions of Apache
  AddOutputFilterByType DEFLATE text/html text/plain text/css application/json
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE text/xml application/xml text/x-component
  AddOutputFilterByType DEFLATE application/xhtml+xml application/rss+xml application/atom+xml
  AddOutputFilterByType DEFLATE application/vnd.ms-fontobject application/x-font-ttf font/opentype
  AddOutputFilterByType DEFLATE image/svg+xml image/png image/jpeg image/gif image/x-icon
</IfModule>
<IfModule mod_expires.c>
  ExpiresActive on

# Perhaps better to whitelist expires rules? Perhaps.
  ExpiresDefault                          "access plus 1 month"

# cache.appcache needs re-requests in FF 3.6 (thx Remy ~Introducing HTML5)
  ExpiresByType text/cache-manifest       "access plus 0 seconds"

# Your document html
  ExpiresByType text/html                 "access plus 1 week"

# Data
  ExpiresByType text/xml                  "access plus 0 seconds"
  ExpiresByType application/xml           "access plus 0 seconds"
  ExpiresByType application/json          "access plus 0 seconds"

# RSS feed
  ExpiresByType application/rss+xml       "access plus 1 hour"

# Favicon (cannot be renamed)
  ExpiresByType image/x-icon              "access plus 1 month"

# Media: images, video, audio
  ExpiresByType image/gif                 "access plus 1 month"
  ExpiresByType image/png                 "access plus 1 month"
  ExpiresByType image/jpg                 "access plus 1 month"
  ExpiresByType image/jpeg                "access plus 1 month"
  ExpiresByType video/ogg                 "access plus 1 month"
  ExpiresByType audio/ogg                 "access plus 1 month"
  ExpiresByType video/mp4                 "access plus 1 month"
  ExpiresByType video/webm                "access plus 1 month"

# HTC files  (css3pie)
  ExpiresByType text/x-component          "access plus 1 month"

# Webfonts
  ExpiresByType font/truetype             "access plus 1 month"
  ExpiresByType font/opentype             "access plus 1 month"
  ExpiresByType application/x-font-woff   "access plus 1 month"
  ExpiresByType image/svg+xml             "access plus 1 month"
  ExpiresByType application/vnd.ms-fontobject "access plus 1 month"

# CSS and JavaScript
  ExpiresByType text/css                  "access plus 1 year"
  ExpiresByType application/javascript    "access plus 1 year"
  ExpiresByType text/javascript           "access plus 1 year"
</IfModule>
<IfModule mod_headers.c>
  <FilesMatch "\.(js|css|xml|gz|svg)$">
    Header set Cache-Control: public
  </FilesMatch>
	# FileETag None is not enough for every server.
	#  Header unset ETag
	Header always append X-Frame-Options SAMEORIGIN
  <FilesMatch "\.(html|js|css|xml|gz|svg)$">
    Header append Vary: Accept-Encoding
  </FilesMatch>
</IfModule>

# Since we`re sending far-future expires, we dont need ETags for static content.
# developer.yahoo.com/performance/rules.html#etags
FileETag None