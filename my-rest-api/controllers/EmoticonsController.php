<?php

class EmoticonsController {
    
    public function getEmoticonsAction( $header_data ){
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
    }
}
