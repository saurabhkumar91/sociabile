<?php
//exit ("unauthorized access");
    require_once 'config.php';
    require_once 'loginValidate.php';
    if( isset($_POST['username']) &&  isset($_POST['password']) ){
        if( $_POST['password'] !== $_POST['confirmPassword'] ){
            echo "<p style='color:red;'>Password and Confirm password did not matched.</p>";
        }else{
            $password   = md5( $_POST['password'] );
            $request    = 'return db.admin_users.find( { username:"'.$_POST['username'].'" } ).toArray();';
            $result     = $db->execute($request);
            if($result['ok'] == 0) {
                echo "<p style='color:red;'>".$result['errmsg']."</p>";
            }else{
                if( count($result["retval"]) > 0 ){
                    echo "<p style='color:red;'>User already exists.</p>";
                }else{
                    $request    = 'return db.admin_users.insert( { username:"'.$_POST['username'].'", password:"'.$password.'" } );';
                    $result     = $db->execute($request);
                    if($result['ok'] == 0) {
                        echo "<p style='color:red;'>".$result['errmsg']."</p>";
                    }else{
                        echo "<p style='color:blue;'>User added successfully.</p>";
                    }
                }
            }
        }
    }
?>
<html>
    <body>
        <form enctype="multipart/form-data" method="post" action="addUser.php">
            <label for="username">User Name</label>
            <input type="text" name="username" id="username" value="">
            <br><br>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" value="">
            <br><br>
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" name="confirmPassword" id="confirmPassword" value="">
            <br><br>
            <input type="submit" name="submit" value="Add User">
        </form>
    </body>
</html>

