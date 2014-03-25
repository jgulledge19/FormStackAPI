<?php
use \FormStack;
/**
 * This file will get the get all submission data that was submitted from a Formstack form
 * for a given form ID and matching any search criteria and then update a field(s) for 
 * each entry
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

// print to screen:
$formStack->setDebug();

/**
 * get all submissions:
 */

// set time filter to retrieve from 21 days ago until 1 day ago:
$form->setSubmissionsTimesFilter(time()-21*3600*24, time()-1*3600*24);

// add a search to a field:
if ( !$form->setSubmissionsSearchFilter('field', 'test') ) {
    echo '<br>Invaild search filter: field';
}
// Only valid feilds will be sent
if ( ! $form->setSubmissionsSearchFilter('Payment Confirmed', 'Yes') ) {
    echo '<br>Invaild search filter: Payment Confirmed';
}

// get all the submission data: Note it is up to you if you want to recieve the existing data or not
$submissions = $form->getSubmissions(1);

if ( !empty($submissions) ) {
    foreach ($submissions as $submission_id => $submission ) {
        echo '<h2>SUBMISSION LOOP: '.$submission_id.'</h2>';
        print_r($submission);
        
        
        /**
         * @param (Array) $update_data ~ The data that will be sent as label/field_id => new value
         */
        $update_data = array();
        
        /**
         * get existing field value 
         * Note the returned array has the numberic field_id as a key so we have a lazy 
         * method to convert a label to a key 
         */
        $field_id = $form->getFieldId('Payment Confirmed');
        if ( !empty($field_id) && isset($submission['data'][$field_id]['value']) ) {
            
            $current_field_value = $submission['data'][$field_id]['value'];
            
            if ( $current_field_value == 'No' ) {
                // do something else...
                $update_data[$field_id] = 'Yes';
            }
        }
    	
        // Just try assigning it directly with no checks:
        $update_data['Notes'] = 'This was updated via the FormStack API v2 at '.date('M d, Y g:i:s a');
        
        
        // now call on the
        
        if ( $form->updateSubmission($submission_id, $update_data) ) {
            echo '<h2>Submission ID: '.$submission_id.' has been updated</h2>';
            
        } else {
            echo '<h2>Submission ID: '.$submission_id.' could not be updated</h2>';
            
        }
           
    }
}