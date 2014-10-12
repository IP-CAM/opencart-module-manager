<?php

if (gethostname() == 'yurii-pc')
{
	// HTTP
	define('HTTP_SERVER', 'http://opencart-clear.dev/admin/');
	define('HTTP_CATALOG', 'http://opencart-clear.dev/');

	// HTTPS
	define('HTTPS_SERVER', 'http://opencart-clear.dev/admin/');
	define('HTTPS_CATALOG', 'http://opencart-clear.dev/');

	// DIR
	define('DIR_APPLICATION', 'C:\xampp\htdocs\opencart-clear.dev\www/admin/');
	define('DIR_SYSTEM', 'C:\xampp\htdocs\opencart-clear.dev\www/system/');
	define('DIR_DATABASE', 'C:\xampp\htdocs\opencart-clear.dev\www/system/database/');
	define('DIR_LANGUAGE', 'C:\xampp\htdocs\opencart-clear.dev\www/admin/language/');
	define('DIR_TEMPLATE', 'C:\xampp\htdocs\opencart-clear.dev\www/admin/view/template/');
	define('DIR_CONFIG', 'C:\xampp\htdocs\opencart-clear.dev\www/system/config/');
	define('DIR_IMAGE', 'C:\xampp\htdocs\opencart-clear.dev\www/image/');
	define('DIR_CACHE', 'C:\xampp\htdocs\opencart-clear.dev\www/system/cache/');
	define('DIR_DOWNLOAD', 'C:\xampp\htdocs\opencart-clear.dev\www/download/');
	define('DIR_LOGS', 'C:\xampp\htdocs\opencart-clear.dev\www/system/logs/');
	define('DIR_CATALOG', 'C:\xampp\htdocs\opencart-clear.dev\www/catalog/');

	// DB
	define('DB_DRIVER', 'mysqli');
	define('DB_HOSTNAME', 'localhost');
	define('DB_USERNAME', 'root');
	define('DB_PASSWORD', '');
	define('DB_DATABASE', 'opencart_watermark_module');
	define('DB_PREFIX', '');
}
elseif (gethostname() == 'yuriis-imac.dlink')
{
	// HTTP
	define('HTTP_SERVER', 'http://opencart-clear.dev/admin/');
	define('HTTP_CATALOG', 'http://opencart-clear.dev/');

	// HTTPS
	define('HTTPS_SERVER', 'http://opencart-clear.dev/admin/');
	define('HTTPS_CATALOG', 'http://opencart-clear.dev/');

	// DIR
	define('DIR_APPLICATION', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/admin/');
	define('DIR_SYSTEM', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/system/');
	define('DIR_DATABASE', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/system/database/');
	define('DIR_LANGUAGE', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/admin/language/');
	define('DIR_TEMPLATE', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/admin/view/template/');
	define('DIR_CONFIG', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/system/config/');
	define('DIR_IMAGE', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/image/');
	define('DIR_CACHE', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/system/cache/');
	define('DIR_DOWNLOAD', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/download/');
	define('DIR_LOGS', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/system/logs/');
	define('DIR_CATALOG', '/Users/yuriikrevnyi/sites/opencart-clear.dev/www/catalog/');

	// DB
	define('DB_DRIVER', 'mysqli');
	define('DB_HOSTNAME', 'localhost');
	define('DB_USERNAME', 'root');
	define('DB_PASSWORD', '');
	define('DB_DATABASE', 'opencart_dev');
	define('DB_PREFIX', '');
}