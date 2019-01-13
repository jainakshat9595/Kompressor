<?php

class Compress {

    // @var file_url
    protected $file_url;

    // @var new_name_image
    protected $new_name_image;

    // @var quality
    protected $quality;

    // @var quality
    protected $pngQuality;
    
    // @var destination
    protected $destination;

    // @var image_size
    protected $image_size;
    
    // @var image_data
    protected $image_data;
    
    // @var image_mime
    protected $image_mime;
    
    // @var array_img_types
    protected $array_img_types;

    
    public function __construct($quality, $pngQuality, $destination) {
        $this->set_quality($quality);
        $this->set_pngQuality($pngQuality);
        $this->set_destination($destination);
    }

    // Getters

    function get_file_url() {
        return $this->file_url;
    }

    function get_new_name_image() {
        return $this->new_name_image;
    }

    function get_quality() {
        return $this->quality;
    }

    function get_pngQuality() {
        return $this->pngQuality;
    }

    function get_destination() {
        return $this->destination;
    }

    // Setters

    function set_file_url($file_url) {
        $this->file_url = $file_url;
    }

    function set_new_name_image($new_name_image) {
        $this->new_name_image = $new_name_image;
    }

    function set_quality($quality) {
        $this->quality = $quality;
    }

    function set_pngQuality($pngQuality) {
        $this->pngQuality = $pngQuality;
    }

    function set_destination($destination) {
        $this->destination = $destination;
    }
    
    /**
     * Function to compress image
     * @return boolean
     * @throws Exception
     */
    public function compress_image() {
        
        //Send image array
        $array_img_types = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png');
        $new_image = null;
        $last_char = null;
        $image_extension = null;
        $destination_extension = null;
        $png_compression = null;
        $maxsize = 5245330;
        
        try{
            
            //If not found the file
            if(empty($this->file_url) && !file_exists($this->file_url)){
                throw new Exception('Please inform the image!');
                return false;
            }
            
            //Get image width, height, mimetype, etc..
            $image_data = getimagesize($this->file_url);
            //Set MimeType on variable
            $image_mime = $image_data['mime'];
            
            //Verifiy if the file is a image
            if(!in_array($image_mime, $array_img_types)){
                throw new Exception('Please send a image!');
                return false; 
            }
            
            //Get file size
            $image_size = filesize($this->file_url);
                                    
            //if image size is bigger than 5mb
            if($image_size >= $maxsize){
                throw new Exception('Please send a imagem smaller than 5mb!');
                return false;
            }
            
            //If not found the destination
            if(empty($this->new_name_image)){
                throw new Exception('Please inform the destination name of image!');
                return false;
            }
            
            //If not found the quality
            if(empty($this->quality)){
                throw new Exception('Please inform the quality!');
                return false;
            }

            //If not found the png quality
            $png_compression = (!empty($this->pngQuality)) ? $this->pngQuality : 9 ;
            
            $image_extension = pathinfo($this->file_url, PATHINFO_EXTENSION);
            //Verify if is sended a destination file name with extension
            $destination_extension = pathinfo($this->new_name_image, PATHINFO_EXTENSION); 
            //if empty
            if(empty($destination_extension)){
                $this->new_name_image = $this->new_name_image.'.'.$image_extension;
            }
            
            //Verify if folder destination isn´t empty
            if(!empty($this->destination)){
                
                //And verify the last one element of value
                $last_char = substr($this->destination, -1);
                
                if($last_char !== '/'){
                    $this->destination = $this->destination.'/';
                }
            }
            
            list($width, $height) = $image_data;

            $shouldResize = false;

            if($width > 1000 || $height > 1000) {

                $shouldResize = true;

                $r = $width / $height;
            
                if (1 > $r) {
                    $newwidth = 1000*$r;
                    $newheight = 1000;
                } else {
                    $newheight = 1000/$r;
                    $newwidth = 1000;
                }
            }
            
            //Switch to find the file type
            switch ($image_mime){
                //if is JPG and siblings
                case 'image/jpeg':
                case 'image/pjpeg':
                    //Create a new jpg image
                    $new_image = imagecreatefromjpeg($this->file_url);

                    if($shouldResize) {
                        $dst = imagecreatetruecolor($newwidth, $newheight);
                        imagecopyresampled($dst, $new_image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    
                        $exif = exif_read_data($this->file_url, 'IFD0');
    
                        if(!empty($exif['Orientation'])) {
                            switch($exif['Orientation']) {
                                case 8:
                                    $dst = imagerotate($dst, 90, 0);
                                    break;
                                case 3:
                                    $dst = imagerotate($dst, 180, 0);
                                    break;
                                case 6:
                                    $dst = imagerotate($dst, -90, 0);
                                    break;
                            }
                        }
    
                        imagejpeg($dst, $this->destination.$this->new_name_image, $this->quality);
                    } else {
                        imagejpeg($new_image, $this->destination.$this->new_name_image, $this->quality);
                    }

                    
                    break;
                //if is PNG and siblings
                case 'image/png':
                case 'image/x-png':
                    //Create a new png image
                    $new_image = imagecreatefrompng($this->file_url);
                    imagealphablending($new_image , false);
                    imagesavealpha($new_image , true);

                    if($shouldResize) {
                        $dst = imagecreatetruecolor($newwidth, $newheight);
                        imagecopyresampled($dst, $new_image, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    
                        imagepng($dst, $this->destination.$this->new_name_image, $png_compression);
                    } else {
                        imagepng($new_image, $this->destination.$this->new_name_image, $png_compression);
                    }
                    
                    break;
            }
            
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        
        //Return the new image resized
        return $this->new_name_image;
        
    }
}

function resize_image($file, $w, $h, $crop=FALSE) {
    
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width-($width*abs($r-$w/$h)));
        } else {
            $height = ceil($height-($height*abs($r-$w/$h)));
        }
        $newwidth = $w;
        $newheight = $h;
    } else {
        
    }
    $src = imagecreatefromjpeg($file);
    $dst = imagecreatetruecolor($newwidth, $newheight);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

    return $dst;
}