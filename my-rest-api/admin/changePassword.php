<?php
//exit ("unauthorized access");
    require_once 'config.php';
    require_once 'loginValidate.php';
    if( isset($_POST['newPassword']) &&  isset($_POST['password']) ){
        if( $_POST['newPassword'] !== $_POST['confirmPassword'] ){
            echo "<p style='color:red;'>New password and Confirm password did not matched.</p>";
        }else{
            $password   = md5( $_POST['password'] );
            $npassword  = md5( $_POST['newPassword'] );
            $request    = 'return db.admin_users.find( { username:"'.$_SESSION["user"]['username'].'" } ).toArray();';
            $result     = $db->execute($request);
            if($result['ok'] == 0) {
                echo "<p style='color:red;'>".$result['errmsg']."</p>";
            }else{
                if( $result["retval"][0]["password"] != $password ){
                    echo "<p style='color:red;'>Invalid Password.</p>";
                }else{
                    $request    = 'return db.admin_users.update( { username:"'.$_SESSION["user"]['username'].'" }, { $set:{password:"'.$npassword.'"} } );';
                    $result     = $db->execute($request);
                    if($result['ok'] == 0) {
                        echo "<p style='color:red;'>".$result['errmsg']."</p>";
                    }else{
                        echo "<p style='color:blue;'>Password changed successfully. Please re-login.</p>";
                        require_once 'login.php';
                        exit();                        
                    }
                }
            }
        }
    }
    $url        = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
    $indexUrl   = str_replace( "changePassword.php", "index.php", $url );
    $logoutUrl  = str_replace( "changePassword.php", "logout.php", $url );
?>
<html>
    <body>
            <p>
                <span style="float:left;" class="button">
                    &nbsp;&nbsp;&nbsp;&nbsp;<a  style="color:#ffffff;" href="<?php echo $indexUrl;?>">Back</a>
                </span>
                <span style="float:right" class="button">
                    <a  style="color:#ffffff;" href="<?php echo $logoutUrl;?>">logout</a>&nbsp;&nbsp;&nbsp;&nbsp;
                </span>
            </p>
            <br><br><br><br><br>
        <div style="text-align: left; border: solid;color:grey;border-radius: 10px;width:500px;display: inline-block;padding-left: 50px;">
            <h2>Change Password</h2>
            <form enctype="multipart/form-data" method="post" action="changePassword.php">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" value="">
                <br><br>
                <label for="newPassword">New Password</label>
                <input type="password" name="newPassword" id="newPassword" value="">
                <br><br>
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" name="confirmPassword" id="confirmPassword" value="">
                <br><br>
                <input type="submit" name="submit" value="submit">
            </form>
            <br><br>
        </div>
    </body>
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
                box-shadow:2px 2px 15px rgba(0,0,0,.75)
            }
        </style>
    
</html>

