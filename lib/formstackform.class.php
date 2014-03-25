<?php
namespace FormStack;
/**
 * API for all FormSTack forms 
 *   
 */
 
/**
 * 
 */
class FormStackForm {
	/**
     * @param formStack ~ (object) the connection class
     * @access protected
     */
    protected $formStack = null;
    /**
     * @param (INT) $id ~ form ID
     * @access protected
     */
    protected $id;
    
    /**
     * @param (Array) $field_names ~ array(name=>id)
     * @access protected
     */
    protected $field_names = null;
    
    /**
     * @param (Array) $fields ~ array(count=>id)
     * @access protected
     */
    protected $fields = null;
    
    /**
     * @param (Array) $submissions ~ array(submission_id=>data)
     * @access protected
     */
    protected $submissions = null;
    
    /**
     * @param (Array) $submissions_filters ~ array(api_argument=>value)
     * @access protected
     */
    protected $submissions_filters = array();
    /**
     * @param (Boolean) $debug
     * @access Protected
     */
    protected $debug = false;
    
	function __construct(&$formStack, $id, $debug) {
		$this->formStack = &$formStack;
        $this->id = $id;
        $this->debug = $debug;
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
     * @param (Boolean) $xml ~ default is false(return JSON), if true return XML
     * @description Get the form fields
     * @return (Mixed) $response ~ Array for JSON, Object for XML or false if curl failed
     */
    public function getFields($xml=false)
    {
        $response = $this->sendRequest('field', "GET", array(), $xml);
        // map to array name=>id
        if (!$xml && is_array($response) ) {
            $this->field_names = $this->fields = array();
            foreach ($response as $count => $field) {
                $this->field_names[$field['name']] = $field['id'];
                $this->fields[$field['id']] = $field;
            }
            return $this->fields;
        } else if ($xml) {
            
        } 
        return false;
    }
    
    /**
     * @param (String) $label ~ the form label
     * @return (INT) $form_field_id or null if not found
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
     * Get one submission
     * @param (INT) $id ~ submission ID
     * @param (String) $encryption_password ~ The encryption password for the form
     * @param (Boolean) $xml ~ default is false(return JSON), if true return XML
     * @return (Array) $data (name=>value)
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
     * @param (Mixed -INT/String) $start ~ (INT) UNIX timestamp, (String) YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
     *      Set value to return submissions after given date/time 
     * @param (Mixed -INT/String) $end ~ (INT) UNIX timestamp, (String) YYYY-MM-DD or YYYY-MM-DD HH:MM:SS
     *      Set value to return submissions before given date/time  
     * @param (String) $timezone ~ the timezone of the $start and $end time
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
     * @param (Mixed -INT/String) $field ~ can be the ID or the label strtolower_label
     * @param (String)
     * @return (Boolean) true it has been verified and set, false field not found
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
     * @param (INT) $per_page ~ The number of submissions to return per page
     * @param (INT) $page ~ The total page number of results to return
     * 
     * @return void
     */
    public function setSubmissionsPagesFilter($per_page=null, $page=null)
    {
        if ( !empty($page) ) {
            $this->submissions_filters['page'] = $page;
        }
        if ( !empty($end) ) {
            $this->submissions_filters['per_page'] = $per_page;
        }
    }
    
    /**
     * get all submissions within filters
     * @param (Boolean) $data ~ Include submission data
     * @param (Boolean) $expand_data ~ Expand submission data
     * @param (String) $sort ~ Sort the submissions by id with DESC or ASC
     * @param (String $encryption_password ~ The encryption password for the form
     * @param (Boolean) $xml ~ default is false(return JSON), if true return XML
     * 
     * @return (Array) $submissions ~ array(submission_id => data )
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
     * @param (INT) $id
     * @param (Array) $fields (name=>value) the name can be the ID or the label
     * @param (Boolean) $xml ~ default is false(return JSON), if true return XML
     * 
     * Updating value of field(s)
     * @return (Boolean) true - success, false - failed
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
     * @param (String) $action ~ form, field, submission, confirmation, notification, webhook
     * @param (Sting) $method ~ GET, POST, PUT, DELETE
     * @param (Array) $data ~ name => value 
     * @param (Boolean) $xml ~ default is false(return JSON), if true return XML
     * 
     * @return (Mixed) $response ~ Array for JSON, Object for XML or false if curl failed
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
     * @param (String) $action ~ form, field, submission, confirmation, notification, webhook
     * @param (Sting) $method ~ GET, POST, PUT, DELETE
     * @param (INT) $item_id ~ the id for the type if nessicary
     * @return (String) $uri
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
                    // get form:
                    $uri = $action.'/'.$this->id;
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
     * @param (Mixed INT/Sting) $time
     * @param (String) $timezone
     * @return (String) $date ~ the expected date in proper format
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
    
}