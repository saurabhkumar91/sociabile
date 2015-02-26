<?php

class TimeCapsuleController {
    
    /**
     * Method for creating time capsule 
     * @param $header_data array of header data
     * @param $post_data array of post data(capsule_text,capsule_recipients,capsule_time) 
     * @author Saurabh Kumar
     * @return json
     */
    
    function createTimeCapsuleAction( $header_data, $post_data ){
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
        exit("test");
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
    
    function getTimeCapsuleAction( $header_data ){
        try{
            $timeCapsules   = TimeCapsules::find( array("conditions"=>array( "user_id"=>$header_data["id"]))  );
            $result         = array();
            $capsuleCount   = 0;
            $users          = Users::findById( $header_data["id"] );
            if( !isset($users->username) ){
                $users->username   = "";
            }
            foreach( $timeCapsules AS $timeCapsule ){
                foreach ($timeCapsule->capsule_image as &$value){
                    $value  = FORM_ACTION.$value;                
                }
                $result[$capsuleCount]['username']              = $users->username;
                $result[$capsuleCount]['capsule_id']            = (string)$timeCapsule->_id;
                $result[$capsuleCount]['capsule_text']          = $timeCapsule->capsule_text;
                $result[$capsuleCount]['capsule_image']         = $timeCapsule->capsule_image;
                $result[$capsuleCount]['capsule_recipients']    = $timeCapsule->capsule_recipients;
                $result[$capsuleCount]['capsule_time']          = $timeCapsule->capsule_time;
                $result[$capsuleCount]['capsule_opened_by']     = $timeCapsule->capsule_opened_by;
                $result[$capsuleCount]['creation_time']         = $timeCapsule->date;
                $result[$capsuleCount]['capsule_type']          = 0;
                $capsuleCount++;
            }
            $timeCapsules   = TimeCapsules::find( array("conditions"=>array( "capsule_recipients"=>$header_data["id"]))  );
            foreach( $timeCapsules AS $timeCapsule ){
                foreach ($timeCapsule->capsule_image as &$value){
                    $value  = FORM_ACTION.$value;                
                }
                $result[$capsuleCount]['username']              = $users->username;
                $result[$capsuleCount]['capsule_id']            = (string)$timeCapsule->_id;
                $result[$capsuleCount]['capsule_text']          = $timeCapsule->capsule_text;
                $result[$capsuleCount]['capsule_image']         = $timeCapsule->capsule_image;
                $result[$capsuleCount]['capsule_recipients']    = $timeCapsule->capsule_recipients;
                $result[$capsuleCount]['capsule_time']          = $timeCapsule->capsule_time;
                $result[$capsuleCount]['capsule_opened_by']     = $timeCapsule->capsule_opened_by;
                $result[$capsuleCount]['creation_time']         = $timeCapsule->date;
                $result[$capsuleCount]['capsule_type']          = 1;
                $capsuleCount++;
            }
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
    
    function openTimeCapsuleAction( $header_data, $post_data ){
        if( !isset($post_data["capsule_id"]) ){
            Library::logging('alert',"API : capsuleOpened : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
            return;
        }
        try{
            $timeCapsule    = TimeCapsules::findById( $post_data["capsule_id"] );
            if( !$timeCapsule ){
                Library::logging('alert',"API : capsuleOpened : ".ERROR_INPUT.": user_id : ".$header_data['id']);
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
                Library::logging('error',"API : capsuleOpened : ".$errors." user_id : ".$header_data['id']);
                Library::output(false, '0', $errors, null);
            }            
        } catch (Exception $e) {
            Library::logging('alert',"API : capsuleOpened : ".$e." : user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }
    }
    
}
