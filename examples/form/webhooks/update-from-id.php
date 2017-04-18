<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This example will update a Webhook for the passed form ID and Webhook ID, if the Webhook does not exist, return false
 *  
 */
require_once dirname(dirname(dirname(__FILE__))).'/config.php';

/**
 * @param array $config ~ these are defined in config.php
 */
$config = array(
        'client_id'     => FormStack_client_id,
        'client_secret' => FormStack_client_secret,
        'redirect_url'  => 'https://www.example.com'.$_SERVER['PHP_SELF'],
        'access_token'  => FormStack_access_token
    );

$formStack = new FormStack($config);

// print to screen:
$formStack->setDebug();
// DO NOT set in production!
$formStack->setInsecure();

/** @param \JGulledge\FormStack\API\Forms */
$myForm = $formStack->loadForm($example_form_id);

$webhook = $myForm->updateWebhook($example_webhook_id,
    [
        'url' => 'http://example.com/update/via/id/'.rand(1000, 1000000),
        'handshake_key' => 'My random handshake: '.md5(rand(1000, 100000))
]);

echo PHP_EOL;
print_r($webhook);

/**
 * Array
    (
        [success] => 1
        [id] => 12345
    )
 *
 */
