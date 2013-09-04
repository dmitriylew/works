<?include '../config.php'?>
<?
if($id = intval($_POST['id'])){
    $img = db_row("SELECT photo FROM photo WHERE id = $id");
}
if(!empty($img->photo)){
?>
<img src='/upload/<?=$img->photo?>' width="600px" />
<script type="text/javascript">
    $(".d img").hide();
    $(".d img").load(function(){
        $(".d img").show('slow');    
    })
</script>
<?}?>