<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This file will get the get all submission data that was submitted from a Formstack form
 * for a given form ID and matching any search criteria and then update a field(s) for 
 * each entry
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
 * This is a valid FormStack form ID
 */
$example_form_id;

// load an existing form that you want submissions from:
$form = $formStack->loadForm($example_form_id);

// print to screen:
$formStack->setDebug();

/**
 * @param (INT) $example_submission_id
 * This is a vaild FormStack submission ID for the current form
 */
$example_submission_id;

/**
 * @param (Array) $update_data ~ The data that will be sent as label/field_id => new value
 */
$update_data = array();

// Just try assigning it directly with no checks:
$update_data['Notes'] = "\n".'This was updated via the FormStack API v2 at '.date('M d, Y g:i:s a');

// now call on the

if ( $form->updateSubmission($example_submission_id, $update_data) ) {
    echo '<h2>Submission ID: '.$example_submission_id.' has been updated</h2>';
    
} else {
    echo '<h2>Submission ID: '.$example_submission_id.' could not be updated</h2>';
}

/**
 * you can also append a field value:
 */
// first get the submission data
$submission = $form->getSubmission($example_submission_id);

echo '<h2>Get Submission Data</h2>
<pre>';
print_r($submission);
echo '</pre>';

// reset data
$update_data = array();

/**
 * get existing field value 
 * Note the returned array has the numberic field_id as a key so we have a lazy 
 * method to convert a label to a key 
 */
$field_id = $form->getFieldId('Notes');
$update_data[$field_id] = '';

if ( !empty($field_id) && isset($submission['data'][$field_id]['value']) ) {
    
    $current_field_value = $submission['data'][$field_id]['value'];
    
    $update_data[$field_id] = $current_field_value;
}

$update_data[$field_id] .= "\n".'This field was appended via the FormStack API v2 at '.date('M d, Y g:i:s a');

if ( $form->updateSubmission($example_submission_id, $update_data) ) {
    echo '<h2>Submission ID: '.$example_submission_id.' has been updated</h2>';
    
} else {
    echo '<h2>Submission ID: '.$example_submission_id.' could not be updated</h2>';
}
