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
                    $result["description"]      = $emoticons->decsription;
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
                                "thumbnail"         => FORM_ACTION.$emoticon["thumbnail"],
                                "description"       => $emoticon["decsription"],
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
		"Emoticons/1042506405Angry.gif",
		"Emoticons/1785500052AngryRed.gif",
		"Emoticons/378074187Cool.gif",
		"Emoticons/1970214114Crying.gif",
		"Emoticons/1458607451Exhausted.gif",
		"Emoticons/739000470GrossGreen.gif",
		"Emoticons/1581078505Happy.gif",
		"Emoticons/2060807312Heart.gif",
		"Emoticons/152659072hehe.gif",
		"Emoticons/631632160Hungover.gif",
		"Emoticons/1110754681Kiss.gif",
		"Emoticons/1976051295KissMe.gif",
		"Emoticons/1059031471OH.gif",
		"Emoticons/10240765OMG.gif",
		"Emoticons/1995428083Ouch.gif",
		"Emoticons/1528958407Sick.gif",
		"Emoticons/1829144643Sleep.gif",
		"Emoticons/381767438Smile.gif",
		"Emoticons/1530968876Stare.gif",
		"Emoticons/1251350824Tear.gif",
		"Emoticons/230375261Tongue.gif",
		"Emoticons/1274011636WatteryEyes.gif",
		"Emoticons/1068348582Wink.gif",
		"Emoticons/170230029Yawn.gif",
        );
        
        $jimmy  = json_decode('{  "title" : "Jimmy (Ghost Boy)", "artist" : "Aaron French", "price" : "0.99", "icon" : "Emoticons/1750747785Jimmy_icon.gif", "large_icon" : "Emoticons/1539600669Jimmy_banner.gif", "decsription" : "Jimmy is a very shy and introverted person who likes to hang out by himself and play pretend. When he wears his sheet he calls himself Ghost Boy and runs around acting silly thinking nobody can see him because he\'s a ghost.", "emoticons" : [ "Emoticons/665961160Angry.gif", "Emoticons/1625407954Crying.gif", "Emoticons/1900127553Dance.gif", "Emoticons/1906481045Dizzy.gif", "Emoticons/1165489977Doh.gif", "Emoticons/768668359Exercise.gif", "Emoticons/1686640744Goodbye.gif", "Emoticons/730070435I-Dont-Know2.gif", "Emoticons/1228184974Jammin.gif", "Emoticons/226580491Late.gif", "Emoticons/73936891Laugh.gif", "Emoticons/1019850594Love.gif", "Emoticons/867279000Mustache.gif", "Emoticons/829550949No.gif", "Emoticons/2060279447Play.gif", "Emoticons/221109452Sleep.gif", "Emoticons/1236081359Smile.gif", "Emoticons/1055473397Wink.gif" ], "purchased_by" : [ "559a3ae0e70e6f9c6a8b456e", "559ccb04e70e6f816a8b456f", "559d08bbe70e6fb66a8b456e", "559bb2dfe70e6f8d6a8b4568", "559a325ee70e6f856a8b456a", "559e5efce70e6fb76a8b456e", "559fa6a7e70e6fa66a8b456e", "5552786ae70e6fa56a8b4568", "55a5320ce70e6fbe6a8b4570", "55af9581e70e6fa26a8b456d", "55afbc28e70e6fb16a8b4572", "55afbcbfe70e6f1a618b456d", "5582388fe70e6f926a8b4567" ], "thumbnail" : "Emoticons/2007166390icon5.png" }');
        $result = array();
        $i  = 0;
        foreach( $staticSmileys AS $emoticon ){
            $result[$i][]    = FORM_ACTION.$emoticon;
        }
        $i++;
        foreach( $jimmy->emoticons AS $emoticon ){
            $result[$i][]    = FORM_ACTION.$emoticon;
        }
        Library::output(true, '1', "No Error", $result);
    }
}
