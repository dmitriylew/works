<?
    include 'config.php';
    foreach ($_FILES as $key => $value) { //перемещение файлов в tmp
        $dot_pos = strpos($value['name'],'.');
        $ext = substr($value['name'] , $dot_pos, strlen($value['name']) - $dot_pos);
        $new_name = MD5(date('Y-m-d H:i:s').rand(0,10000)).$ext;
        if(move_uploaded_file($value['tmp_name'], "upload/".$new_name)){
            db_insert('photo', array('photo'=>$new_name, 'category_id' => $_SESSION['category_id']));
        }
    }   
?>