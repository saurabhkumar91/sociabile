<?php
    if( isset($_POST['username']) &&  isset($_POST['password']) ){
        require_once 'config.php';
            $password   = md5( $_POST['password'] );
            $request    = 'return db.admin_users.find( { username:"'.$_POST['username'].'", password:"'.$password.'" } ).toArray();';
            $result     = $db->execute($request);
            if($result['ok'] == 0) {
                exit( $result['errmsg'] );
            }
            $phpName    = str_replace( "login.php", "addEmoticons.php", $_SERVER["PHP_SELF"] );
            $url        = "http://".$_SERVER["HTTP_HOST"].$phpName;
            if( count($result["retval"]) > 0 ){
                session_start();
                session_regenerate_id();
                $_SESSION["user"]   = $result["retval"][0];
                header("Location:$url");
            }else{
                echo "<p style='color:red;'>Invalid username or password.</p>";
            }
   }
?>
<html>
    <body>
        <form enctype="multipart/form-data" method="post" action="login.php">
            <label for="username">User Name</label>
            <input type="text" name="username" id="username" value="">
            <br><br>
            <label for="password">Password</label>
            <input type="password" name="password" id="password" value="">
            <br><br>
            <input type="submit" name="submit" value="Login">
        </form>
    </body>
</html>

