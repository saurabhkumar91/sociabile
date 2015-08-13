<?php

/**
 * Description of NotificationsController
 *
 * @author Saurabh Kumar
 */
class NotificationsController {
    
    function saveNotifications( $deviceToken, $message ){
        foreach( $deviceToken AS $token ){
            $user   = Users::find( array( array("device_token"=>$token) ) );
            if( !empty($user[0]) ){
                $userId = (string)$user[0]->_id;
                $notification               = new Notifications();
                $notification->user_id      = $userId;
                $notification->notification = json_decode($message["message"]);
                $notification->is_viewed    = 0;
                $notification->date         = time();
                $notification->save();
            }
        }
    }
    
    function getNotificationsAction( $header_data, $post_data ){
        if(!isset($post_data['time']) ) {
            Library::logging('alert',"API : getNotifications : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        }        
        try {
            $db         =   Library::getMongo();
            $request    =   'return db.notifications.find( {  user_id : "'.$header_data['id'].'", date:{$gt:'.$post_data['time'].'} } ).toArray();';
            $notifications     = $db->execute($request);
            if($notifications['ok'] == 0) {
                Library::logging('error',"API : getNotifications : mongo error : ".$notifications['errmsg']." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
            $result = array();
            foreach( $notifications["retval"] AS $notification ){
                $result[]   = $notification["notification"];
            }
            Library::output(true, '1', "no error", $result);
        } catch (Exception $e) {
            Library::logging('error',"API : getNotifications : ".$e->getMessage()." ".": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_REQUEST, null);
        }
    }
    
}
