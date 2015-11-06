<?php

/*
 * This work is licensed under the Creative Commons GNU GPL v3 License.
 * 
 * Source:  https://github.com/iam-decoder/rest_server
 * License: http://www.gnu.org/licenses/gpl.html
 * Version: 1.0.1
 * Author:  Travis J. Neal
 * Web:     https://github.com/iam-decoder
 * 
 * A Plug-and-play REST Controller with CORS support meant to fit in
 * most PHP implemented servers.
 * 
 * This is a plug-and-play library meant to fit most web applications. 
 * However, for best results the use of an Apache-based server is
 * desired. If you plan to use this library on a non-apache-based
 * server, please replace values being returned to the _request
 * property branching off of the _init method with your server's
 * version of the variables (or use your own clever way to get the
 * necessary result).
 * 
 */

class REST_Server {
    
    private
        $_url_args = array()
    ,   $_method_args = array()
    ,   $_request = array()
    ,   $_supported_formats = array(
            'form' => 'application/x-www-form-urlencoded'
        ,   'xml' => 'application/xml'
        ,   'json' => 'application/json'
        ,   'serialized' => 'application/vnd.php.serialized'
        ,   'csv' => 'application/csv'
    )
    ,   $_response_data = NULL
    ,   $__default_response_type = "json"
    ,   $__charset = "utf-8"
    ,   $__default_xml_wrapper = "ApplicationResponse"
    ,   $__ajax_only = true //if this is true, only requests with the x-requested-with header and value of xmlhttprequest will be allowed
    ,   $__allowed_http_origins = array('*') //specify which http origin(s) a request can come from
    ,   $__allowed_http_methods = array('get', 'delete', 'post', 'put', 'options', 'head') //specify which http request methods are allowed
    ,   $__allowed_http_headers = array('origin', 'x-requested-with', 'content-type') //specify which headers are allowed in requests
    ,   $__allow_http_credentials = true //specify that the server accepts cookies in requests
    ;
    
    public function __construct($config = NULL){
        $this->_init($config); //allows for different settings at different endpoints
        if(headers_sent())die;
        header('Access-Control-Allow-Origin: '.join(', ', $this->__allowed_http_origins));
        header('Access-Control-Allow-Methods: '.join(', ', $this->__allowed_http_methods));
        header('Access-Control-Allow-Credentials: '.($this->__allow_http_credentials ? "true" : "false"));
        header('Access-Control-Allow-Headers: '.join(', ', $this->__allowed_http_headers));
    	if($this->_request['method'] === 'options') die; //most option requests are from AJAX preflight calls checking it's CORS status and authenticity, since the initial request carries no data, we die to move on to the request that matters
        if($this->__ajax_only && !$this->_request['ajax']) { //if we are only accepting AJAX requests, and the request was not made with AJAX, then die.
            die('Only AJAX requests are accepted.');
        }
        if(!in_array($this->_request['method'], $this->__allowed_http_methods)){ //make sure that we're using an allowed method
            die('The requested HTTP method is not allowed.');
        }
        if(get_parent_class()){ //if we have a parent, now that we've made sure the request hasn't been rejected and updated the class settings, we can call the constructor to initialize the parent
            parent::__construct();
        }
        $this->_parse_url_args(); //sometimes a request is accompanied by url arguments and an input stream, this way we can access both.
        $this->{'_parse_'.$this->_request['method']}();
    }
    
    public function requestData($method, $element = NULL){
        if(is_string($method)){
            $method = strtolower($method);
        }
        if(empty($method) || !in_array($method, $this->__allowed_http_methods) || !array_key_exists($method, $this->_method_args)) return NULL;
        if(is_null($element)){
            return $this->_method_args[$method];
        } elseif(array_key_exists($element, $this->_method_args[$method])){
            return $this->_method_args[$method][$element];
        }
        return false;
    }
    
    public function requestInfo(){
        return $this->_request;
    }
    
    public function callMethod($method_prefix = NULL, $parameters = NULL){
        if(is_null($method_prefix)) die;
        if(is_callable(array($this, $method_prefix.$this->_request['method']))){
            if(is_array($parameters)){
                call_user_func_array(array($this, $method_prefix.$this->_request['method']), $parameters);
            } elseif(!is_null($parameters)) {
                call_user_func(array($this, $method_prefix.$this->_request['method']), $parameters);
            } else {
                call_user_func(array($this, $method_prefix.$this->_request['method']));
            }
            return true;
        }
        return false;
    }
    
    public function setData($new_data = NULL){
        $this->setResponseData($new_data);
    }
    
