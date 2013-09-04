<?
class Gallery{
    public static function prefix_image($filename, $pref = '100x100', $options = array(), $cache = true){
        $datas = explode('.', $filename);
        $ext = array_pop($datas);
        $filename_prefix = join('.', $datas)."_{$pref}.{$ext}";
        if( (!file_exists(DOCUMENT_ROOT.$filename_prefix) && $cache) || !$cache){
            list($w, $h) = explode('x', $pref);
            $img = new Image(DOCUMENT_ROOT.$filename, $options);
            $img->Resize($w, $h);
            $img->Save($pref);
        } 
        return $filename_prefix;
    }
    public static function getGalleryById($id){
        $cats = db_rows("SELECT p.*, c.header as cheader FROM photo p RIGHT JOIN category c ON p.category_id = c.id WHERE p.is_show AND p.category_id = {$id} ORDER BY p.date DESC");
        return $cats;    
    }     
}
?>