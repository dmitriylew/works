
<form enctype="multipart/form-data" id=news-form method="post" action="/index.php">
    <input  type="hidden" name="act" value="photo-add">
    <table>
    <tr>
        <td>Photo</td>
        <td>
            <input id=file  multiple="multiple" name="file[]" type="file">
            <progress></progress>
        </td>
    </tr>
    <tr>
        <td colspan="2" ><input value="Exit" onclick="d.close()" type="button"></td>
    </tr>
    <tr>
        <td>
            
        </td>
    </tr>
    </table>
</form>
<script type="text/javascript">
$(function(){
$('progress').hide();       
function progressHandlingFunction(e){
    if(e.lengthComputable){
        $('progress').attr({value:e.loaded,max:e.total});
    }
}
     
    $('#preloader').hide();
    $('#file').bind('change', function(){
        
    var data = new FormData();
    
    var error = '';
    jQuery.each($('#file')[0].files, function(i, file) {
         
            if(file.name.length < 1) {
                error = error + ' ERR  ';   
            }
            if(file.size > 1000000) {
                error = error + ' File ' + file.name + ' is to big.';
            }
            if(file.type != 'image/png' && file.type != 'image/jpg' && !file.type != 'image/gif' && file.type != 'image/jpeg' ) {
                error = error + 'File  ' + file.name + '  doesnt match png, jpg or gif';
            }
         
        data.append('file-'+i, file);
 
    });
  
if (error != '') {alert(error);} else { 
        $.ajax({
            url: '/ajax.php',
            type: 'POST',
                xhr: function() { 
                var myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){ // проверка что осуществляется upload
                    myXhr.upload.addEventListener('progress',progressHandlingFunction, false); //передача в функцию значений
                }
                return myXhr;
                },
            data: data,
            cache: false,
            contentType: false,
            processData: false,
             
            beforeSend: function() {
              $('progress').show();  
            },
             
            success: function(data){
                location.reload();
                $('progress').hide();
               
            }
             
            ,
            error: errorHandler = function() {
                alert('ERROR LOADING FILES');
            }
 
        });
         
        }
    })
 
});
</script>