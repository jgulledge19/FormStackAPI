<?php
use \FormStack;
/**
 * This file will get one users submission data that was submitted from a Formstack form 
 * via the submission ID 
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

/**
 * @param (INT) $example_form_id
 * This is a vaild FormStack form ID
 */
$example_form_id;

// load an existing form that you want submissions from:
$form = $formStack->loadForm($example_form_id);

/**
 * @param (INT) $example_submission_id
 * This is a vaild FormStack submission ID for the current form
 */
$example_submission_id;

// get the data for one submission, returns array of data
$submission = $form->getSubmission($example_submission_id);

echo '<pre>';
print_r($submission);
echo '</pre>';



