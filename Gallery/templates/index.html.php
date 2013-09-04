<html>
    <head>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
        <script type="text/javascript" src="/templates/js/gallery.js"></script>
        
        <link href="/templates/css/styles.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id=category>
                <?foreach($categories as $c){?>
                <a href="?act=category&id=<?=$c->id?>" class=item ><?=$c->header?></a>
                <?}?>
                <span onclick="d.setLoadFile('add_category'); d.showD();" class="add">ADD CATEGORY</span>    
        </div>
        <div id=gallery>
            <?if(!empty($_category_name)){?>
                <h1><?=$_category_name?> <span onclick="d.setLoadFile('add_photo'); d.showD();" class="add" >ADD PHOTO</span></h1>
                <?if(!empty($gallery_images)){?>    
                    <?foreach($gallery_images as $i){?>
                        <div>
                            <img onclick="d.setLoadFile('photo'); d.showD({id:<?=$i->id?>});" title="<?=htmlspecialchars($i->desc)?>" src=<?=Gallery::prefix_image("/upload/{$i->photo}", "320x240", array('fixed_height' => true))?>>
                        </div>
                    <?}?>
                <?}else{?>
                    <div>
                        <i>Empty category</i>
                    </div>
                <?}?>
            <?}?>
        </div>
    </body>
</html>