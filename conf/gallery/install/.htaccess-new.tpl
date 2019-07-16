RewriteEngine on
Options +FollowSymLinks

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
# [FF_SITE_PATH]
#


#############
# Redirect non https or non-www to https://www
#------------
RewriteCond %{HTTPS}                        off [OR]
RewriteCond %{HTTP_HOST}                    ^paginemediche\.info$
RewriteRule (.*)                            https://www.paginemediche.info%{REQUEST_URI} [L,R=301]

#############
# Redirect non https to https (sub domains)
#------------
RewriteCond %{HTTPS}                        off
RewriteRule (.*)                            https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

#############
# Error
#------------
ErrorDocument 404                           /cm/error.php
ErrorDocument 403                           /cm/error.php


#############
# Asset
#------------

#css | js
RewriteCond   %{HTTP_HOST}  	            www\.paginemediche\.info$ [NC,OR]
RewriteCond   %{HTTP_HOST}  	            pro\.paginemediche\.info$ [NC]
RewriteCond   %{REQUEST_URI}  	            ^/asset
RewriteRule   ^asset/(.*)                   /cache/$1 [L]

#media
RewriteCond   %{HTTP_HOST}  	            www\.paginemediche\.info$ [NC,OR]
RewriteCond   %{HTTP_HOST}  	            pro\.paginemediche\.info$ [NC]
RewriteCond   %{REQUEST_URI}  	            ^/media
RewriteRule   ^media/(.*)                   /cache/.thumbs/$1 [L]

#static
RewriteCond   %{HTTP_HOST}  	            www\.paginemediche\.info$ [NC,OR]
RewriteCond   %{HTTP_HOST}  	            pro\.paginemediche\.info$ [NC]
RewriteCond   %{REQUEST_URI}	            ^/static
RewriteRule   ^static(.*)                  /cm/static\.php?_ffq_=$1 [L,QSA]


#############
# Using Sub Domains for Render Images
#------------

#media with SubDomains
RewriteCond %{HTTP_HOST}                    ^media\.paginemediche\.info$ [NC]
RewriteCond %{REQUEST_URI}  	            !/cache/.thumbs
RewriteCond %{REQUEST_URI}  	            !/cm/error\.php
RewriteRule ^(.*)                           /cache/.thumbs/$0 [L,QSA]

#media:404 goto static
#RewriteCond %{HTTP_HOST}                   ^media\.paginemediche\.info$ [NC]
#RewriteCond %{REQUEST_FILENAME}            ^!-f
#RewriteRule ^cache/.thumbs/(.*)            https://static.paginemediche.info/$1 [L,R=302,E=nocache:1]

#static with SubDomains
RewriteCond %{HTTP_HOST}                    ^static\.paginemediche\.info$ [NC]
RewriteCond %{REQUEST_URI}  	            !/cm/static\.php
RewriteRule ^(.*)                           /cm/static\.php?_ffq_=%1 [L,QSA]


####################
# Static Cache
#---------------------
RewriteCond %{REQUEST_URI}  	            !^/cache
RewriteCond %{REQUEST_FILENAME}             !-d
RewriteCond %{REQUEST_FILENAME}             !-f
RewriteCond %{REQUEST_FILENAME}             !-l
RewriteCond %{QUERY_STRING}                 ^$ [OR]
RewriteCond %{QUERY_STRING}                 ^page=([0-9]*)$
RewriteRule ^(.*)                           /cache/http/%{HTTP_HOST}/$0/index%1.html [L,QSA]

#Static Cache By Search
RewriteCond %{REQUEST_URI}  	            !^/cache
RewriteCond %{REQUEST_FILENAME}             !-d
RewriteCond %{REQUEST_FILENAME}             !-f
RewriteCond %{REQUEST_FILENAME}             !-l
RewriteCond %{QUERY_STRING}                 q=([a-zA-Z0-9-]+) [NC,OR]
RewriteCond %{QUERY_STRING}                 ^.*[&]?q=([a-zA-Z0-9-]+).*&page=([0-9]*).*$ [NC]
RewriteRule ^(.*)                           /cache/http/%{HTTP_HOST}/$0/search-%1%2.html [L,QSA]

#Static Cache By Filter
RewriteCond %{REQUEST_URI}  	            !^/cache
RewriteCond %{REQUEST_FILENAME}             !-d
RewriteCond %{REQUEST_FILENAME}             !-f
RewriteCond %{REQUEST_FILENAME}             !-l
RewriteCond %{QUERY_STRING}                 ffl=([a-zA-Z0-9-]+) [NC,OR]
RewriteCond %{QUERY_STRING}                 ^.*[&]?ffl=([a-zA-Z0-9-]+).*&page=([0-9]*).*$ [NC]
RewriteRule ^(.*)                           /cache/http/%{HTTP_HOST}/$0/filter-%1%2.html [L,QSA]


####################
# Rewrite Base
#---------------------
RewriteCond %{REQUEST_FILENAME}             !-d
RewriteCond %{REQUEST_FILENAME}             !-f
RewriteCond %{REQUEST_FILENAME}             !-l
RewriteRule ^(.*)                           /index.php [L,QSA]





#############
# CMS Router
#------------
# RewriteCond %{HTTP_HOST}                  ^[DOMAIN_ALIAS1]$ [NC, OR]
# RewriteCond %{HTTP_HOST}                  ^[DOMAIN_ALIAS2]$ [NC, OR]
# RewriteCond %{HTTP_HOST}                  ^[DOMAIN_ALIAS3]$ [NC]
# RewriteCond %{REQUEST_URI}                !/router\.php
# RewriteRule ^(.*)                         /router\.php/$0 [L,QSA]

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