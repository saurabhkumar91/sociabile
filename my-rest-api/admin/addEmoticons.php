<html>
    <body>
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
            <label for="decsription">Description</label>
            <textarea type="file" name="decsription" id="decsription" value=""></textarea>
            <br><br>
                <div id="filediv"></div>
                <div><input name="file[]" type="file" id="file"/></div>
                <input type="button" id="add_more" class="upload" value="Add More Files"/>
            <br><br>
            <input type="submit" name="submit" value="submit">
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
                        $("#filediv").append("<div id='abcd" + abc + "' class='abcd'><img id='previewimg" + abc + "' src=''/></div>");
                        var reader = new FileReader();
                        reader.onload = imageIsLoaded;
                        reader.readAsDataURL(this.files[0]);
                        $(this).parent().hide();
                        $("#abcd" + abc).append($("<img/>", {
                            id: 'img',
                            src: 'x.png',
                            alt: 'delete'
                        }).click(function() {
                            $(this).parent().parent().remove();
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
                background-color:red;
                border:1px solid red;
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
            #file{
                color:green;
                padding:5px;
                border:1px dashed #123456;
                background-color:#f9ffe5
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
<?php
if (isset($_POST['submit'])) {
    
        $target_path        = "uploads/";
        $validextensions    = array("jpeg", "jpg", "png", "gif" );
        $errorMessage       = "";
        $maxSize            = 1000000;
        
        // upload icon image
        $iconImageName  = uploadFile( $_FILES['icon']['name'], $target_path, $_FILES["icon"]["size"], $_FILES['icon']['tmp_name'], $validextensions, $maxSize );
        if( $iconImageName === false ){
            $errorMessage   .= "Invalid file ".$_FILES['file']['name']."<BR>";
        }
        
        // upload large_icon image
        $largeiconImageName = uploadFile( $_FILES['large_icon']['name'], $target_path, $_FILES["large_icon"]["size"], $_FILES['large_icon']['tmp_name'], $validextensions, $maxSize );
        if( $largeiconImageName === false ){
            $errorMessage   .= "Invalid file ".$_FILES['file']['name']."<BR>";
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
            echo $errorMessage;
        }else{
            $mongo = new MongoClient();
            $db = $mongo->Sociabile;
            $request = 'db.emoticons.insert({ title: "'.$_POST["title"].'", artist: "'.$_POST["artist"].'", price: "'.$_POST["price"].'", icon: "'.$iconImageName.'", large_icon: "'.$largeiconImageName.'", decsription: "'.$_POST["decsription"].'", emoticons:'.json_encode($emoticons).' })';
            $result = $db->execute($request);
            if($result['ok'] == 0) {
                exit( $result['errmsg'] );
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
                    "key"                       =>  "emoticons/".$imgName,//$amazonSign["key"],
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
</pre>