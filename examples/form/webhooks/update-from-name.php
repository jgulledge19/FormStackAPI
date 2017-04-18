<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This example will update a Webhook for the passed form ID and Webhook name, if the Webhook name does not exist,
 * it will be created
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

$webhook = $myForm->updateWebhookName('Webhook API Test',
    [
        'url' => 'http://example.com/update/via/name/'.rand(1000, 1000000),
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
