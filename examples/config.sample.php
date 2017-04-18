<?php
/**
 * This file should be protected as you do not want users to have access to your connection params
 * 
 * For Windows you may to to set up the curl.cainfo
 * - http://us3.php.net/manual/en/function.curl-setopt.php#110457
 * - http://curl.haxx.se/docs/caextract.html
 * just add to the top of your config file:
 * ini_set('curl.cainfo', 'E:\inetpub\cacert.pem');
 */
error_reporting(E_ALL);

/** 
 * This is your FormStack client ID 
 */
define('FormStack_client_id', '1001');

/** This is your FormStack client secret */
define('FormStack_client_secret', 'XXXX');

/** This is your FormStack redirect URL for apps: */
define('FormStack_redirect_url', 'https://www.example.com');

/** This is your FormStack access token */
define('FormStack_access_token', '1234567890ABCDEFGHIJKLM' );

/**
 * Example data:
 */
$example_form_id = 12345;
$example_submission_id = 12345;
$example_webhook_id = 12345;


require_once dirname(dirname(__FILE__)) . '/src/FormStack.php';
