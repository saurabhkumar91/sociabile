<?php

use Phalcon\Mvc\Controller;
use Phalcon\Logger\Adapter\File as FileAdapter;


class ControllerBase extends Controller
{
    
   /**
    * Method for defining global variable
    *
    * @param object request params
    * @param object reponse object
    *
    * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
    * @return void
    */
	
    protected function initialize()
    {
        
        // user related messages
        define('ERROR_INPUT', 'Please provide all input values.');
        define('ERROR_REQUEST', 'Error in request. Please try again');
        define('USER_NOT_REGISTERED', 'Invalid User');
        define('CONTACTS_SAVED', 'All The Contacts Has Been Saved Successfully.');
        define('USER_NAME_SAVED', 'User Name Saved Successfully.');
        
        
        // otp relaed messages
        define('OTP_SENT', 'OTP Sent Successfully');
        define('OTP_VERIFIED', 'OTP Verified.');
        define('OTP_WRONG', 'OTP Not Verified.');
        
        // authentication
        define('KEY', 'JUTdqn7yMq5BjrQoiDo6kbYHymcoaWmbR5mlbEt');
        define('TOKEN_MSG','Token Generated Successfully.');
        define('TOKEN_WRONG','Token Mismatch.');
        define('WRONG_OS_VERSION','Wrong Os OR Version');
        
        
        // check for authentication using random token
        $action = array('registration','generateToken');
        $os = array('1','2');
        $version = array('1.0');
        
        if(!in_array($this->dispatcher->getActionName(), $action)) {
            try{
                $user_id = $this->request->get("user_id");
                //$token = $this->request->get("token");
                $token = getallheaders();
                if(isset($token['token'])) {
                    $token = $token['token'];
                    if((in_array($this->request->get("os"), $os)) && (in_array($this->request->get("version"), $version))) {
                        $user = Users::findById($user_id);
                        if($user) {
                             if($user->hash == $token) {
                            //echo "ok";die;    
                            } else {
                                $this->outputAction(false, '0', TOKEN_WRONG, null);
                            }
                        } else {
                            $this->loggingAction('error',"API : Middleware ".USER_NOT_REGISTERED);
                            $this->outputAction(false, '0', USER_NOT_REGISTERED, null);
                        }

                    } else {
                         $this->loggingAction('error',"API : Middleware ".WRONG_OS_VERSION." "."user_id : ".$user_id);
                        $this->outputAction(false, '0', WRONG_OS_VERSION, null);
                    }
                } else {
                    $this->loggingAction('error',"API : Middleware ".TOKEN_WRONG." "."user_id : ".$user_id);
                    $this->outputAction(false, '0', TOKEN_WRONG, null);
                }
              
            } catch(Exception $e) {
                $this->loggingAction('error',"API : Middleware ".$e);
                $this->outputAction(false, '0', "worng user id", null);
            }
        } 
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
	
    public function outputAction($success,$error,$msg,$result)
     {
     	
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
     
   /**
    * Method for logger
    *
    * @param object request params
    * @param object reponse object
    *
    * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
    * @return json
    */
	
    public function loggingAction($type,$error)
    {
        $logger = new FileAdapter("../app/logs/error.log");
        $logger->$type($error);
    }
}
