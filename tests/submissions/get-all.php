<?php
use \FormStack;
/**
 * This file will get the get all submission data that was submitted from a Formstack form
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

// set time filter to retrieve from 21 days ago until 1 day ago:
//$form->setSubmissionsTimesFilter(time()-21*3600*24, time()-1*3600*24);

// add a search to an Event field: This will only get those with a quantity of 1 or 1*
if ( !$form->setSubmissionsSearchFilter('basketball_june_day_camp__boys', 'quantity = 1') ) {
    echo '<br>Invaild search filter: field';
}

// Only valid feilds will be sent
/*
if ( ! $form->setSubmissionsSearchFilter('Payment Confirmed', 'Yes') ) {
    echo '<br>Invaild search filter: Payment Confirmed';
}
*/
// get the data:
$submissions = $form->getSubmissions(1);

echo '<pre>';
print_r($submissions);
echo '</pre>';


