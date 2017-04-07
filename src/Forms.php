<?php
namespace JGulledge\FormStack\API;
/**
 * API for all FormSTack forms 
 *   
 */
class Forms {
	/**
     * @param formStack ~ (object) the connection class
     */
    protected $formStack = null;

    /**
     * @param int $id ~ form ID
     */
    protected $id;
    
    /**
     * @param array $details ~ form details array(count=>id)
     */
    protected $details = null;
    
    /**
     * @param array $field_names ~ array(name=>id)
     */
    protected $field_names = null;
    
    /**
     * @param array $fields ~ array(count=>id)
     * @TODO review, need type and other data to parse submissions to readable
     */
    protected $fields = null;
    
    /**
     * @param array $submissions ~ array(submission_id=>data)
     */
    protected $submissions = null;
    
    /**
     * @param array $submissions_filters ~ array(api_argument=>value)
     */
    protected $submissions_filters = array();

    /**
     * @param boolean $debug
     */
    protected $debug = false;
    
	function __construct(&$formStack, $id, $debug=false, $details=null) {
		$this->formStack = &$formStack;
        $this->id = $id;
        $this->debug = $debug;
        if ( !empty($details) ) {
            $this->details = $details;
        }
	}
    
    /**
     * @return int $form_id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the form details https://www.formstack.com/developers/api/resources/form#form/:id_GET
     * @param boolean $xml ~ default is false(return JSON), if true return XML
     * @description Get the form fields
     *
     * @return mixed ~ Array for JSON, Object for XML or false if curl failed
     */
    public function getDetails($xml=false)
    {
        $response = $this->sendRequest('form', "GET", array('id' => $this->id), $xml);
        $this->details = $response;
        
        if ( $this->loadFields($response['fields'],$xml) ) {
            // report error?
        }
        
        return $response;
    }
    /**
     * @param boolean $debug
     * @return void
     */
    public function setDebug($debug=true)
    {
        $this->debug = $debug;
        $this->formStack->setDebug($debug);
    }
    
    /**
     * @param boolean $xml ~ default is false(return JSON), if true return XML
     * @description Get the form fields
     * @return bool|array ~ Array for JSON, Object for XML or false if curl failed
     */
    public function getFields($xml=false)
    {
        if ( !empty($this->fields) ) {
            return $this->fields;
        }
        $response = $this->sendRequest('field', "GET", array(), $xml);
        // map to array name=>id
        if ( $this->loadFields($response, $xml) ) {
            return $this->fields;
        }
        
        return false;
    }
    
    /**
     * Load the fields from the response data
     * @param array $fields
     * @param boolean $xml ~ default is false(return JSON), if true return XML
     * @return boolean
     */
    protected function loadFields($fields, $xml=false) {
        if (!$xml && is_array($fields) ) {
            $this->field_names = $this->fields = array();
            foreach ($fields as $count => $field) {
                $this->field_names[$field['name']] = $field['id'];
                $this->fields[$field['id']] = $field;
            }
            return true;
        } else if ($xml) {
            
        } 
        return false;
    }

    /**
     * Get the Field Names/Labels and the FormStack Field ID
     * @return array $field_names ~ array(name => id)
     */
    public function getFieldNames()
    {
        if ( empty($this->field_names) ) {
            $this->getFields();
        }
        return $this->field_names;
    }

    /**
     * Get the Field Names from the FormStack Field ID
     * @param int $id
     * @return string|null $name
     */
    public function getFieldName($id)
    {
        if ( empty($this->field_names) ) {
            $this->getFields();
        }
        if ( !($name = array_search($id, $this->field_names)) ) {
            return null;
        }
        return $name;
    }
    /**
     * @param string $label ~ the form label
     * @return int|null
     */
    public function getFieldId($label)
    {
        if ( empty($this->fields) ) {
            $this->getFields();
        }
        $form_field_id = null;
        
        $label = str_replace(' ', '_', strtolower($label));
        
        if ( isset($this->field_names[$label]) ) {
            $form_field_id = $this->field_names[$label];
        }
        
        return $form_field_id;
    }

