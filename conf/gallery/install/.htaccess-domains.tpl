RewriteEngine on

AddType text/cache-manifest .manifest
AddDefaultCharset UTF-8

##################
# LEGEND
#-----------------
#
# [DOMAIN_PROTOCOL]
# [DOMAIN_SUB]
# [DOMAIN_NAME]
# [DOMAIN_EXT]
# [PROJECT_NAME]
# [FF_SITE_PATH] => subpath starting from document_root and end at the folder starting project
# [FF_TOP_DIR] => folder starting project
#

#############
# ENV
#------------
SetEnvIf Host "[DOMAIN_SUB].[DOMAIN_NAME].[DOMAIN_EXT]" "FF_PROJECT_DIR=[FF_TOP_DIR]/domains/[PROJECT_NAME]"

#Symbolic Link
#SetEnvIf Host "[DOMAIN_SUB].[DOMAIN_NAME].[DOMAIN_EXT]" "FF_TOP_DIR=[FF_TOP_DIR]"



#############
# Redirect non https or non-www to https://www
#------------
RewriteCond %{HTTPS}                        off [OR]
RewriteCond %{HTTP_HOST}                    ^[DOMAIN_NAME]\.[DOMAIN_EXT]$
RewriteRule (.*)                            https://www.[DOMAIN_NAME].[DOMAIN_EXT]%{REQUEST_URI} [L,R=301]

#############
# Redirect non https to https (sub domains)
#------------
RewriteCond %{HTTPS}                        off
RewriteRule (.*)                            https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]


#############
# Services
#------------

#getlibs
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/services/getlibs(.*)
RewriteRule  ^(.*)                          [FF_SITE_PATH]/services/getlibs%1 [L,QSA]

#uploader
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/themes/library/plugins/jquery\.uploadifive/uploadifive\.php
RewriteRule  ^(.*)                          [FF_SITE_PATH]/themes/library/plugins/jquery\.uploadifive/uploadifive\.php [L,QSA]


#############
# Error
#------------
ErrorDocument 404                           [FF_SITE_PATH]/cm/error.php
ErrorDocument 403                           [FF_SITE_PATH]/cm/error.php


#############
# Asset
#------------

#css | js
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}  	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/asset
RewriteRule   ^asset/(.*)                   [FF_SITE_PATH]/domains/[PROJECT_NAME]/cache/$1 [L]

#media
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}  	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/media
RewriteRule   ^media/(.*)                   [FF_SITE_PATH]/domains/[PROJECT_NAME]/cache/.thumbs/$1 [L]

#static
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/static
RewriteRule   ^static(.*)                   [FF_SITE_PATH]/cm/static\.php?_ffq_=$1 [L,QSA]

#showfiles
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/cm/showfiles\.php
RewriteRule  ^cm/showfiles.php(.*)          [FF_SITE_PATH]/cm/showfiles\.php?_ffq_=$1 [L,QSA]

#############
# Using Sub Domains for Render Images
#------------

#media with SubDomains
RewriteCond %{HTTP_HOST}                    ^media\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond %{REQUEST_URI}  	            ![FF_SITE_PATH]/domains/[PROJECT_NAME]/cache/.thumbs
RewriteCond %{REQUEST_URI}  	            ![FF_SITE_PATH]/cm/error\.php
RewriteRule ^(.*)                           [FF_SITE_PATH]/domains/[PROJECT_NAME]/cache/.thumbs/$0 [L,QSA]

#media:404 goto static
#RewriteCond %{HTTP_HOST}                   ^media\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
#RewriteCond %{REQUEST_FILENAME}            ^!-f
#RewriteRule ^cache/.thumbs/(.*)            [DOMAIN_PROTOCOL]://static.[DOMAIN_NAME].[DOMAIN_EXT]/$1 [L,R=302,E=nocache:1]

#static with SubDomains
RewriteCond %{HTTP_HOST}                    ^static\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond %{REQUEST_URI}  	            ![FF_SITE_PATH]/cm/static\.php
RewriteRule ^(.*)                           [FF_SITE_PATH]/cm/static\.php?_ffq_=%1 [L,QSA]


#############
# Modules
#------------

RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/modules/([^/]+)/themes(.+)
RewriteRule  ^modules/([^/]+)/themes(.+)    [FF_SITE_PATH]/modules/$1/themes$2 [L,QSA]

#da verificare
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
RewriteCond   %{REQUEST_URI}	            ^[FF_SITE_PATH]/domains/[PROJECT_NAME]/modules
RewriteRule  ^modules/([^/]+)(.+)           [FF_SITE_PATH]/modules/$1/themes$2 [L,QSA]

#############
# Basic rules
#------------
RewriteCond   %{HTTP_HOST}  	            [DOMAIN_SUB]\.[DOMAIN_NAME]\.[DOMAIN_EXT]$ [NC]
#core
RewriteCond   %{REQUEST_URI}  	            ![FF_SITE_PATH]/domains/[PROJECT_NAME]/applets/([^/]+)/themes(.+)
RewriteCond   %{REQUEST_URI}  	            ![FF_SITE_PATH]/domains/[PROJECT_NAME]/modules/([^/]+)/themes(.+)

#install
RewriteCond   %{REQUEST_URI}  	            ![FF_SITE_PATH]/install

#static
RewriteCond   %{REQUEST_URI}                ![FF_SITE_PATH]/domains/[PROJECT_NAME]/cache
RewriteCond   %{REQUEST_URI}  	            ![FF_SITE_PATH]/domains/[PROJECT_NAME]/themes
RewriteCond   %{REQUEST_URI}                ![FF_SITE_PATH]/domains/[PROJECT_NAME]/uploads

#libs
RewriteCond   %{REQUEST_URI}  	            ![FF_SITE_PATH]/vendor

#root files
RewriteCond   %{REQUEST_URI}  	            ![FF_SITE_PATH]/domains/[PROJECT_NAME]/robots\.txt
RewriteCond   %{REQUEST_URI}  	            ![FF_SITE_PATH]/domains/[PROJECT_NAME]/favicon
RewriteRule   ^(.*)                         [FF_SITE_PATH]/cm/main\.php?_ffq_=/$0 [L,QSA]

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
    AddOutputFilterByType DEFLATE application/javascript text/javascript
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