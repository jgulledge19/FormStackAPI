<?php
use \JGulledge\FormStack\API\FormStack;
/**
 * This file will get one users submission data that was submitted from a Formstack form 
 * via the submission ID 
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

/**
 * You can set these to either by the label or the id for the field and you can mix them too
 * required fields?
 * NOTES: 
 *  - Emails and Webhooks are fired!
 *  - loads defaults if set via the FormStack Build
 *  - Product Inventory is updated if using a limit
 *  - Products/Events have no defaults set so you need to do set them completly
 *  - field names are case insensitive, they are converted to proper format 
 *  - Checkbox field types add \n to each option/value like: Value 1\nValue 2\nValue 3
 *      And then if you use the other opiton it will look like this: Value 1\nOther: Value 4 
 *  
 */
$fields = array(
    'email' => 'test@email.com',
    'Home Phone' => '123 456-7890',
    'work_phone' => '123 456-7890',
    'company' => 'My Company',
    // sub fields like name and address: method 1 multiple key=value pairs
    'name-first' => 'Test',
    'name-last' => 'API User',
    // sub fields like name and address: method 2 array
    'address' => array(
                    'address' => '123 Street',
                    'city' => 'South Bend',
                    'state' => 'IN',
                    'zip' => '12345'
                    
                ),
    /*
    // event/product field type:
    // => charge_type = fixed_amount quantity = 1 unit_price = 125 total = 125
    'Purchase basketball' => array(
            'charge_type' => 'fixed_amount',
            'quantity' => 1,
            'unit_price' => 16,
            'total' => 16
        ),
    'total' => '16',
    */
);

$timestamp = (time() - 24*3500*2);// Does not see to work

// get the data for one submission, returns array of data
$submission = $form->addSubmission($fields);//, $timestamp);

echo '<h1>Results: '.date('Y-m-d H:i:s', (int) $timestamp).'</h1>
<pre>';
print_r($submission);
echo '</pre>';