    /**
     * @param array $fields ~ array(label or ID => value )
     *      sub fields should be like: name-first, name-last and address-address, address-city, ect. 
     *              OR
     *              As sub array ~ name => array(first => value, last => value)
     * @param int $timestamp ~ time that submission was created, default to current time
     * @param string $user_agent ~ Browser user agent value that should be recorded for the submission
     * @param string $remote_addr ~ IP address that should be recorded for the submission
     * @param string $payment_status ~ Status of a payment integration
     * @param int $read ~ Flag (1 or 0) indicating the submission has a status of read
     * Options: https://www.formstack.com/developers/api/resources/submission#form/:id/submission_POST
     * @return int|bool $submission ID on success else false
     */
    public function addSubmission($fields, $timestamp=null, $user_agent=null, $remote_addr=null, $payment_status=null, $read=0)
    {
        if ( empty($this->fields) ) {
            $this->getFields();
        }
        
        $data = array();
        if ( !empty($timestamp) ) {
            $timestamp = time();
            $data['timestamp'] = date('Y-m-d H:i:s', (int) $timestamp);//YYYY-MM-DD HH:MM:SS format is expected.
        }
        if ( $read == 1 ) {
            $data['read'] = 1;
        }
        if ( !empty($user_agent) ) {
            $data['user_agent'] = $user_agent;
        }
        if ( !empty($remote_addr) ) {
            $data['remote_addr'] = $remote_addr;
        }
        if ( !empty($payment_status) ) {
            $data['payment_status'] = $payment_status;
        }
        
        // set defaults:
        foreach ( $this->fields as $id => $field ) {
            // do something with type?
            switch ($field['type']) {
                case 'product':
                    // currently can not see other details
                    break;
                default:
                    
                    break;
            }
            $data['field_'.$id] = $field['default'];
        }
        
        // now add to the data:
        foreach ($fields as $label => $value) {
            if ( is_array($value) ) {
                foreach ( $value as $sub => $v ) {
                    if ( !is_numeric($label)) {
                        $label = $this->getFieldId($label);
                    }
                    $data['field_'.$label][$sub] = $v;
                }
            } else if ( is_numeric($label) ) {
                $data['field_'.$label] = $value;
            } else if ( strpos($label, '-') > 0) {
                list($label, $sub) = explode('-', $label);
                if ( !is_numeric($label)) {
                    $label = $this->getFieldId($label);
                }
                if ( $label > 0 ) {
                    $data['field_'.$label][$sub] = $value;
                }
            } else {
                $id = $this->getFieldId($label);
                if ( $id > 0 ){
                    $data['field_'.$id] = $value;
                }
            }
        }
        
        $response = $this->sendRequest('submission', "POST", $data, $xml=false);
        return $response;
        
    }

    /**
     * Get one submission
     * @param int $id ~ submission ID
     * @param string $encryption_password ~ The encryption password for the form
     * @param bool $xml ~ default is false(return JSON), if true return XML
     * @return array $data (name=>value)
     */
    public function getSubmission($id, $encryption_password=null, $xml=false )
    {
        if ( isset($this->submissions[$id]) ) {
            return $this->submissions[$id];
        }
        $data = array('id' => $id);
        if ( !empty($encryption_password) ) {
            $data['encryption_password'] = $encryption_password;
        }
        $response = $this->sendRequest('submission', "GET", $data, $xml);
        
        $data = $response['data'];
        unset($response['data']);
        
        $this->submissions[$response['id']] = $response;
        
        // now add data back in as submission_id => array(key => value)
        foreach ($data as $count => $row) {
            $this->submissions[$response['id']]['data'][$row['field']] = $row;
        }
        return $this->submissions[$response['id']];
    }
    
    
    /**
     * @param int|string $start ~ (INT) UNIX timestamp, (String) YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
     *      Set value to return submissions after given date/time 
     * @param int|string $end ~ (INT) UNIX timestamp, (String) YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
     *      Set value to return submissions before given date/time  
     * @param string $timezone ~ the timezone of the $start and $end time
     * 
     * @return void
     */
    public function setSubmissionsTimesFilter($start=null, $end=null, $timezone=null)
    {
        if ( !empty($start) ) {
            $this->submissions_filters['min_time'] = $this->convertTime($start, $timezone);
        }
        if ( !empty($end) ) {
            $this->submissions_filters['max_time'] = $this->convertTime($end, $timezone);
        }
    }
    