    public function setResponseData($new_data = NULL){
        $this->_response_data = $new_data;
    }
    
    
    public function response($data = NULL, $http_code = NULL, $return = FALSE){
        if(is_null($data)){
            $data = $this->_response_data;
        }
        if(is_null($data) && is_null($http_code)){
            $http_code = 404;
            $output = NULL;
        } elseif(is_null($data) && is_numeric($http_code)){
            $output = NULL;
        } else {
            $format = !$this->_request['format'] || $this->_request['format'] === 'form' ? $this->__default_response_type : $this->_request['format'];
            is_numeric($http_code) || $http_code = 200;
            if(method_exists($this, '_format_'.$format)){
                header('Content-Type: '.$this->_supported_formats[$format].'; charset='.strtolower($this->__charset));
                $output = $this->{'_format_'.$format}($data);
            } elseif(method_exists(new Format_Translator, 'to_'.$format)){
                header('Content-Type: '.$this->_supported_formats[$format].'; charset='.strtolower($this->__charset));
                
                $output = $format !== "xml" ? Format_Translator::{'to_'.$format}($data) : Format_Translator::{'to_'.$format}($data, $this->__default_xml_wrapper);
            } else {
                $output = $data;
            }
        }
        http_response_code($http_code);
        header('Content-Length: '.strlen($output));
        if($return){
            return $output;
        } else {
            die($output);
        }
    }
    
    private function _init($config){
        if(is_array($config) || is_object($config)){
            foreach($config as $property => $value){
                if(property_exists($this, "__".$property)){
                    $this->{"__".$property} = $value;
                }
            }
        }
        $this->_request = array();
        $this->_request['method'] = $this->_find_request_method();
        $this->_request['ajax'] = $this->_is_ajax();
        $this->_request['ssl'] = $this->_find_ssl();
        $this->_request['port'] = $this->_find_request_port();
        $this->_request['current_url'] = $this->_find_current_url();
        $this->_request['querystring'] = $this->_find_querystring();
        $this->_request['format'] = $this->_find_input_format();
        $this->_request['inputstring'] = file_get_contents('php://input'); //storing this as a server variable since the input stream is only available for the first read.
    }
    
    private function _find_request_method(){
        $method = array_key_exists('HTTP_ACCESS_CONTROL_REQUEST_METHOD', $_SERVER) ? $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] : false;
        if($method === false){
            $method = array_key_exists('REQUEST_METHOD', $_SERVER) ? $_SERVER['REQUEST_METHOD'] : false;
        }
        $method = strtolower($method);
        if(in_array($method, $this->__allowed_http_methods) && method_exists($this, '_parse_'.$method)){
            return $method;
        }
        return 'get';
    }
    
    private function _is_ajax(){
        if(array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)){
            return (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        }
        return false;
    }
    
    private function _find_ssl(){
        return (isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']));
    }
    
    private function _find_current_url(){
        $url = 'http'.($this->_request['ssl'] ? 's' : '').'://';
        if(array_key_exists('SERVER_NAME', $_SERVER)){
            $url .= $_SERVER['SERVER_NAME'];
        } elseif(array_key_exists('HTTP_HOST', $_SERVER)){
            $url .= $_SERVER['HTTP_HOST'];
        } else {
            return false;
        }
        if(array_key_exists('REQUEST_URI', $_SERVER)){
            $url .= $_SERVER['REQUEST_URI'];
        }
        return $url;
    }
    
    private function _find_request_port(){
        return array_key_exists('SERVER_PORT', $_SERVER) ? $_SERVER['SERVER_PORT'] : false;
    }
    
    private function _find_querystring(){
        if(array_key_exists('QUERYSTRING', $_SERVER)){
            return $_SERVER['QUERYSTRING'];
        } elseif(strpos($this->_request['current_url'], '?') !== false){
            $url_parts = explode('?', $this->_request['current_url']);
            return urldecode($url_parts[1]);
        }
        return false;
    }
    
    private function _find_input_format(){
        if(array_key_exists('CONTENT_TYPE', $_SERVER) && !empty($_SERVER['CONTENT_TYPE'])){
            foreach($this->_supported_formats as $format => $mime){
                if(strpos($match = $_SERVER['CONTENT_TYPE'], ';')){
                    $match = current(explode(';', $match));
                }
                if($match === $mime){
                    return $format;
                }
            }
        }
        return NULL;
    }
    
    private function _parse_url_args(){
        if(!$this->_request['format']){
            if(!empty($this->_request['querystring'])){
                parse_str($this->_request['querystring'], $this->_url_args);
            }
        } else {
            $this->_url_args = Format_Translator::{'translate_'.$this->_request['format']}($this->_request['querystring']);
        }
    }
    
    private function _parse_get(){
        $this->_method_args['get'] = $this->_url_args;
    }
    
    private function _parse_post(){
        if(!$this->_request['format']){
            parse_str($this->_request['inputstring'], $this->_method_args['post']);
        } else {
            $this->_method_args['post'] = Format_Translator::{'translate_'.$this->_request['format']}($this->_request['inputstring']);
        }
    }
    
    private function _parse_put(){
        if(!$this->_request['format']){
            parse_str($this->_request['inputstring'], $this->_method_args['put']);
        } else {
            $this->_method_args['put'] = Format_Translator::{'translate_'.$this->_request['format']}($this->_request['inputstring']);
        }
    }
    
    private function _parse_head(){
        $this->_method_args['get'] = $this->_url_args;
    }
    
    private function _parse_options(){
        $this->_method_args['get'] = $this->_url_args;
    }
    
    private function _parse_delete(){
        if(!$this->_request['format']){
            parse_str($this->_request['inputstring'], $this->_method_args['delete']);
        } else {
            $this->_method_args['delete'] = Format_Translator::{'translate_'.$this->_request['format']}($this->_request['inputstring']);
        }
    }
}

