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
                $purchased  = "0";
                if( !empty($emoticon["purchased_by"]) && in_array( $header_data["id"], $emoticon["purchased_by"]) ){
                    $purchased  = "1";
                }
                $result[]   = array(
                                "id"        => (string)$emoticon["_id"],
                                "title"     => $emoticon["title"],
                                "artist"    => $emoticon["artist"],
                                "icon"      => FORM_ACTION.$emoticon["icon"],
                                "thumbnail" => FORM_ACTION.$emoticon["thumbnail"],
                                "price"     => $emoticon["price"],
                                "purchased" => $purchased
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
                    $result["thumbnail"]        = FORM_ACTION.$emoticons->thumbnail;
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
                                "large_icon"        => FORM_ACTION.$emoticon["large_icon"],
                                "decsription"       => $emoticon["decsription"],
                                "emoticons_count"   => count($emoticon["emoticons"]),
                                "emoticons"         => $emoticon["emoticons"]
                );
            }
            Library::output(true, '1', "No Error", $result);
        } catch(Exception $e) {
            Library::logging('error',"API : getPurchasesdEmoticons, error_msg : ".$e." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
    public function getFreeEmoticonsAction( $header_data ){
        $staticSmileys = array(
            "Emoticons/1757899461soc_angry.gif",
            "Emoticons/1884133391soc_angryred.gif",
            "Emoticons/304715169soc_cool2.gif",
            "Emoticons/1705379628soc_cool.gif",
            "Emoticons/1593154307soc_crying.gif",
            "Emoticons/1061497941soc_exhaushted.gif",
            "Emoticons/1821514132soc_gross.gif",
            "Emoticons/1946847887soc_grossgreen.gif",
            "Emoticons/1115455992soc_happy.gif",
            "Emoticons/1791049876soc_hehe.gif",
            "Emoticons/525641982soc_hungover.gif",
            "Emoticons/579783157soc_hungover2.gif",
            "Emoticons/803381996soc_kiss.gif",
            "Emoticons/618229093soc_kissme.gif",
            "Emoticons/708778971soc_oh.gif",
            "Emoticons/1174664530soc_omg.gif",
            "Emoticons/858143723soc_ouch.gif",
            "Emoticons/501717861soc_redeyes.gif"
        );
        $result = array();
        $i  = 0;
        foreach( $staticSmileys AS $emoticon ){
            $result[$i][]    = FORM_ACTION.$emoticon;
        }
        Library::output(true, '1', "No Error", $result);
    }
}
