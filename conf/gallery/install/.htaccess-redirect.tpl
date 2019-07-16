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