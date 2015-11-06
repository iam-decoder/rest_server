# REST_Server

→ Author: Travis Neal

→ License: [GNU GPL v3](http://www.gnu.org/licenses/gpl.html)

→ Web: [https://github.com/iam-decoder](https://github.com/iam-decoder)

## About
A Plug-and-play REST Controller with CORS support meant to fit in most PHP implemented servers.

This is a plug-and-play library meant to fit most web applications utilizing PHP as the server-sided scripting language. However, for best results, the use of an Apache-based server is desired. If you plan to use this library on a non-apache-based server, please replace values being returned to the `_request` property branching off of the `_init` method with your server's version of the variables (or use your own clever way to get the necessary result).

## Usage
* Copy the file `/src/REST_Server.php` to your application
* Include the file prior to calling the REST_Server class (`require_once`)
* Extend a class/controller that you want the REST_Server to officiate

#### _→ example_
```php
/*
 * For servers using full classes as endpoints
 */
require_once('REST_Server.php');
class api_endpoint extends REST_Server {

  public function __construct(){
    parent::__constructor();
    $this->callMethod('methodPrefix_');
  }
  
  public function methodPrefix_get(){
    //this method is only called in a get request
    $this->response("You've reached the methodPrefix_get method");
  }
  
  public function methodPrefix_post(){
    //this method is only called in a post request
    $this->response("You've reached the methodPrefix_post method");
  }
}
```

### OR

```php
/*
 * For servers using class methods as endpoints
 */
require_once('REST_Server.php');
class api_endpoint extends REST_Server {

  public function __construct(){
    parent::__constructor();
  }
  
  public function calledMethod(){
    //reached by this method being called from somewhere
    $this->callMethod('methodPrefix_');
  }
  
  public function methodPrefix_get(){
    //this method is only called in a get request
    $this->response("You've reached the methodPrefix_get method");
  }
  
  public function methodPrefix_post(){
    //this method is only called in a post request
    $this->response("You've reached the methodPrefix_post method");
  }
}
```

## Methods

Available methods to be called from the extended class

### __construct($config = `NULL`)

The object contructor accepts either no parameters or an array of configuration variables. If no parameters are passed, the default options will be used (default options can be changed by overwriting the property values in the class that are prefixed with 2 underscores (`__`). If you'd like to send the configuration settings through the constructor, you can pass in an associative array with the key being the name of the changeable property and the value being the new value to be used rather than the default. The changeable properties are:

| Property Name              | Default Value                                              | Description                                                                                                                                    |
|----------------------------|------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------|
| **default_response_type**  | `"json"`                                                   | If the request was made using a non-recognized data format, or if the format is an encoded form string, the default response type will be used |
| **charset**                | `"utf-8"`                                                  | Defines the charset header value                                                                                                               |
| **default_xml_wrapper**    | `"ApplicationResponse"`                                    | When responding in XML format, data needs to be wrapped in a root node, if one is not provided, the default wrapper will be used.              |
| **ajax_only**              | `true`                                                     | If this is true, only requests with the x-requested-with header and value of xmlhttprequest will be allowed                                    |
| **allowed_http_origins**   | `array('*')`                                               | Specifies which http origin(s) a request can come from                                                                                         |
| **allowed_http_methods**   | `array('get', 'delete', 'post', 'put', 'options', 'head')` | Specifies which http request methods are allowed                                                                                               |
| **allowed_http_headers**   | `array('origin', 'x-requested-with', 'content-type')`      | Specifies which headers are allowed in requests                                                                                                |
| **allow_http_credentials** | `true`                                                     | Specifies that the server accepts cookies in requests                                                                                          |
#### _→ example_
```php
public function __construct(){
  parent::__constructor(
    array(
      "ajax_only" => false
    , "default_response_type" => "form"
    )
  );
}
```
-
### requestData($method, $element = `NULL`)

Retrieves data for the provided request method type. If an element is specified, it will search through the parsed data looking for the element, if it doesn't exist then it will return `false`. If an element is **not** specified, all data associated to the method will be returned as an array.

#### _→ example_
```php
public function someMethod(){

  $this->requestData('get');
  //returns all get variables as an array

  $this->requestData('post', 'name');
  /* if the 'name' element exists in the post
   * data, the previous line will return its
   * value, otherwise it will return a
   * boolean false.
   */
}
```
-
### requestInfo()

Returns information about the request such as whether the request was made using AJAX, the request method being used (like get, post, etc.), if the request accessed the Secure Sockets Layer (SSL), etc.

#### _→ example_
```php
public function someMethod(){
  $this->requestInfo();
}
```
-
### callMethod($method_prefix = `NULL`, $parameters = `NULL`)

Calls a method in the current class inheritance the begins with `$method_prefix` and ends with the current request method. If parameters are included in the call, it will attach them to the method call. For instance if you wanted to pass a value that was calculated on the server to the method you could add it to the statement like so:
#### _→ example_
```php
public function foo(){
  $this->callMethod('bar_', 'baz');
}
public function bar_get($singleParameter){
  echo $singleParameter; //outputs: baz
}
```
If you wanted to pass more than 1 parameter you would pass an indexed array of those parameters to the callMethod method
#### _→ example_
```php
public function foo(){
  $this->callMethod('bar_', array('baz', 'qux'));
}
public function bar_get($firstParameter, $secondParameter){
  echo $firstParameter." -> ".$secondParameter; //outputs: baz -> qux
}
```
-
### setResponseData($new_data = `NULL`)

Sets the data to be used when generating the response. Use this if you wish to separate the data from the final `response()` statement.
#### _→ example_
```php
public function someMethod(){
  $data = array("name" => "Travis Neal");
  $this->setResponseData($data);
  $this->response();
}
```
### setData($new_data = `NULL`)

alias for the `setResponseData` method.

-
### response($data = `NULL`, $http_code = `NULL`, $return = `FALSE`)

Outputs data formatted to follow how the request was generated. For example, if the request was originated with `XML` content, `XML` content will be returned. If it cannot determine the correct format, then the `__default_response_type` property value will be used

Accepts 0 to 3 parameters:
* The first parameter should either be `NULL` or an array of data. If `NULL` is passed, it will check if the data was previously set using the `setResponseData` method, if both are `NULL`, then nothing will be output for the request, however, the status code will be set.
* The second parameter can either be `NULL` or a numeric value corresponding to the HTTP Status code for the request ([See Status Code Definitions](http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html)). If this is set to `NULL` it will be translated to status `200 OK`.
* The third parameter should be a boolean value of either `TRUE` or `FALSE`, if this is set to `TRUE` then the formatted data (if any) will be returned and the HTTP Status header will be set, but the request will continue. If it's set to `FALSE` then the request will end, and the formatted data will be output for reading and the status code will be set.

#### _→ example_
```php
public function someMethod(){
  $data = array("name" => "Travis Neal");
  $this->response($data, 100); //outputs the formatted data and terminates the request
}
```
## Changelog
### _v1.0.1_
* General
  * Changed license from GNU LGPL v2.1 to GNU GPL v3
  * Added license headers to all files
* Bug Fixes
  * Fixed CSV responses
### _v1.0.0_
> Initial upload