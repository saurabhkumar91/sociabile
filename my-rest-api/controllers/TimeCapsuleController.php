<?php

class TimeCapsuleController {
    
    /**
     * Method for creating time capsule 
     * @param $header_data array of header data
     * @param $post_data array of post data(capsule_text,capsule_recipients,capsule_time) 
     * @author Saurabh Kumar
     * @return json
     */
    
    public function createTimeCapsuleAction( $header_data, $post_data ){
        if( !isset($post_data["capsule_text"]) || !isset($post_data["capsule_recipients"]) || !isset($post_data["capsule_time"]) ){
            Library::logging('alert',"API : createTimeCapsule : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
            return;
        }
        try{
            if($header_data['os'] == 1) {
                $post_data["capsule_recipients"] =  json_decode($post_data["capsule_recipients"]);
            }
            $timeCapsule                        = new TimeCapsules();
            $timeCapsule->user_id               = $header_data["id"];
            $timeCapsule->capsule_text          = $post_data["capsule_text"];
            $timeCapsule->capsule_recipients    = $post_data["capsule_recipients"];
            $timeCapsule->capsule_time          = $post_data["capsule_time"];
            $timeCapsule->capsule_opened_by     = array();
            $timeCapsule->capsule_image         = array();
            $timeCapsule->date                  = time();
            if ( $timeCapsule->save() ) {
                    $result['capsule_id']           = (string)$timeCapsule->_id;
                    Library::output(true, '1', TIME_CAPSULE_SAVED, $result);
            } else {
                $errors = array();
                foreach ($timeCapsule->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                Library::logging('error',"API : createTimeCapsule : ".$errors." user_id : ".$header_data['id']);
                Library::output(false, '0', $errors, null);
            }            
        } catch (Exception $e) {
            Library::logging('alert',"API : createTimeCapsule : ".$e." : user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
    }
    
    /**
     * Method to get list of time capsules for logged in user
     * @param $header_data array of header data
     * @author Saurabh Kumar
     * @return json
     */
    
    public function getTimeCapsuleAction( $header_data ){
        try{
            $result         = array();
            $capsuleCount   = 0;
            $users          = Users::findById( $header_data["id"] );
            if( !isset($users->username) ){
                $users->username   = "";
            }
            //$timeCapsules   = TimeCapsules::find( array("conditions"=>array( "user_id"=>))  );
            $db             = Library::getMongo();
            $timeCapsules   = $db->execute('return db.time_capsules.find( { $or : [{ "user_id" : "'.$header_data["id"].'" }, { "capsule_recipients" : "'.$header_data["id"].'" } ]} ).sort( { date: -1 } ).toArray()');
                if( $timeCapsules['ok'] == 0 ) {
                    Library::logging('error',"API : getTimeCapsule, mongodb error: ".$timeCapsules['errmsg']." : user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
            if( empty($timeCapsules["retval"]) ){
                    Library::output(true, '0', "No Result Found.", null);
            }
            foreach( $timeCapsules["retval"] AS $timeCapsule ){
                if( $timeCapsule["user_id"] == $header_data["id"] ){
                    if(in_array($header_data["id"], $timeCapsule["capsule_recipients"])){
                        $capsuleType    = 1;  // user is receipent
                    }else{
                        $capsuleType    = 0;  // user is sender
                    }
                    $sender         = $users->username;
                }else{
                    $senderRes  = $db->execute('return db.users.find({"_id" : ObjectId("'.$timeCapsule["user_id"].'")}, {username:1}).toArray()');
                    if( $senderRes['ok'] == 0 || empty($senderRes["retval"]) ) {
                        $errorMessage   = ($senderRes['ok'] == 0) ? $senderRes['errmsg'] : "User(".$timeCapsule->user_id.") Not found";
                        Library::logging('error',"API : getTimeCapsule, mongodb error: ".$errorMessage." : user_id : ".$header_data['id']);
                        Library::output(false, '0', ERROR_REQUEST, null);
                    }
                    $sender         = $senderRes["retval"][0]["username"];
                    $capsuleType    = 1;
                    
                }
                foreach ($timeCapsule["capsule_image"] as &$value){
                    $value  = FORM_ACTION.$value;                
                }
                $result[$capsuleCount]['username']              = $sender;
                $result[$capsuleCount]['capsule_id']            = (string)$timeCapsule["_id"];
                $result[$capsuleCount]['capsule_text']          = $timeCapsule["capsule_text"];
                $result[$capsuleCount]['capsule_image']         = $timeCapsule["capsule_image"];
                $result[$capsuleCount]['capsule_recipients']    = $timeCapsule["capsule_recipients"];
                $result[$capsuleCount]['capsule_time']          = $timeCapsule["capsule_time"];
                $result[$capsuleCount]['capsule_opened_by']     = $timeCapsule["capsule_opened_by"];
                $result[$capsuleCount]['creation_time']         = $timeCapsule["date"];
                $result[$capsuleCount]['capsule_type']          = $capsuleType;
                $capsuleCount++;
            }
            
            // Time capsule sorted by Open Date. Oldest dates appearing first. This means that opened capsules will be at the top and then yet to be open capsules will be displayed
            usort($result, function($a, $b){
                $openTimeA  = $a["creation_time"];
                $openTimeB  = $b["creation_time"];
                if( empty($a['capsule_opened_by']) && !empty($b['capsule_opened_by']) ){
                    $openTimeB  = 0; 
                }elseif( empty($b['capsule_opened_by']) && !empty($a['capsule_opened_by']) ){
                    $openTimeA  = 0;
                }elseif( !empty($b['capsule_opened_by']) && !empty($a['capsule_opened_by']) ){
                    $openedByA  = $a['capsule_opened_by'];
                    usort($openedByA, function($openDetailx, $openDetaily){
                        if ($openDetailx["time"] == $openDetaily["time"]) {
                            return 0;
                        }
                        return ($openDetailx["time"] < $openDetaily["time"]) ? -1 : 1;
                    });  
                    $openTimeA  = $openedByA[0]["time"];
                    
                    $openedByB  = $b['capsule_opened_by'];
                    usort($openedByB, function($openDetailx, $openDetaily){
                        if ($openDetailx["time"] == $openDetaily["time"]) {
                            return 0;
                        }
                        return ($openDetailx["time"] < $openDetaily["time"]) ? -1 : 1;
                    });  
                    $openTimeB  = $openedByB[0]["time"];
                }
                if ($openTimeA == $openTimeB) {
                    return 0;
                }
                return ($openTimeA < $openTimeB) ? -1 : 1;
            });    
            
            Library::output(true, '1', "No Error", $result);
        } catch (Exception $ex) {
            Library::logging('alert',"API : createTimeCapsule : ".$ex." : user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
    }
    
    /**
     * Method for opening time capsule 
     * @param $header_data array of header data
     * @param $post_data array of post data(capsule_id) 
     * @author Saurabh Kumar
     * @return json
     */
    
    public function openTimeCapsuleAction( $header_data, $post_data ){
        if( !isset($post_data["capsule_id"]) ){
            Library::logging('alert',"API : openTimeCapsule : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
            return;
        }
        try{
            $timeCapsule    = TimeCapsules::findById( $post_data["capsule_id"] );
            if( !$timeCapsule ){
                Library::logging('alert',"API : openTimeCapsule : ".ERROR_INPUT.": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_INPUT, null);
            }
            $timeCapsule->capsule_opened_by[]  = array( "user_id"=>$header_data['id'], "time"=>time() );
            if ( $timeCapsule->save() ) {
                    Library::output(true, '1', TIME_CAPSULE_OPENED, null);
            } else {
                $errors = array();
                foreach ($timeCapsule->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                Library::logging('error',"API : openTimeCapsule : ".$errors." user_id : ".$header_data['id']);
                Library::output(false, '0', $errors, null);
            }            
        } catch (Exception $e) {
            Library::logging('alert',"API : openTimeCapsule : ".$e." : user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
    }
    
    /**
     * Method for deleting time capsule 
     * @param $header_data array of header data
     * @param $post_data array of post data(capsule_id) 
     * @author Saurabh Kumar
     * @return json
     */
    
    public function deleteTimeCapsuleAction( $header_data, $post_data ){
        if( !isset($post_data["capsule_id"]) ){
            Library::logging('alert',"API : deleteTimeCapsule : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
            return;
        }
        try{
            $timeCapsule    = TimeCapsules::findById( $post_data["capsule_id"] );
            if( !$timeCapsule ){
                Library::logging('alert',"API : deleteTimeCapsule : ".ERROR_INPUT.": user_id : ".$header_data['id']." : capsule_id : ".$post_data["capsule_id"]);
                Library::output(false, '0', "This Time Capsule Does Not Exists.", null);
            }
            if( /*$timeCapsule->user_id !== $header_data['id'] &&*/ !in_array( $header_data['id'], $timeCapsule->capsule_recipients ) ){
                Library::logging('error',"API : deleteTimeCapsule : ".TIME_CAPSULE_DELETE_AUTH_ERR." : user_id : ".$header_data['id'].", capsule_id: ".$post_data['capsule_id']);
                Library::output(false, '0', TIME_CAPSULE_DELETE_AUTH_ERR, null);
            }
            $openedFlag = false;
            if(!empty($timeCapsule->capsule_opened_by)){
                foreach( $timeCapsule->capsule_opened_by AS $openedBy ){
                    if( $openedBy["user_id"] == $header_data['id'] ){
                        $openedFlag = true;
                        break;
                    }
                }
            }
            if( !$openedFlag ){
                Library::logging('error',"API : deleteTimeCapsule : ".TIME_CAPSULE_NOT_OPENED." : user_id : ".$header_data['id'].", capsule_id: ".$post_data['capsule_id']);
                Library::output(false, '0', TIME_CAPSULE_NOT_OPENED, null);
            }
            $db     = Library::getMongo();
//            if( $timeCapsule->user_id == $header_data['id'] ){
//                require 'components/S3.php';
//                $s3         = new S3(AUTHKEY, SECRETKEY);
//                $bucketName = S3BUCKET; 
//                foreach( $timeCapsule->capsule_image AS $image){
//                    if ( ! $s3->deleteObject($bucketName, $image) ) {
//                        Library::logging('error',"API : deleteTimeCapsule : Image Not Deleted From S3 Server : user_id : ".$header_data['id'].", post_id: ".$post_data['post_id']);
//                        Library::output(false, '0', TIME_CAPSULE_NOT_DELETED, null);
//                    }
//                }
//                $res    = $db->execute('db.time_capsules.remove({"_id" : ObjectId("'.$post_data['capsule_id'].'")})');
//                if( empty($res['retval']["nRemoved"]) ) {
//                    Library::logging('error',"API : deleteTimeCapsule, mongodb error: ".$res['errmsg']." : user_id : ".$header_data['id']);
//                    Library::output(false, '0', TIME_CAPSULE_NOT_DELETED, null);
//                }
//            }else{
                $update = $db->execute('db.time_capsules.update( {"_id" : ObjectId("'.$post_data['capsule_id'].'")}, { $pull: { capsule_recipients: "'.$header_data['id'].'" } } )');
                if( $update['ok'] == 0 ){
                    Library::logging('error',"API : deleteTimeCapsule, mongodb error: ".$update['errmsg']." : user_id : ".$header_data['id']);
                    Library::output(false, '0', TIME_CAPSULE_NOT_DELETED, null);
                }
//            }
            Library::output(true, '0', TIME_CAPSULE_DELETED, null);
            
        } catch (Exception $e) {
            Library::logging('alert',"API : deleteTimeCapsule : ".$e." : user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
    }
    
    /**
     * Method to set multiple images on a Time Capsule
     * @param $header_data array of header data
     * @param $post_data array of post data(capsule_id) 
     * @author Saurabh Kumar
     * @return json
     */
    
    public function setTimeCapsuleImagesAction($header_data,$post_data)
    {
        if( empty($_FILES["images"]['name']) || !isset($post_data["capsule_id"]) ) {
            Library::logging('alert',"API : setTimeCapsuleImages : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            $timeCapsules   = TimeCapsules::findById($post_data["capsule_id"]);
            if( !$timeCapsules ){
                Library::logging('error',"API : setTimeCapsuleImages : ".INVALID_CAPSULE." : user_id : ".$header_data['id']);
                Library::output(false, '0', INVALID_CAPSULE, null);
            }
            foreach( $_FILES["images"]['name'] As $key=>$value ){
                $post_data['images'][]  = array( "name"=>$value, "tmp_name"=>$_FILES["images"]["tmp_name"][$key]) ;
            }
            try {
                foreach( $post_data['images'] As $image ){
                    $uploadFile = rand().$image["name"];
                    $amazon     = new AmazonsController();
                    $amazonSign = $amazon->createsignatureAction($header_data,10);
                    $url        = $amazonSign['form_action'];
                    $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                    $ext        = explode(".", $uploadFile);
                    $extension  = trim(end($ext));
                    if( !in_array($extension, array("jpeg", "png", "gif"))){
                        $extension  = "jpeg";
                    }
                    $postfields = array(
                        "key"                       => "uploaded/".$uploadFile,
                        "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                        "acl"                       => $amazonSign["acl"],
                        "success_action_redirect"   => $amazonSign["success_action_redirect"],
                        "policy"                    => $amazonSign["policy"],
                        "signature"                 => $amazonSign["signature"],
                        "Content-Type"              => "image/$extension",
                        "file"                      => file_get_contents($image["tmp_name"])
                    );
                    $ch = curl_init();
                    $options = array(
                        CURLOPT_URL         => $url,
                        CURLOPT_POST        => 1,
                        CURLOPT_HTTPHEADER  => $headers,
                        CURLOPT_POSTFIELDS  => $postfields,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_RETURNTRANSFER => true
                    ); // cURL options
                    curl_setopt_array($ch, $options);
                    $imageName      = curl_exec($ch);
                    curl_close($ch);
                    $timeCapsules->capsule_image[]  = $imageName;
                }
                if ( $timeCapsules->save() == false ) {
                    foreach ($timeCapsules->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : setTimeCapsuleImages : ".$errors." : user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                }
                Library::output(true, '1', TIME_CAPSULE_IMAGE, null);
            } catch (Exception $e) {
                Library::logging('error',"API : setTimeCapsuleImages : ".$e." ".": user_id : ".$header_data["id"]);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
}
