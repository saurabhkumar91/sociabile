<?php
    require_once 'config.php';
    require_once 'loginValidate.php';
    
    if( isset($_POST['message']) && isset($_POST['subject']) ){
        if( empty($_POST['subject']) ) {
            echo "<p style='color:red;'>Please enter subject</p>";
        }elseif( empty($_POST['message']) ) {
            echo "<p style='color:red;'>Please enter message</p>";
        }else{
            $request    = 'return db.users.find( { device_token:{$exists:true}, is_active:1, is_deleted:0 }, {device_token:1,os:1} ).toArray();';
            $result     = $db->execute($request);
            if($result['ok'] == 0) {
                echo "<p style='color:red;'>".$result['errmsg']."</p>";
            }else{
                $devices[1] = array();
                $devices[2] = array();
                $users      = array();
                foreach( $result["retval"] AS $user ){
                    if( !empty($user["device_token"]) ){
                        $users[]    = (string)$user["_id"];
                        if( $user["os"] == 1 ){
                            $devices[1][]  = $user["device_token"];
                        }elseif( $user["os"] == 2 ){
                            $devices[2][]  = $user["device_token"];
                        }
                    }
                }
                $filePath    =  strstr(__FILE__, "/admin/", true);
                require_once "$filePath/bootstrap.php";
                require_once "$filePath/controllers/SettingsController.php";
                $settings   = new SettingsController();
                $message    = array( "message"=>$_POST['subject'], "description"=>$_POST['message'], "type"=>NOTIFY_BY_ADMIN );
                if( $devices[1] ){
                    $settings->sendNotifications( $devices[1], array("message"=>json_encode($message)), "android", false, false );
                }
                if( $devices[2] ){
                    $settings->sendNotifications( $devices[2], array("message"=>json_encode($message)), "ios", false, false );
                }
                
                foreach( $users AS $userId ){
                    $request    = "return db.notifications.insert( { "
                                                    . "user_id:'$userId', "
                                                    . "notification:".json_encode($message).", "
                                                    . "is_viewed:0, "
                                                    . "date:".time()
                                    . "});";
                    $result     = $db->execute($request);
                }
                echo "<p style='color:blue;'>Sent successfully.</p>";
            }
        }
    }
    
    $url        = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
    $indexUrl   = str_replace( "sendNotification.php", "index.php", $url );
    $logoutUrl  = str_replace( "sendNotification.php", "logout.php", $url );
?>
<html>
    <body  style="text-align: center;">
        <p>
            <span style="float:left;" class="button">
                &nbsp;&nbsp;&nbsp;&nbsp;<a  style="color:#ffffff;" href="<?php echo $indexUrl;?>">Back</a>
            </span>
            <span style="float:right" class="button">
                <a  style="color:#ffffff;" href="<?php echo $logoutUrl;?>">logout</a>&nbsp;&nbsp;&nbsp;&nbsp;
            </span>
        </p>
        <br><br>
        <br><br>
        <div style="text-align: center; border: solid;color:grey;border-radius: 10px;width:500px;display: inline-block;">
            <h2>Send Notification</h2>
                <form enctype="multipart/form-data" method="post" action="sendNotification.php">
                    <b>Subject :</b> &nbsp;&nbsp;
                    <textarea  maxlength="50" name="subject" style="width:250px;"></textarea><span style="font-size: 10px;">*maximum 50 characters</span>
                    <br><br>
                    <b>Message :</b> &nbsp;&nbsp;
                    <textarea  maxlength="100" name="message" style="width:250px; height:80px;"></textarea><span style="font-size: 10px;">*maximum 100 characters</span>
                    <br><br>
                    <input type="submit" name="submit" value="submit">
                </form>
             <br><br>
        </div>
        <style>
            body{
                text-align: center;
            }
            .button{
                background-color:green;
                border:1px solid green;
                color:#fff;
                border-radius:5px;
                padding:10px;
                text-shadow:1px 1px 0 green;
                text-decoration: none;
                box-shadow:2px 2px 15px rgba(0,0,0,.75)
            }
        </style>
    </body>
</html>
