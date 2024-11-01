<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$vdrm_environments = array(
	'Production' => 'https://api.virtuoussoftware.com',
	'Staging' => 'https://virtuousapiprod-staging.azurewebsites.net/',
	'Quality Assurance' => 'https://virtuousapiqa.azurewebsites.net',
	'Development' => 'https://virtuousapidev.azurewebsites.net'
);
