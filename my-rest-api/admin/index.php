<?php
    require_once 'config.php';
    require_once 'loginValidate.php';
    $url                    = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
    $changePasswordUrl      = str_replace( "index.php", "changePassword.php", $url );
    $addEmoticonsUrl        = str_replace( "index.php", "addEmoticons.php", $url );
    $sendNotificationUrl    = str_replace( "index.php", "sendNotification.php", $url );
    $logoutUrl              = str_replace( "index.php", "logout.php", $url );
?>
<html>
    <body  style="text-align: center;">
        <br><br>
        <div style="text-align: center; border: solid;color:grey;border-radius: 10px;width:300px;display: inline-block;">
            <br><br>
            <p>
                <span class="button">
                    <a  style="color:#ffffff;" href="<?php echo $addEmoticonsUrl;?>">Add Emoticons</a>&nbsp;&nbsp;&nbsp;&nbsp;
                </span>
            </p>
            <br><br>
            <p>
                <span class="button">
                    <a  style="color:#ffffff;" href="<?php echo $sendNotificationUrl;?>">Send Notifications</a>&nbsp;&nbsp;&nbsp;&nbsp;
                </span>
            </p>
            <br><br>
            <p>
                <span class="button">
                    &nbsp;&nbsp;&nbsp;&nbsp;<a  style="color:#ffffff;" href="<?php echo $changePasswordUrl;?>">Change Password</a>
                </span>
            </p>
            <br><br>
            <p>
                <span class="button">
                    <a  style="color:#ffffff;" href="<?php echo $logoutUrl;?>">Logout</a>&nbsp;&nbsp;&nbsp;&nbsp;
                </span>
            </p>
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
