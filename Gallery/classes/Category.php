<?
class Category{
    public $categories;
    public function __construct(){
        $this->categories = db_rows("SELECT * FROM category WHERE is_show ORDER BY header");    
    }
    public function getAllCategories(){
        return $this->categories;
    }
    public function getHeaderById($id){
        foreach($this->categories as $c){
            if($c->id == $id){
                return $c->header;
            }
        }    
    }
}
?>