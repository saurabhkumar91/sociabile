<?php

class EmoticonsController {
    
    public function getEmoticonsAction( $header_data ){
        try {
            $result     = array();
            $db         = Library::getMongo();
            $emoticons  = $db->execute('return db.emoticons.find().toArray()');
            if( $emoticons['ok'] == 0 ) {
                Library::logging('error',"API : getEmoticons, mongodb error: ".$emoticons['errmsg']." : user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            foreach( $emoticons["retval"] AS $emoticon ){
                $result[]   = array(
                                "title"     => $emoticon["title"],
                                "artist"    => $emoticon["artist"],
                                "icon"      => FORM_ACTION.$emoticon["icon"],
                                "price"     => $emoticon["price"]
                );
            }
            Library::output(true, '1', "No Error", $result);
        } catch(Exception $e) {
            Library::logging('error',"API : getEmoticons, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    public function getEmoticonDetailsAction( $header_data, $post_data ){
        if( !isset($post_data['id'])) {
            Library::logging('alert',"API : getEmoticonDetails : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
             try {
                $emoticons  = Emoticons::findById( $post_data['id'] );
                $result     = array();
                if( $emoticons ) {
                    foreach ($emoticons->emoticons as &$value){
                        $value = FORM_ACTION.$value;
                    }
                    $result["title"]            = $emoticons->title;
                    $result["artist"]           = $emoticons->artist;
                    $result["price"]            = $emoticons->price;
                    $result["large_icon"]       = FORM_ACTION.$emoticons->large_icon;
                    $result["decsription"]      = $emoticons->decsription;
                    $result["emoticons_count"]  = count($emoticons->emoticons);
                    $result["emoticons"]        = $emoticons->emoticons;
                }else{
                    Library::logging('error',"API : getEmoticonDetails,  : user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', "No Error", $result);
            }catch(Exception $e) {
                Library::logging('error',"API : getEmoticonDetails, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    public function emoticonsPurchaseAction( $header_data, $post_data ){
        if( !isset($post_data['id'])) {
            Library::logging('alert',"API : getEmoticonDetails : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
            try {
                $emoticons  = Emoticons::findById( $post_data['id'] );
                $result     = array();
                if( $emoticons ) {
                    if( empty($emoticons->purchased_by) ){
                        $emoticons->purchased_by    = array();
                    }
                    $emoticons->purchased_by[]  = $header_data["id"];
                    if ( $emoticons->save() ) {
                            Library::output(true, '1', "Purchase Successfull", null);
                    } else {
                        $errors = array();
                        foreach ($emoticons->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        Library::logging('error',"API : openTimeCapsule : ".$errors." user_id : ".$header_data['id']);
                        Library::output(false, '0', $errors, null);
                    }
                }else{
                    Library::logging('error',"API : getEmoticonDetails,  : user_id : ".$header_data['id']);
                    Library::output(false, '0', ERROR_REQUEST, null);
                }
                Library::output(true, '1', "No Error", $result);
            }catch(Exception $e) {
                Library::logging('error',"API : getEmoticonDetails, error_msg : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
    
    public function getPurchasesdEmoticonsAction( $header_data ){
        try{
            $result     = array();
            $db         = Library::getMongo();
            $emoticons  = $db->execute('return db.emoticons.find({purchased_by:"'.$header_data["id"].'"}).toArray()');
            if( $emoticons['ok'] == 0 ) {
                Library::logging('error',"API : getPurchasesdEmoticons, mongodb error: ".$emoticons['errmsg']." : user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            foreach( $emoticons["retval"] AS $emoticon ){
                foreach ($emoticon["emoticons"] as &$value){
                    $value = FORM_ACTION.$value;
                }
                $result[]   = array(
                                "title"             => $emoticon["title"],
                                "artist"            => $emoticon["artist"],
                                "icon"              => FORM_ACTION.$emoticon["icon"],
                                "price"             => $emoticon["price"],
                                "large_icon"        => FORM_ACTION.$emoticons["large_icon"],
                                "decsription"       => $emoticons["decsription"],
                                "emoticons_count"   => count($emoticons["emoticons"]),
                                "emoticons"         => $emoticons["emoticons"]
                );
            }
            Library::output(true, '1', "No Error", $result);
        } catch(Exception $e) {
            Library::logging('error',"API : getPurchasesdEmoticons, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
}
