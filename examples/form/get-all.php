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


$formStack = new FormStack($config);

// print to screen:
$formStack->setDebug();
// DO NOT set in production!
$formStack->setInsecure();

$forms = $formStack->getForms();

if ( is_array($forms) ) {
    foreach ($forms as $name => $formObject) {
        /** @param \JGulledge\FormStack\API\Forms */
        $formObject;

        $details = $formObject->getDetails();
        unset($details['fields']);
        //print_r($formObject->getFields());exit();

        echo '<h2>Form: '.$name.' and ID: '.$formObject->getId().'</h2>
        <h3>Form Details</h3>';
        print_r($details);

        echo '<h3>Form Fields:</h3>
        <pre>';
        print_r($formObject->getFields());
        echo '</pre>';
                
    }
}




