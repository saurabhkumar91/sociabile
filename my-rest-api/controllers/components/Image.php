<?php
class Image {
    private function __resizeGif($original, $new_filename, $scaled_width, $scaled_height, $width, $height) {
        $error = false;

        if (!($src = imagecreatefromgif($original))) {
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $error = true;
        }

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $error = true;
        }

        if( empty($new_filename) ){
            $new_filename=null;
            ob_start();
        }
        if (!($new_image = imagegif($tmp, $new_filename))) {
            $error = true;
        }
        if( empty($new_filename) ){
            $new_image = ob_get_clean();
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality) {
        $error = false;
        if (!($src = imagecreatefromjpeg($original))) {
            $error = true;
        }
        if (!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $error = true;
        }

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $error = true;
        }
        if( empty($new_filename) ){
            $new_filename=null;
            ob_start();
        }
        if (!($new_image = imagejpeg($tmp, $new_filename, $quality))) {
            $error = true;
        }
        if( empty($new_filename) ){
            $new_image = ob_get_clean();
        }
        imagedestroy($tmp);
        if (false == $error) {
            return $new_image;
        }

        return false;
    }

    private function __resizePng($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality) {
        $error = false;
        /**
         * we need to recalculate the quality for imagepng()
         * the quality parameter in imagepng() is actually the compression level,
         * so the higher the value (0-9), the lower the quality. this is pretty much
         * the opposite of how imagejpeg() works.
         */
        $quality = ceil($quality / 10); // 0 - 100 value
        if (0 == $quality) {
            $quality = 9;
        } else {
            $quality = ($quality - 1) % 9;
        }


        if (!($src = imagecreatefrompng($original))) {
            $error = true;
        }

        if (!($tmp = imagecreatetruecolor($scaled_width, $scaled_height))) {
            $error = true;
        }

        imagealphablending($tmp, false);

        if (!imagecopyresampled($tmp, $src, 0, 0, 0, 0, $scaled_width, $scaled_height, $width, $height)) {
            $error = true;
        }

        imagesavealpha($tmp, true);

        if( empty($new_filename) ){
            $new_filename=null;
            ob_start();
        }
        if (!($new_image = imagepng($tmp, $new_filename, $quality))) {
            $error = true;
        }
        if( empty($new_filename) ){
            $new_image = ob_get_clean();
        }

        imagedestroy($tmp);

        if (false == $error) {
            return $new_image;
        }

        return false;
    }

    public function resize($original, $new_filename, $new_width = 0, $new_height = 0, $quality = 100) {
        if (!($image_params = getimagesize($original))) {
            throw new Exception('Original file is not a valid image: ' . $original);
        }

        $width = $image_params[0];
        $height = $image_params[1];

        if (0 != $new_width && 0 == $new_height) {
            $scaled_width = $new_width;
            $scaled_height = floor($new_width * $height / $width);
        } elseif (0 != $new_height && 0 == $new_width) {
            $scaled_height = $new_height;
            $scaled_width = floor($new_height * $width / $height);
        } elseif (0 == $new_width && 0 == $new_height) { //assume we want to create a new image the same exact size
            $scaled_width = $width;
            $scaled_height = $height;
        } else { //assume we want to create an image with these exact dimensions, most likely resulting in distortion
            $scaled_width = $new_width;
            $scaled_height = $new_height;
        }

        //create image
        $ext = $image_params[2];
        switch ($ext) {
            case IMAGETYPE_GIF:
                $return = $this->__resizeGif($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            case IMAGETYPE_JPEG:
                $return = $this->__resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            case IMAGETYPE_PNG:
                $return = $this->__resizePng($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
            default:
                $return = $this->__resizeJpeg($original, $new_filename, $scaled_width, $scaled_height, $width, $height, $quality);
                break;
        }

        return $return;
    }
}
