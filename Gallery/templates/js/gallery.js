dialog = function(){
    this.template = '';
    
    this.html = "<div class=d></div>"; 
    $("body").append(this.html);
    $('.d').css('position','fixed');
    $('.d').css('z-index','999');
    width = $(document).width() / 2 - 300; 
    $('.d').css('left', width);
    $('.d').css('top', 10);
    $('.d').css('background','rgba(0,0,0,.7)');
    $('.d').css('border','10px solid rgba(255,255,255,.5)');
    $('.d').css('width','600px');
    $('.d').css('height','auto');
    $('.d').css('display','none');
    
    $("body").click(function(){
        d.close();    
    })
    this.showD = function(param){
        $.post(this.template,param,function(data){
            $('.d').html(data);
            $('.d').show("slide");         
        })
          
    };
    this.close = function(){
        $('.d').hide("explode");
    }
    this.setLoadFile = function(file){
        this.template = '/templates/' + file + '.html.php';       
    };   
}

photo = function(){
    this.showOriginal = function(id){
            
    }
}
$(function(){
    d = new dialog();    
})

