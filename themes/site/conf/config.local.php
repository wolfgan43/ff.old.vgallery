<?php
/**
 * Path
 */
define("FF_DISK_PATH", "/var/www/vhosts/blueocarina.net/subdomains/dev/httpdocs");
define("FF_SITE_PATH", "");
define("SITE_UPDIR", "/uploads");
define("DISK_UPDIR", FF_DISK_PATH . FF_SITE_PATH);

/**
 * Session
 */
define("SESSION_SAVE_PATH", (getenv("SESSION_SAVE_PATH")
    ? getenv("SESSION_SAVE_PATH")
    : "/tmp"
));
define("SESSION_NAME", 'PHPSESS_abq42619');
define("MOD_SECURITY_SESSION_PERMANENT", true);

/**
 * Database
 */
define("DB_CHARACTER_SET", 'utf8');
define("DB_COLLATION", 'utf8_unicode_ci');

/**
 * Database Mysql
 */
define("FF_DATABASE_NAME", "www_blueocarina_net");
define("FF_DATABASE_HOST", "localhost");
define("FF_DATABASE_USER", "bonetdb");
define("FF_DATABASE_PASSWORD", "EveryDay273");

/**
 * Database Mongo
 */
define("MONGO_DATABASE_NAME", "");
define("MONGO_DATABASE_HOST", "");
define("MONGO_DATABASE_USER", "");
define("MONGO_DATABASE_PASSWORD","");


/**
 * Trace
 */
define("TRACE_TABLE_NAME", 'trace');
define("TRACE_NOTIFY_TABLE_NAME", 'trace_notify');
define("TRACE_ONESIGNAL_APP_ID", "");
define("TRACE_ONESIGNAL_API_KEY", "");

/**
 * Trace Database Mysql
 */
define("TRACE_DATABASE_NAME", '');
define("TRACE_DATABASE_HOST", '');
define("TRACE_DATABASE_TABLE", '');
define("TRACE_DATABASE_USER", '');
define("TRACE_DATABASE_PASSWORD", '');

/**
 * Trace Database Mongo
 */
define("TRACE_MONGO_DATABASE_NAME", '');
define("TRACE_MONGO_DATABASE_HOST", '');
define("TRACE_MONGO_DATABASE_USER", '');
define("TRACE_MONGO_DATABASE_PASSWORD", '');

/**
 * Notifier
 */
define("NOTIFY_PUSH_ONESIGNAL_APP_ID", "");
define("NOTIFY_PUSH_ONESIGNAL_API_KEY", "");

/**
 * Notifier Database Mysql
 */
define("NOTIFY_SQL_HOST", '');
define("NOTIFY_SQL_NAME", '');
define("NOTIFY_SQL_USER", '');
define("NOTIFY_SQL_PASSWORD", '');
define("NOTIFY_SQL_TABLE", 'trace_notify');
define("NOTIFY_SQL_KEY", 'ID');

/**
 * Notifier Database Mysql
 */
define("NOTIFY_NOSQL_HOST", '');
define("NOTIFY_NOSQL_NAME", '');
define("NOTIFY_NOSQL_USER", '');
define("NOTIFY_NOSQL_PASSWORD", '');
define("NOTIFY_NOSQL_TABLE", 'trace_notify');
define("NOTIFY_NOSQL_KEY", 'ID');

/**
 * Email SMTP
 */
define("A_SMTP_HOST", 'localhost');
define("SMTP_AUTH", true);
define("A_SMTP_USER", 'postmaster@blueocarina.net');
define("A_SMTP_PASSWORD", 'Vodafone');

/**
 * Email Settings
 */
define("A_FROM_EMAIL", 'info@blueocarina.net');
define("A_FROM_NAME", 'Blue Ocarina');
define("CC_FROM_EMAIL", '');
define("CC_FROM_NAME", '');
define("BCC_FROM_EMAIL", 'debug@blueocarina.net');
define("BCC_FROM_NAME", 'test[blueocarina.net]');

/**
 * Superadmin
 */
define("SUPERADMIN_USERNAME", 'rambaldi');
define("SUPERADMIN_PASSWORD", 'marshall92');

/**
 * Locale
 */
define("LANGUAGE_DEFAULT", 'ITA');
define("LANGUAGE_DEFAULT_ID", '1');
define("LANGUAGE_RESTRICTED_DEFAULT", 'ITA');
define("TIMEZONE", "Europe/Rome");

/**
 * Repository Master
 */
define("MASTER_SITE", 'www.blueocarina.net');
define("PRODUCTION_SITE", '');
define("DEVELOPMENT_SITE", '');

/**
 * Auth Apachee
 */
define("AUTH_USERNAME", '');
define("AUTH_PASSWORD", '');

/**
 * FTP
 */
define("FTP_USERNAME", 'bonet');
define("FTP_PASSWORD", 'Gomma258');
define("FTP_PATH", '/httpdocs');

/**
 * Debug
 */
define("DEBUG_MODE", true);
define("DEBUG_PROFILING", true);
define("DEBUG_LOG", true);

/**
 * Site Settings
 */
//define("DISABLE_CACHE", true);
define("CM_LOCAL_APP_NAME", 'Blue Ocarina');
define("APPID", '9abe42619b6fa5ce92889ff1e6fed8b4-888ef0s118e1004221606882ef3ca09f');
define("CDN_STATIC", false);
//define("CM_SHOWFILES", "");
//define("CMS_SHOWFILES", "");
define("TRACE_VISITOR", true);
define("ADMIN_THEME"			, "admin");


/**
 * Server Settings
 */
define("PHP_EXT_MEMCACHE", false);
define("PHP_EXT_APC", false);
define("PHP_EXT_JSON", true);
define("PHP_EXT_GD", true);
define("APACHE_MODULE_EXPIRES", true);
define("MYSQLI_EXTENSIONS", true);
define("MEMORY_LIMIT", '96M');