<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This example will get all Webhooks for the passed form ID
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

$webhook = $myForm->deleteWebhookName('Webhook API Test');
// OR
// $webhook = $myForm->deleteWebhook($example_webhook_id);

print_r($webhook);

/**
 * {
    "success": "1",
    "id": "1"
    }
 */