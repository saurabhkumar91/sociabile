<?php
echo "\n\nmoveChatImagesToS3: Process Started At ".date('Y-m-d H:i:s')." \n";


$filepath   = str_replace("auto/moveChatImagesToS3.php", "", $_SERVER['SCRIPT_FILENAME'] );

include_once "$filepath/bootstrap.php";
include_once "$filepath/controllers/AmazonsController.php";


function uploadFile( $fileName, $fileTmpName ){
        $uploadFile = $fileTmpName;
        $amazon     = new AmazonsController();
        $amazonSign = $amazon->createsignatureAction(array("id"=>"admin"),10);
        $url        = $amazonSign['form_action'];
        $headers    = array("Content-Type:multipart/form-data"); // cURL headers for file uploading
        $ext        = explode(".", $fileName);
        $extension  = trim(end($ext));
        $postfields = array(
            "key"                       =>  "chat/".$fileName,//$amazonSign["key"],
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
//        return false;
}


$validExtensions    = array("jpeg", "jpg", "png", "gif" );
$errorMessage       = "";
// upload icon image
        
$dir   = str_replace("auto/moveChatImagesToS3.php", "images/chat/", $_SERVER['SCRIPT_FILENAME'] );
//$dir= "../images/chat";
$files  = scandir($dir);

if( is_array($files) && count($files) > 0 ){
    foreach( $files AS $fileName ){
        $ext            = explode('.', basename($fileName));
        $file_extension = end($ext); // Store extensions in the variable.
        if ( in_array($file_extension, $validExtensions) ) {
            
            if( uploadFile( $fileName, $dir.$fileName ) === false ){
                $errorMessage   .= "Invalid file ".$_FILES['icon']['name']." \n";
                echo "moveChatImagesToS3: ".$errorMessage;
            }else{
                echo "moveChatImagesToS3: File ".$dir.$fileName. " successfulyy transfered. \n";
                unlink( $dir.$fileName );
            }
        }
        
    }
}
echo "moveChatImagesToS3: Process Completed Successfully At ".date('Y-m-d H:i:s')." \n";