class Format_Translator {
    private
        $raw_data = NULL
    ,   $translated = NULL
    ;
    
    public function __construct($data = NULL, $type = NULL){
        if(!is_null($data)){
            $this->raw_data = $data;
            if(!is_null($type) && method_exists($this, 'translate_'.$type)){
                $this->translated = $this->{'translate_'.$type}();
            }
        }
    }
    
    public function last_translation(){
        return $this->translated;
    }
    
    public static function translate_form($data = NULL){
        $static = !(isset($this) && get_class($this) == __CLASS__);
        if(is_null($data)){
            if($static) return $data;
            $data = $this->raw_data;
        }
        $result = NULL;
        parse_str($data, $result);
        if(!$static){
            $this->translated = $result;
            return $this->last_translation();
        } else {
            return $result;
        }
    }
    
    public static function translate_xml($data = NULL){
        $static = !(isset($this) && get_class($this) == __CLASS__);
        if(is_null($data)){
            if($static) return $data;
            $data = $this->raw_data;
        }
        $result = !empty($data) ? (array)simplexml_load_string(trim($data), 'SimpleXMLElement', LIBXML_NOCDATA) : [];
        if(!$static){
            $this->translated = $result;
            return $this->last_translation();
        } else {
            return $result;
        }
    }
    
    public static function translate_csv($data = NULL){
        $static = !(isset($this) && get_class($this) == __CLASS__);
        if(is_null($data)){
            if($static) return $data;
            $data = $this->raw_data;
        }
		$result = [];
		$rows = explode("\n", trim($data));
		$headings = explode(',', str_replace("\"", "", array_shift($rows)));
		foreach ($rows as $row){
			$data_fields = explode('","', trim(substr($row, 1, -1)));
			if (count($data_fields) == count($headings)){
				$result[] = array_combine($headings, $data_fields);
			}
		}
        if(!$static){
            $this->translated = $result;
            return $this->last_translation();
        } else {
            return $result;
        }
    }
    
    public static function translate_serialized($data = NULL){
        $static = !(isset($this) && get_class($this) == __CLASS__);
        if(is_null($data)){
            if($static) return $data;
            $data = $this->raw_data;
        }
        $result = unserialize(trim($data));
        if(!$static){
            $this->translated = $result;
            return $this->last_translation();
        } else {
            return $result;
        }
    }
    
    public static function translate_json($data = NULL){
        $static = !(isset($this) && get_class($this) == __CLASS__);
        if(is_null($data)){
            if($static) return $data;
            $data = $this->raw_data;
        }
        $result = json_decode(trim($data), true);
        if(!$static){
            $this->translated = $result;
            return $this->last_translation();
        } else {
            return $result;
        }
    }
    
    public static function to_querystring($data = NULL){
        if(is_null($data)) return false;
        $result = $data;
        if(!is_string($data)){
            $result = http_build_query($data);
        }
        return $result;
    }
    
    public static function to_xml($data = NULL , $default_wrapper = 'root'){
        if(is_null($data)) return false;
        $result = $data;
        if(!is_string($data)){
            $root = "<$default_wrapper/>";
            $data_keys = array_keys($data);
            if(count($data_keys) === 1){
                $root = '<'.$data_keys[0].'/>';
                $data = $data[$root];
            } else {
                $root = "<$default_wrapper/>";
            }
            $xml = new SimpleXMLElement($root);
            self::to_xml_helper($xml, $data);
            $result = $xml->asXML();
        }
        return $result;
    }
    
    private static function to_xml_helper(&$xml, $data){
        if(is_array($data)){
            foreach($data as $k => $v){
                if(is_array($v)){
                    $sub = $xml->addChild($k);
                    self::to_xml_helper($sub, $v);
                } else {
                    $xml->addChild($k, $v);
                }
            }
        }
    }
    
    public static function to_csv($data = NULL){
        if(is_null($data)) return false;
        $result = $data;
        if(!is_string($data)){
            if (isset($data[0]) && is_array($data[0])){
                $headings = array_keys($data[0]);
            } else {
                $headings = array_keys($data);
                $data = array($data);
            }
            $result = '"'.implode('","', $headings).'"'.PHP_EOL;
            foreach($data as &$row){
                foreach($row as $test){
                    if(is_array($test)) return false; //csv's don't allow multi-dimensional arrays
                }
                $row = str_replace('"', '""', $row); // Escape double quotes per RFC 4180
                $result .= '"'.implode('","', $row).'"'.PHP_EOL;
            }
        }
        return $result;
    }
    
    public static function to_serialized($data = NULL){
        if(is_null($data)) return false;
        $result = $data;
        if(!is_string($data)){
            $result = serialize($data);
        }
        return $result;
    }
    
    public static function to_json($data = NULL){
        if(is_null($data)) return false;
        $result = $data;
        if(!is_string($data)){
            $result = json_encode($data);
        }
        return $result;
    }
}