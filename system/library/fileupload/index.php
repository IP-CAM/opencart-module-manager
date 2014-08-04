<?php

error_reporting(E_ALL | E_STRICT);

// Get application config
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';

// Require main image handler
require(DIR_SYSTEM . 'library/fileupload/UploadHandler.php');
require(DIR_SYSTEM . 'library/fileupload/MysqlUploadHandler.php');

// Get product id
if ( ! empty($_GET['product_id']))
{
	$product_id = (int) $_GET['product_id'];
}



$upload_handler = new MysqlUploadHandler(array(
    'image_versions' => array(),

	'product_id' => $product_id,

	'upload_dir' => DIR_IMAGE . 'data/',
	'upload_url' => DIR_IMAGE . 'data/',
	'store_url' => 'data/'
));
