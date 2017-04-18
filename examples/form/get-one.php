<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This file will get the form field names/labels and the associated FormStack field id
 *  
 */
require_once dirname(dirname(__FILE__)).'/config.php';

/**
 * @param array $config ~ these are defined in config.php
 */
$config = array(
        'client_id'     => FormStack_client_id,
        'client_secret' => FormStack_client_secret,
        'redirect_url'  => 'https://www.example.com'.$_SERVER['PHP_SELF'],
        'access_token'  => FormStack_access_token
    );

/** @var \JGulledge\FormStack\API\FormStack $formStack */
$formStack = new FormStack($config);

// print to screen:
$formStack->setDebug();
// DO NOT set in production!
$formStack->setInsecure();

/** @param \JGulledge\FormStack\API\Forms */
$myForm = $formStack->loadForm($example_form_id);

echo 'ID: '.$myForm->getId();

$details = $myForm->getDetails();
unset($details['fields']);
unset($details['html']);
//unset($details['javascript']);

print_r($details);





