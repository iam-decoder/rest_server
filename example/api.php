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
 * This is simply an example controller meant to show a few of the
 * methods being used, this file is not necessary for the REST_Server
 * class to work properly.
 * 
 */

require_once '../src/REST_Server.php';

class api extends REST_Server {
    
    public function __construct(){
        if(get_parent_class()){
            parent::__construct();
        }
        $this->callMethod('api_');
    }
    
    public function api_get(){
        echo('get function was called.<br/>');
        $this->response($this->requestData('get'));
    }
    
    public function api_delete(){
        echo('delete function was called.<br/>');
        $this->response($this->requestData('delete'));
    }
    
    public function api_post(){
        echo('post function was called.<br/>');
        $this->setData(array("request" => $this->requestData('post'), "extra" => array("test1" => "a", "test2" => "b"))); //test for multi-dimensional conversions
        $this->response();
    }
    public function api_put(){
        echo('put function was called.<br/>');
        $this->setData($this->requestData('put'));
        $this->response();
    }
}

$api = new api;