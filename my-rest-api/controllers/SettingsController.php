<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class SettingsController 
{ 
    /**
     * Method for change phone number change
     *
     * @param object request params
     * @param object reponse object
     *
     * @author Shubham Agarwal <shubham.agarwal@kelltontech.com>
     * @return json
     */
    
    public function changeNumberAction($header_data,$post_data)
    {   
        if( !isset($post_data['mobile_number'])) {
            Library::logging('alert',"API : changeNumber : ".ERROR_INPUT.": user_id : ".$header_data['id']);
            Library::output(false, '0', ERROR_INPUT, null);
        } else {
             try {
                 $mobile_number = $post_data['mobile_number'];
                 $user = Users::findById($header_data['id']);
                 $user->mobile_no = $mobile_number;
                 if ($user->save() == false) {
                    foreach ($user->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    Library::logging('error',"API : changeNumber : ".$errors." : user_id : ".$header_data['id']);
                    Library::output(false, '0', $errors, null);
                } else {
                    Library::output(true, '1', CHANGE_NUMBER, null);
                }
            } catch(Exception $e) {
                Library::logging('error',"API : changeNumber : ".$e." ".": user_id : ".$header_data['id']);
                Library::output(false, '0', ERROR_REQUEST, null);
            }
        }
    }
}
?>