    /**
     * @param int|string $field ~ can be the ID or the label strtolower_label
     * @param string $search
     *
     * @return bool true it has been verified and set, false field not found
     */
    public function setSubmissionsSearchFilter($field, $search)
    {
        $return = true;
        if ( empty($this->fields) ) {
            $this->getFields();
        }
        if ( is_numeric($field) ) {
            if ( !isset($this->fields[$field]) ) {
                $return = false;
            }
        } else {
            // get the field ID
            $field = $this->getFieldId($field);
            if (empty($field) ) {
                $return = false;
            }
        }
        if ( !$return ) {
            if ( $this->debug ) {
                echo '<br>Invaild search filter param: <strong>'.$field.'</strong> not found in form# '.$this->id;
            }
            return false;
        }
        $this->submissions_filters['searches'][] = array( 'field' => $field, 'search' => $search);
        
        return true;
    }
    /**
     * @param int $per_page ~ The number of submissions to return per page
     * @param int $page ~ The total page number of results to return
     * 
     * @return void
     */
    public function setSubmissionsPagesFilter($per_page=null, $page=null)
    {
        if ( !empty($page) ) {
            $this->submissions_filters['page'] = $page;
        }
        if ( !empty($per_page) ) {
            $this->submissions_filters['per_page'] = $per_page;
        }
    }
    
    /**
     * get all submissions within filters
     * @param bool $data ~ Include submission data
     * @param bool $expand_data ~ Expand submission data
     * @param bool $sort ~ Sort the submissions by id with DESC or ASC
     * @param string $encryption_password ~ The encryption password for the form
     * @param bool $xml ~ default is false(return JSON), if true return XML
     * 
     * @return array $submissions ~ array(submission_id => data )
     */
    public function getSubmissions($data=0, $expand_data=0, $sort='DESC', $encryption_password=null, $xml=false )
    {
        $api_data = array();
        
        foreach ($this->submissions_filters as $filter => $value) {
            if ( $filter == 'searches' ) {
                $x = 1;
                foreach ($value as $k => $row) {
                    $api_data['search_field_'.$x] = $row['field'];
                    $api_data['search_value_'.$x] = $row['search'];
                    $x++;
                }
                
            } else {
                $api_data[$filter] = $value;
            }
        }
        $api_data['sort'] = $sort;
        if ( !empty($data) ) {
            $api_data['data'] = $data;
        }
        if ( !empty($expand_data) ) {
            $api_data['expand_data'] = $expand_data;
        }
        if ( !empty($encryption_password) ) {
            $api_data['encryption_password'] = $encryption_password;
        }
        if ( $this->debug ) {
            echo '<h2>getSubmissions::submissions_filters</h2><pre>'.print_r($this->submissions_filters, true).'</pre>';
        }
        $response = $this->sendRequest('submission', "GET", $api_data, $xml);
        
        foreach ($response['submissions'] as $count => $submission ) {
            $this->submissions[$submission['id']] =$submission;
        }
        
        return $this->submissions;
    }
    /**
     * @param int $id
     * @param array $fields (name=>value) the name can be the ID or the label
     * @param bool $xml ~ default is false(return JSON), if true return XML
     * 
     * Updating value of field(s)
     * @return bool
     */
    public function updateSubmission($id, $fields, $xml=false)
    {
        /**
         * 1. Get the form Fields first
         * 2. If the data $name is string then get ID from the fields list
         * 3. Make correct data
         * 4. Call on request function
         * 5. Handle error
         */
        if ( empty($this->fields) ) {
            $this->getFields();
            
        }
        /**
         * id
         * remote_addr ~ IP address that should be recorded for the submission. defaults to the IP address from the API request
         * payment_status ~ Status of a payment integration
         * read ~ Flag (1 or 0) indicating the submission has a status of read
         * field_x ~ Value that should be stored for a specific field on the form. x must contain the id of the field whose value should be set 
         */
        
        $data = array(
                'id' => $id,
            );
        foreach ($fields as $label => $value) {
            $id = null;
            if (is_numeric($label) && isset($this->fields[$label])) {
                $id = $label;
            } else {
                $id = $this->getFieldId($label);
            }
            if ( !empty($id)) {
                $data['field_'.$id] = $value;
            }
        }
        
        $response = $this->sendRequest('submission', "PUT", $data, $xml);
        
        // returns JSON {"success":"1"}
        if ( $response['success'] == '1' ) {
            return true;
        }
        return false;
    }
    
    /**
     * @param string $action ~ form, field, submission, confirmation, notification, webhook
     * @param sting $method ~ GET, POST, PUT, DELETE
     * @param array $data ~ name => value
     * @param bool $xml ~ default is false(return JSON), if true return XML
     * 
     * @return mixed $response ~ Array for JSON, Object for XML or false if curl failed
     * 
     */
    protected function sendRequest($action, $method="GET", $data=array(), $xml=false)
    {
        $id = null;
        if ( isset($data['id'])  && !empty($data['id']) ) {
            $id = $data['id'];
        }
        $uri = $this->buildUri($action, $method, $id);
        //echo '<br>URI: '.$uri;
        return $this->formStack->sendRequest($uri, $method, $data, $xml);
    }
    
