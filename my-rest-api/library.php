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
        //ini_set("display_errors",1);
        //error_reporting(E_ALL^ E_NOTICE);
        $action = array('registration','generateToken');
        $os = array('1','2');
        $version = array('1.0'); 
        $param = self::getallheaders();
        $db = self::getMongo();
        //print_r(json_encode($_POST));die;
        $request = 'db.requests.insert({ 
                            api_name: "'.$_SERVER['REQUEST_URI'].'", 
                            method: "'.$_SERVER['REQUEST_METHOD'].'",
                            os: "'.$_SERVER['HTTP_OS'].'",
                            version: "'.$_SERVER['HTTP_VERSION'].'",
                            user_id: "'.$_SERVER['HTTP_ID'].'",
                            user_agent: "'.$_SERVER['HTTP_USER_AGENT'].'",
                            ip: "'.$_SERVER['REMOTE_ADDR'].'"
                    })';
        $result =  $db->execute($request);
        
        if($result['ok'] == 0) {
            Library::logging('error',"API : request log mongodb error: ".$result['errmsg']." ".": user_id : ".$param['id']);
        }
        //die;
        $api_name = explode('/', $_SERVER['REQUEST_URI']);
        //$api_name = end($api_name);
        if( trim(strtolower($api_name[1]))=="sociabileapi"){
            $api_name = $api_name[3];
        }else{
            $api_name = $api_name[1];
        }
        if($api_name == 'getStatus') {
            
        } elseif((in_array($param['os'], $os)) && (in_array($param['version'], $version))) {
            if(!in_array($api_name, $action)) {
                try {
                    if(isset($param['token']) && isset($param['id'])) {
                        $token = $param['token'];
                        $user = Users::findById($param['id']);
                        if( $user && (in_array($api_name, array("codeVerification", "setDeviceToken")) || ($user->is_active && $user->is_deleted==0)) ) {
                             if($user->hash == $token) {
                            //echo "ok";die;    
                            } else {
                                self::output(false, '0', TOKEN_WRONG, null);
                            }
                        } else {
                            self::logging('error',"API : Middleware: ".USER_NOT_REGISTERED." user_id : ".$param['id']);
                            self::output(false, '0', USER_NOT_REGISTERED, null);
                        }
                    } else {
                        self::logging('error',"API : Middleware: ".HEADER_INFO." user_id : ".$param['id'] );
                        self::output(false, '0', HEADER_INFO, null);
                    }

                } catch(Exception $e) {
                    self::logging('error',"API : Middleware: ".$e->getMessage());
                    self::output(false, '0', "wrong user id", null);
                }
            }
        } else {
            self::logging('error',"API : Middleware ".WRONG_OS_VERSION." user_id : ".$param['id']);
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
    	//$response->setRawHeader("HTTP/1.1 200 OK");
    	
    	//Set the content of the response
    	if(!is_array($result)) {
            $response->setJsonContent(array("is_success" => $success,"error_code" => $error,"message" => $msg));
    	} else {
            $response->setJsonContent(array("is_success" => $success,"error_code" => $error,"message" => $msg,"result" => $result));
    	}
    	
    	//Send response to the client
    	$response->send();exit;
    }
    
    
     static function getallheaders()
    {
        $headers = '';
       foreach ($_SERVER as $name => $value)
       {
           if (substr($name, 0, 5) == 'HTTP_')
           {
               $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
           }
       }
       $headers = array_change_key_case( $headers , CASE_LOWER);
       return $headers;
    }
    
    static function  getMongo() 
    {
        $mongo = new MongoClient();
        $db = $mongo->Sociabile;
        return $db;
    }
    
    static function getOTP( $digits='4' ){
        return rand(pow(10, $digits-1), pow(10, $digits)-1);        
    }
    
    static function sendMail( $receiver, $message, $subject='' ){
        ini_set("smtp_server", "smtp.gmail.com");            
        ini_set("smtp_port", 465);
        ini_set("auth_username", "test.sociabile@gmail.com");
        ini_set("sendmail_from", "test.sociabile@gmail.com");
        ini_set("auth_password", "sociabile@1");


        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        return mail( $receiver[0], $subject, $message, $headers );
        
    }

}

Library::auth();


