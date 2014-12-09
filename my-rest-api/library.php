<?php
require_once('loader.php');
require_once('bootstrap.php');
use Phalcon\Logger\Adapter\File as FileAdapter;

 class Library {
    
   /**
    * Method for authentication
    *
    * @param object request params
    * @param object reponse object
    *
    * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
    * @return json
    */
     
     static function auth() {
         
        $action = array('registration','generateToken');
        $os = array('1','2');
        $version = array('1.0'); 
        
        $param = getallheaders();
        
        $api_name = explode('/', $_SERVER['QUERY_STRING']);
        $api_name = $api_name[1];
        
        if((in_array($param['os'], $os)) && (in_array($param['version'], $version))) {
            if(!in_array($api_name, $action)) {
                try {
                    if(isset($param['token']) && isset($param['user_id'])) {
                        $token = $param['token'];
                        $user = Users::findById($param['user_id']);
                        if($user) {
                             if($user->hash == $token) {
                            //echo "ok";die;    
                            } else {
                                self::output(false, '0', TOKEN_WRONG, null);
                            }
                        } else {
                            self::logging('error',"API : Middleware ".USER_NOT_REGISTERED);
                            self::output(false, '0', USER_NOT_REGISTERED, null);
                        }

                    } else {
                        self::logging('error',"API : Middleware ".HEADER_INFO);
                        self::output(false, '0', HEADER_INFO, null);
                    }

                } catch(Exception $e) {
                    self::logging('error',"API : Middleware ".$e);
                    self::output(false, '0', "worng user id", null);
                }
            }
        } else {
            self::logging('error',"API : Middleware ".WRONG_OS_VERSION);
            self::output(false, '0', WRONG_OS_VERSION, null);
        }
        

    }
    
    
   /**
    * Method for logger
    *
    * @param object request params
    * @param object reponse object
    *
    * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
    * @return json
    */
    
    static function logging($type,$error){
        $logger = new FileAdapter("../my-rest-api/error.log");
        $logger->$type($error);
    }
    
   /**
    * Method for returning json to client
    *
    * @param object request params
    * @param object reponse object
    *
    * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
    * @return json
    */
    
    static function output($success,$error,$msg,$result) {
        $response = new \Phalcon\Http\Response();
    	
    	//Set status code
    	$response->setRawHeader("HTTP/1.1 200 OK");
    	
    	//Set the content of the response
    	if($result == null) {
    		$response->setJsonContent(array("is_success" => $success,"error_code" => $error,"message" => $msg));
    	} else {
    		$response->setJsonContent(array("is_success" => $success,"error_code" => $error,"message" => $msg,"result" => $result));
    	}
    	
    	//Send response to the client
    	$response->send();exit;
    }
}

Library::auth();


