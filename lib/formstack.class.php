<?php
namespace FormStack;

/**
 * FormStack API v2
 * 
 * Autheniticate  https://www.formstack.com/developers/api/authorization
 * 
 * Forms 
 * - Fields
 * - Submissions
 * - Confirmations
 * - Notifications
 * - Webhooks
 * 
 * @Author: Joshua Gulledge
 * @License MIT
 * @Site: https://github.com/jgulledge19/FormStackAPI
 * @Verion: 1.0 alpha 
 * 
 */
class FormStack {
	/**
     * @param (Array) $config ~ name=>value
     * @access Protected
     */
    protected $config = array();
    /**
     * @param (Boolean) $debug
     * @access Protected
     */
    protected $debug = false;
    
    function __construct($config=array()) {
        $this->config = array(
            'authorize_url' => 'https://www.formstack.com/api/v2/oauth2/authorize',
            'token_url'     => 'https://www.formstack.com/api/v2/oauth2/token',
            'client_id'     => 'xxx',
            'client_secret' => '',
            'redirect_url'  => '',
            'access_token'  => '',
            'api_url'       => 'https://www.formstack.com/api/v2/',
            'api_key'       => '',
            'debug_hide_api_keys' => true,
            
        );
        $this->config = array_merge($this->config, $config);
        
        //print_r($this->config);
	}
    /**
     * Require user to authorize 
     */
    public function authorize()
    {
        if (!empty($_GET['code'])) {
            /**
            * We have an authorization code. We now exchange it for an access token.
            */
            $ch = curl_init($this->config['token_url']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
                'grant_type' => 'authorization_code',
                'client_id' => $this->config['client_id'],
                'client_secret' => $this->config['client_secret'],
                'code' => $_GET['code'],
                'redirect_uri' => $this->config['redirect_url']
            )));
            // oauth2 contains the the access_token.
            $oauth2 = json_decode(curl_exec($ch));
            
            $this->config['access_token'] = $oauth2->access_token;
        } else {
            /**
            * Send the user to the authorization page.
            */
            $auth_url = $this->config['authorize_url'] . '?' . http_build_query(array(
                'client_id' => $this->config['client_id'],
                'redirect_uri' => $this->config['redirect_url'],
                'response_type' => 'code'
            ));
            header('Location:' . $auth_url);
            exit;
        }
    }
    
    /**
     * @param (Boolean) $debug
     * @return (Void)
     */
    public function setDebug($debug=true)
    {
        $this->debug = $debug;
    }
        
    /**
     * @param (String) $uri
     * @param (String) $method
     * @param (Array) $data ~ name => value 
     * @param (Boolean) $xml ~ default is false(return JSON), if true return XML
     * 
     * @return (Mixed) $response ~ Array for JSON, Object for XML or false if curl failed
     * 1. Builds correct URL
     * 2. Sends curl request
     * 3. Shows response
     */ 
    public function sendRequest($uri, $method="GET", $data, $xml=false)
    {
        /**
         * The Formstack API offers two response types: JSON (default) and XML. 
         * To indicate which response type you would like to receive, 
         * simply add .xml or .json to the end of any resource URIs. 
         * If an extension is not present, we will respond with JSON.
         */
        $url = $this->config['api_url'].$uri;
        
        if ( $xml ) {
            $url .= '.xml'; 
        } else {
            $url .= '.json';
        }
        
        $data['access_token'] = $this->config['access_token'];
        // verson 1
        //$data['api_key'] = $this->config['api_key']; 
        try {
            $ch = curl_init();
            // For JSON ~ note need to review the POSTFIELDS below to use with JSON
            if ( 1 == 2 ) {
                curl_setopt($ch, CURLOPT_HEADER, 'Content-Type: application/json');
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Bearer ' . $this->config['access_token']
            )); 
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            /**
             * The Formstack API accepts two request data types: HTTP url encoded and JSON. 
             * By default, we expect HTTP url encoded. To use JSON, simply change the 
             * Content-Type header in your request to be "Content-Type: application/json" 
             * and put the JSON in the body of the request.
             */
            switch ($method) {
                case 'DELETE':
                    // http://stackoverflow.com/questions/13420952/php-curl-delete-request
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    $result = curl_exec($ch);
                    $result = json_decode($result);
                    curl_close($ch);
                    
                    break;
                case 'POST':
                    // http://davidwalsh.name/curl-post
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, count($data));
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    
                    break;
                case 'PUT':
                    // http://www.lornajane.net/posts/2009/putting-data-fields-with-php-curl
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
             
                    break;
                    
                case 'GET':
                default:
                    $url = $url. (strpos($url, '?') === FALSE ? '?' : '').http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url );
                    break;
            }
            if ( $this->debug ) {
                echo '<h2>Method: '.$method.' URI: '.$uri.'</h2>';
                $tmp_data = $data;
                if ( $this->config['debug_hide_api_keys'] ) {
                    foreach ($this->config as $key => $value) {
                        if ( isset($tmp_data[$key]) ) {
                            $tmp_data[$key] = 'XXXXXXXXXX-Hidden-XXXXXXXXXX';
                        }
                    }
                }
                echo '<pre>'.print_r($tmp_data, true).'</pre>';
            }
            //echo 'URL: '.$url.'<pre>';print_r($data);echo '</pre>';
            
            $response = curl_exec($ch);
            // echo 'R: '.$response;
            if ( $this->debug ) {
                
                echo 'Response: '.$response;
            }
        
        } catch (exception $e) {
            print_r($e);
        }
        if ( !$response ) {
            trigger_error(curl_error($ch)); 
            return false;
        }
        if ( $xml ) {
            return simplexml_load_string($response);
        } else {
            return json_decode($response, TRUE);
        }
        
    }
    
    /**
     * 
     */
    protected function buildUrl() {
        
    }
    /**
     * @param (INT) $id ~ the form ID
     * @return (Object) $form ~ FormStackForm object
     */
    public function loadForm($id)
    {
        require_once 'formstackform.class.php';
        return new FormStackForm($this, $id, $this->debug);
    }
}
/*
$formStack = new FormStack();

 *  new form - POST
 *  
 

// list:
$formStack->getForms();// list of forms

$myForm = $formStack->loadForm('id...');
**
 * GET
 * POST
 * /

$myForm->getFields();
$myForm->getSubmissions();

$myForm->updateSubmission($id, $data);

$myForm->runWebhooks();
*/