<?
    include "config.php";
    
    $cat_obj = new Category();
    
    $gallery_images = array();
    
    $act = (isset($_GET['act']))?$_GET['act']:$_POST['act'];
    switch($act){
        case 'category':
            if($id = intval($_GET['id'])){
                $_SESSION['category_id'] = $id;
                $gallery_images = Gallery::getGalleryById($id); 
                $_category_name = $cat_obj->getHeaderById($id);  
            }
        break;
        case 'category-add':
            unset($_POST['act']);
            $_POST['header'] = trim(addslashes(strip_tags($_POST['header'])));
            db_insert('category',$_POST);
            header("Location:/");
        break;
        case 'photo-add':
            print_r($_FILES);
        break;
    }
    
    $categories = $cat_obj->getAllCategories();
    
    
    include DOCUMENT_ROOT."templates/index.html.php"
?>