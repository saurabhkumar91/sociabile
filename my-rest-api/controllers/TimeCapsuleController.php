<?php
 
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Logger\Adapter\File as FileAdapter;

class TimeCapsuleController {
    //put your code here
    function createTimeCapsuleAction( $header_data, $post_data ){
        if( !isset($post_data["capsule_text"]) || !isset($post_data["capsule_recipients"]) || !isset($post_data["capsule_time"]) ){
            Library::logging('alert',"API : createTimeCapsule : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
            return;
        }
        try{
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
//                    $result['capsule_text']         = $timeCapsule->capsule_text;
//                    $result['capsule_recipients']   = $timeCapsule->capsule_recipients;
//                    $result['capsule_time']         = $timeCapsule->capsule_time;
//                    $result['capsule_opened_by']       = $timeCapsule->capsule_opened_by;
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
    
    function getTimeCapsuleAction( $header_data ){
        try{
            $timeCapsules   = TimeCapsules::find( array("conditions"=>array( "user_id"=>$header_data["id"]))  );
            $result         = array();
            $capsuleCount   = 0;
            foreach( $timeCapsules AS $timeCapsule ){
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
