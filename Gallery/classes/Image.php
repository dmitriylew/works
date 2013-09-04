<?
class Image{
    
    public $FileName;
    
    public $Types = array(
        '1' => 'gif',
        '2' => 'jpg',
        '3' => 'png',
        '4' => 'swf',
        '5' => 'psd',
        '6' => 'bmp',
        '7' => 'tiff',# байтовый порядок intel
        '8' => 'tiff',# байтовый порядок motorola
        '9' => 'jpc',
        '10' => 'jp2',
        '11' => 'jpx'
    );
    
    public $option = array(
        'fixed_width' => true, //фиксированная ширина, а значение высоты игнорируется
        'fixed_height' => false, //фиксированная выстора, а значение ширины игнорируется
        //если и ширина и высота фиксированная, то режем по пропорции если это возможно,
        //иначе режем по пропорции и лишнее удаляем
        'type_resize' => 'px'
    );
    public $filename;    
    public $width_orig;
    public $height_orig;
    public $type_orig;
    public $mime;
    
    public $image_p;
    public $image;
    
    public function Image($filename, $option = array()){
        $this->filename = $filename;
        foreach($option as $_k => $_v) $this->option[$_k] = $_v;
        $imageinfo = getimagesize($this->filename);
        if(is_array($imageinfo) && count($imageinfo)){
            list($this->width_orig, $this->height_orig, $this->type_orig) = $imageinfo;
            $this->mime = $imageinfo['mime'];
        }
        else{
            $this->type_orig = exif_imagetype($this->filename);
            $this->create_image();
            $this->width_orig = imagesx($this->image);
            $this->height_orig = imagesy($this->image);
        }
    }
        
    public function Resize($width, $height){
        if($this->GetOption('type_resize') == '%'){
            $width = $this->width_orig * ($width / 100);
            $height = $this->height_orig * ($height / 100);
        }
        if($this->width_orig && $this->height_orig){
            
            $percent_w = $width / $this->width_orig;
            $percent_h = $height / $this->height_orig;
            
            $dw = 0;
            $dh = 0;
            
            $ow = $width;
            $oh = $height;
            
            if($percent_h < 1 && $percent_h < 1){
                if($this->GetOption('fixed_width') && $this->GetOption('fixed_height')){                
                    if($percent_w > $percent_h){
                        $height = $this->height_orig * $percent_w;
                        $width = $this->width_orig * $percent_w;                        
                    }
                    else if($percent_w < $percent_h){
                        $height = $this->height_orig * $percent_h;
                        $width = $this->width_orig * $percent_h;
                    }
                    $dw = $ow - $width;
                    $dh = $oh - $height;
                }
                else if($this->GetOption('fixed_width')){
                    if($percent_w < 1 && $percent_h < 1)
                        $height = $this->height_orig * $percent_w;
                }
                else if($this->GetOption('fixed_height')){
                    if($percent_h < 1 && $percent_h < 1)
                        $width = $this->width_orig * $percent_h;
                }
                
                $this->create_image($width + $dw, $height + $dh);                    
                imagecopyresampled($this->image_p, $this->image, 0, 0, 0, 0, $width, $height, $this->width_orig, $this->height_orig);
            }
            
        }
    }
    
    public function Save($pf = 'preview'){        
        if(preg_match('!^(.+)\.([^\.]+)$!i', $this->filename, $save))
            $filename2 = "{$save[1]}_{$pf}.{$save[2]}";
        else
            $filename2 = $this->filename;
        
        if(!$this->out_image($filename2))
            copy($this->filename, $filename2);
        
        $this->FileName = $filename2;
        if(strpos(DOCUMENT_ROOT, $this->FileName) == 0)
            $this->FileName = str_replace(DOCUMENT_ROOT, '', $this->FileName);
        if(strpos('/', $this->FileName) === false)
            $this->FileName = "/{$this->FileName}";
    }
    
    public function Output(){        
        if($this->mime){
            header("Content-type: {$this->mime}");
            $this->out_image(null);
        }
    }
    
    public function SetOption($name, $value){
        $this->option[$name] = $value;
    }
    
    public function GetOption($name){
        return isset($this->option[$name])? $this->option[$name] : '';
    }
    
    public function create_image($width = null, $height = null){
        if(!$this->image){
            if($this->type_orig == 1)
                $this->image = imagecreatefromgif($this->filename);
            else if($this->type_orig == 2)
                $this->image = imagecreatefromjpeg($this->filename);
            else if($this->type_orig == 3)
                $this->image = imagecreatefrompng($this->filename);
        }
        
        if($width && $height){
            $this->image_p = imagecreatetruecolor($width, $height);
        }
    }
    
    public function out_image($filename = null){
        if(!$this->image_p) return false;        
        if($this->type_orig == 1)
            imagegif($this->image_p, $filename, 100);
        else if($this->type_orig == 2)
            imagejpeg($this->image_p, $filename, 100);
        else if($this->type_orig == 3)
            imagepng($this->image_p, $filename, 0);
        return true;
        #imagedestroy($this->image_p);
        #return true;
    }
}
?>
