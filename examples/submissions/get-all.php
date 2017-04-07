<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This file will get the get all submission data that was submitted from a Formstack form
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

// set time filter to retrieve from 21 days ago until Today:
$form->setSubmissionsTimesFilter(time()-21*3600*24, time());

// add a search to an Event field: This will only get those with a quantity of 1 or 1*
// if ( !$form->setSubmissionsSearchFilter('basketball_june_day_camp__boys', 'quantity = 1') ) {
//    echo '<br>Invaild search filter: field';
// }

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


