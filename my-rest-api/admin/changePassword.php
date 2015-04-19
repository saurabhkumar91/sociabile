<?php
//exit ("unauthorized access");
    require_once 'loginValidate.php';
    if( isset($_POST['newPassword']) &&  isset($_POST['password']) ){
        if( $_POST['newPassword'] !== $_POST['confirmPassword'] ){
            echo "<p style='color:red;'>New password and Confirm password did not matched.</p>";
        }else{
            $mongo = new MongoClient();
            $db = $mongo->Sociabile;
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
    $url                = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
    $changePasswordUrl  = str_replace( "changePassword.php", "addEmoticons.php", $url );
?>
<html>
    <body>
        <p>
            <span>
                &nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo $changePasswordUrl;?>">Back</a>
            </span>
        </p>
        <br>
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
    </body>
</html>

