<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This example will add a Webhook for the passed form ID and Webhook name, url, handshake and optional details
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

$webhook = $myForm->addWebhook(
    'Webhook API Test',
    'http://example.com/created/via/add/'.rand(1,1000),
    'My random handshake: '.md5(rand(1,1000)),
    [
        // @see https://developers.formstack.com/docs/form-id-webhook-post
        'content_type' => 'json'// this is the default
    ]
);

print_r($webhook);