    /**
     * @param string $action ~ form, field, submission, confirmation, notification, webhook
     * @param sting $method ~ GET, POST, PUT, DELETE
     * @param int $item_id ~ the id for the type if nessicary
     * @return string $uri
     * 
     * https://www.formstack.com/developers/api/resources
     * should I make the methods CRUD for readable?  create, read, update, delete, copy, others?
     * 
     */
    protected function buildUri($action, $method='GET', $item_id=null) {
        $uri = '';
        $action = strtolower($action);
        
        switch (strtoupper($method)) {
            // Get one or many
            case 'GET':
                
                if ( !empty($item_id) && is_numeric($item_id) ) {
                    // individual record:
                    $uri = $action.'/'.$item_id;
                } else if ( $action == 'form') {
                    // get all forms:
                    $uri = $action.'/';
                } else {
                    // get list:
                    $uri = 'form/'.$this->id.'/'.$action;
                }
                break;
            
            // Create new one
            case 'POST':
                if ( $action == 'form') {
                    // form only:
                    $uri = $action.'/'.$this->id;
                } else {
                    // create a new action ~ all others
                    $uri = 'form/'.$this->id.'/'.$action;
                }
                break;
            
            // Update one
            case 'PUT':
                
                if ( !empty($item_id) && is_numeric($item_id) ) {
                    // individual record:
                    $uri = $action.'/'.$item_id;
                } else if ( $action == 'form') {
                    // get form:
                    $uri = $action.'/'.$this->id;
                }
                break;
            
            // DELETE one
            case 'DELETE':
                
                if ( !empty($item_id) && is_numeric($item_id) ) {
                    // individual record:
                    $uri = $action.'/'.$item_id;
                } else if ( $action == 'form') {
                    // get form:
                    $uri = $action.'/'.$this->id;
                }
                break;
        }
        
        return $uri;
    }

    /**
     * Set to FormStack expected time
     * PHP 5.3+
     * @param int|string $time
     * @param string $timezone
     * @return string $date ~ the expected date in proper format
     */
    protected function convertTime($time, $timezone=null)
    {
        $full_format = true;
        $format = 'U';
        
        if ( !is_numeric($time) ) {
            $format = 'Y-m-d H:i:s';
            if ( strlen($time) <= 10 ) {
                $format = 'Y-m-d';
                $full_format = false;
            }
        }
        
        if ( !empty($timezone) ) {
            
            $dt = \DateTime::createFromFormat($format, $time, $timezone);
            $dt->setTimeZone(new DateTimeZone('America/New_York'));
            
        } else {
            $dt = \DateTime::createFromFormat($format, $time);
        }
        if ( $full_format ){
            $date = $dt->format('Y-m-d H:i:s');
        } else {
            $date = $dt->format('Y-m-d');
        }
        return $date;
    }
    /**
     * @param string $value
     * @param string $return ~ 'all', or key in parsed array
     *
     * @return array|mixed
     */
    public function parseEventField($value, $return='quantity' )
    {
        // break appart:
        $parts = explode("\n", $value);
        if ( count($parts) == 4 ) { //
            $data = array();
            /**
             * @TODO Review for other types
             * charge_type = fixed_amount
                quantity = 1
                unit_price = 10
                total = 10
             */
            if ( $parts[0] == 'charge_type = fixed_amount' ) {
                list($t, $data['quantity']) = explode('quantity = ', $parts[1]);
                list($t, $data['unit_price']) = explode('unit_price = ', $parts[2]);
                list($t, $total['total']) = explode('total = ', $parts[3]);
            } else if (  $parts[0] == 'fixed_amount' ) {
                $data = array(
                        'charge_type' => $parts[0],
                        'quantity' => $parts[1],
                        'unit_price' => $parts[2],
                        'total' => $parts[3],
                    );
            }
            if ( $return != 'all' && isset($data[$return]) ) {
                return $data[$return];
            } else if ( count($data) > 0 ) {
                return $data;
            }
        }
        // for name and address types:
        $value = str_replace(array('first = ', 'last = ', 'address = ', 'city = ', 'state = ', 'zip = '), ' ', $value);
        // remove new lines
        $value = preg_replace('/\R/', ' ', $value);
        return $value;
    }
}
