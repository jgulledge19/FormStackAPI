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

/**
 * @param int $example_form_id
 * This is a vaild FormStack form ID
 */
//$example_form_id;

// load an existing form that you want submissions from:
$form = $formStack->loadForm($example_form_id);

$fields = $form->getFieldNames();
foreach ($fields as $n => $id ) {
    echo '"'.ucwords(str_replace('_', ' ', $n)).'":"'.$id.'",';
}
echo '<pre>';
print_r($fields);
echo '</pre>';



