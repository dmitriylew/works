<?
    session_start();
    define('DOCUMENT_ROOT','/home/a1675293/public_html/');
    define('DB_HOST', 'mysql1.000webhost.com');
    define('DB_USER', 'a1675293_ur');
    define('DB_PASSWORD','pass4db');
    define('DB_NAME','a1675293_db');

    include DOCUMENT_ROOT.'functions/functions.db.php';
    db_connect();
    
    function __autoload($file_name){
        if(file_exists(DOCUMENT_ROOT.'classes/'. $file_name.'.php')){
            include DOCUMENT_ROOT.'classes/'.$file_name.'.php';
        }        
    }
?>