<?php
//Example controller utilizing the rest_server
require_once 'REST_Server.php';

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