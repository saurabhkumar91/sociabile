<?php
    require_once 'config.php';
    require_once 'loginValidate.php';
    $url        = "http://".$_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
    $indexUrl   = str_replace( "addEmoticons.php", "index.php", $url );
    $logoutUrl  = str_replace( "addEmoticons.php", "logout.php", $url );
?>
<!--<pre>-->
<?php
if (isset($_POST['submit'])) {
//    print_r($_FILES); exit;
        $target_path        = "uploads/";
        $validextensions    = array("jpeg", "jpg", "png", "gif" );
        $errorMessage       = "";
        $maxSize            = 1000000;
        
        // upload icon image
        $iconImageName  = uploadFile( $_FILES['icon']['name'], $target_path, $_FILES["icon"]["size"], $_FILES['icon']['tmp_name'], $validextensions, $maxSize );
        if( $iconImageName === false ){
            $errorMessage   .= "Invalid file ".$_FILES['icon']['name']."<BR>";
        }
        
        // upload large_icon image
        $largeiconImageName = uploadFile( $_FILES['large_icon']['name'], $target_path, $_FILES["large_icon"]["size"], $_FILES['large_icon']['tmp_name'], $validextensions, $maxSize );
        if( $largeiconImageName === false ){
            $errorMessage   .= "Invalid file ".$_FILES['large_icon']['name']."<BR>";
        }
        
        // upload thumbnail image
        $thumbnailImageName = uploadFile( $_FILES['thumbnail']['name'], $target_path, $_FILES["thumbnail"]["size"], $_FILES['thumbnail']['tmp_name'], $validextensions, $maxSize );
        if( $thumbnailImageName === false ){
            $errorMessage   .= "Invalid file ".$_FILES['thumbnail']['name']."<BR>";
        }
        
        $emoticons  = array();
        // Loop to get individual element from the array
        for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
            if( $_FILES['file']['name'][$i] ){
                $imageName  = uploadFile( $_FILES['file']['name'][$i], $target_path, $_FILES["file"]["size"][$i], $_FILES['file']['tmp_name'][$i], $validextensions, $maxSize );
                if( $imageName === false ){
                    $errorMessage   .= "Invalid file ".$_FILES['file']['name'][$i]."<BR>";
                }else{
                    $emoticons[]    =  $imageName;
                }
            }
            
        }

        if( $errorMessage ){
            echo "<p style='color:red;'>".$errorMessage."</p>";
        }else{
            $created    = time();
            $request = 'return db.emoticons.insert({ title: "'.$_POST["title"].'", artist: "'.$_POST["artist"].'", price: "'.$_POST["price"].'", icon: "'.$iconImageName.'", large_icon: "'.$largeiconImageName.'", thumbnail: "'.$thumbnailImageName.'", decsription: "'.$_POST["decsription"].'", emoticons:'.json_encode($emoticons).', created: '.$created.' })';
            $result = $db->execute($request);
            if($result['ok'] == 0) {
                echo "<p style='color:red;'>".$result['errmsg']."</p>";
            }else{
                $result = $db->execute( 'return db.emoticons.find( {title: "'.$_POST["title"].'",created: '.$created.'} ).toArray()' );
                echo "<p style='color:blue;'>Emoticon set uploaded successfully. Emoticon set ID is '".(string)$result['retval'][0]['_id']."' .</p>";
            }
        }
    
    
}
function uploadFile( $fileName, $target_path, $fileSize, $fileTmpName, $validextensions, $maxSize=100000 ){
        $ext            = explode('.', basename($fileName));
        $file_extension = end($ext); // Store extensions in the variable.
        $target_path    = $target_path . basename($fileName);     // Set the target path with a new name of image.
        if ( ($fileSize < $maxSize )     // Approx. 100kb files can be uploaded.
                && in_array($file_extension, $validextensions) ) {
                $imgName    = rand().basename($fileName);
                $uploadFile = $fileTmpName;
                include_once "../bootstrap.php";
                include_once "../controllers/AmazonsController.php";
                
                $amazon     = new AmazonsController();
                
                $amazonSign = $amazon->createsignatureAction(array("id"=>"admin"),10);
                $url        = $amazonSign['form_action'];
                $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
                $ext        = explode(".", $imgName);
                $extension  = trim(end($ext));
                $postfields = array(
                    "key"                       =>  "Emoticons/".$imgName,//$amazonSign["key"],
                    "AWSAccessKeyId"            => $amazonSign["AWSAccessKeyId"],
                    "acl"                       => $amazonSign["acl"],
                    "success_action_redirect"   => $amazonSign["success_action_redirect"],
                    "policy"                    => $amazonSign["policy"],
                    "signature"                 => $amazonSign["signature"],
                    "Content-Type"              => "image/$extension",
                    "file"                      => file_get_contents($uploadFile)
                );
                $ch         = curl_init();
                $options    = array(
                    CURLOPT_URL         => $url,
                    //CURLOPT_HEADER      => true,
                    CURLOPT_POST        => 1,
                    CURLOPT_HTTPHEADER  => $headers,
                    CURLOPT_POSTFIELDS  => $postfields,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_RETURNTRANSFER => true
                ); // cURL options
                curl_setopt_array($ch, $options);
                $imageName              = curl_exec($ch);
                curl_close($ch);
                return $imageName;
            
//            
//            if (move_uploaded_file($fileTmpName, $target_path)) {
//            } else {     //  If File Was Not Moved.
//                return "Some error occurred while uploading file ".basename($fileName);
//            }
        }
        return false;
}
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
        <br><br><br><br>
        <h3>Add Emoticons</h3>
        <form enctype="multipart/form-data" method="post" action="addEmoticons.php">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" value="">
            <br><br>
            <label for="artist">Artist</label>
            <input type="text" name="artist" id="artist" value="">
            <br><br>
            <label for="price">Price</label>
            <input type="text" name="price" id="price" value="">
            <br><br>
            <label for="icon">Icon</label>
            <input type="file" name="icon" id="icon">
            <br><br>
            <label for="large_icon">Large Icon</label>
            <input type="file" name="large_icon" id="large_icon" multiple>
            <br><br>
            <label for="large_icon">Thumbnail</label>
            <input type="file" name="thumbnail" id="thumbnail" multiple>
            <br><br>
            <label for="decsription">Description</label>
            <textarea type="file" name="decsription" id="decsription" value=""></textarea>
            <br><br>
                <div id="filediv"></div>
                <div><input name="file[]" type="file" id="file"/></div>
                <br>
                <input type="button" id="add_more" class="upload" value="Add More Files"/>
            <br><br>
            <input type="submit" name="submit" value="submit" class="button">
        </form>
        

        <!-------Including jQuery from Google ------>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
        <script>
            var abc = 0;      // Declaring and defining global increment variable.
            $(document).ready(function() {
                //  To add new input file field dynamically, on click of "Add More Files" button below function will be executed.
                $('#add_more').click(function() {
                    $(this).before($("<div/>", {
                        id: 'filediv'
                    }).fadeIn('slow').append($("<input/>", {
                        name: 'file[]',
                        type: 'file',
                        id: 'file'
                    }), $("<br/><br/>")));
                });
                // Following function will executes on change event of file input to select different file.
                $('body').on('change', '#file', function() {
                    if (this.files && this.files[0]) {
                        abc += 1; // Incrementing global variable by 1.
                        var z = abc - 1;
                        var x = $(this).parent().find('#previewimg' + z).remove();
                        $(this).prop("id", $(this).prop("id")+abc );
                        $("#filediv").append("<div id='abcd" + abc + "' class='abcd'><img id='previewimg" + abc + "' src=''/></div>");
                        var reader = new FileReader();
                        reader.onload = imageIsLoaded;
                        reader.readAsDataURL(this.files[0]);
                        $(this).parent().hide();
                        $("#abcd" + abc).append($("<img/>", {
                            id: 'img',
                            src: 'x.png',
                            alt: 'delete',
                            sno:abc
                        }).click(function() {
                            $(this).parent().remove();
                            $("#file"+$(this).attr("sno")).remove();
                        }));
                    }
                });
                // To Preview Image
                function imageIsLoaded(e) {
                    $('#previewimg' + abc).attr('src', e.target.result);
                };
                $('#upload').click(function(e) {
                    var name = $(":file").val();
                    if (!name) {
                        alert("First Image Must Be Selected");
                        e.preventDefault();
                    }
                });
            });        
        </script>
        <!------- Including CSS File ------>
        <style>
            form{
                background-color:#fff
            }
            #maindiv{
                width:960px;
                margin:10px auto;
                padding:10px;
                font-family:'Droid Sans',sans-serif
            }
            #formdiv{
                width:500px;
                float:left;
                text-align:center
            }
            form{
                padding:40px 20px;
                box-shadow:0 0 10px;
                border-radius:2px
            }
            h2{
                margin-left:30px
            }
            .upload{
                background-color:green;
                border:1px solid green;
                color:#fff;
                border-radius:5px;
                padding:10px;
                text-shadow:1px 1px 0 green;
                box-shadow:2px 2px 15px rgba(0,0,0,.75)
            }
            .upload:hover{
                cursor:pointer;
                background:#c20b0b;
                border:1px solid #c20b0b;
                box-shadow:0 0 5px rgba(0,0,0,.75)
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
            #upload{
                margin-left:45px
            }
            #noerror{
                color:green;
                text-align:left
            }
            #error{
                color:red;
                text-align:left
            }
            #img{
                width:17px;
                border:none;
                height:17px;
                margin-left:-20px;
                margin-bottom:91px
            }
            .abcd{
                text-align:center;
                 display: inline-block;
            }
            .abcd img{
                height:100px;
                width:100px;
                padding:5px;
                border:1px solid #e8debd
            }
            b{
                color:red
            }     
            #filediv{
                display:inline-block;
            }
        </style>
        
<!--        <div id="maindiv">
            <div id="formdiv">
            </div>
        </div>-->
        
    </body>
</html>
<BR>
<pre>
</pre>