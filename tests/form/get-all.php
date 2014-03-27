<?php
use \FormStack;
/**
 * This file will get the form field names/labels and the associated FormStack field id
 *  
 */
require_once '../config.php';

/**
 * @param (Array) $config ~ these are defined in config.php
 */
$config = array(
        'client_id'     => FormStack_client_id,
        'client_secret' => FormStack_client_secret,
        'redirect_url'  => 'https://www.example.com'.$_SERVER['PHP_SELF'],
        'access_token'  => FormStack_access_token,
        'api_key'       => FormStack_api_key,
    );


$formStack = new \FormStack\FormStack($config);

// print to screen:
$formStack->setDebug();

$forms = $formStack->getForms();

if ( is_array($forms) ) {
    foreach ($forms as $name => $formObject) {
        echo '<h2>Form: '.$name.' and ID: '.$formObject->getId().'</h2>
        <p>Form Fields:</p>
        <pre>';
        print_r($formObject->getFields());
        echo '</pre>';
                
    }
}




