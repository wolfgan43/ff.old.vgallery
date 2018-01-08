<IfModule mod_php4.c>
    php_value engine off
</IfModule>
<IfModule mod_php5.c>
    php_value engine off
</IfModule>

Options -Indexes
Options -ExecCGI
AddHandler cgi-script .php .php3 .php4 .phtml .pl .py .jsp .asp .htm .shtml .sh .cgi