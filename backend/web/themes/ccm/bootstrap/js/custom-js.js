$(document).ready(function(){                       
 
 //Navigation Menu Slider
  $('#nav-expander').on('click',function(e){
    e.preventDefault();
    $('body').toggleClass('nav-expanded');
  });
  $('#nav-close').on('click',function(e){
    e.preventDefault();
    $('body').removeClass('nav-expanded');
  });
    
});

$(document).on('click', '.panel-heading span.clickable', function(e){
    var $this = $(this);
  if(!$this.hasClass('panel-collapsed')) {
    $this.parents('.panel').find('.panel-body').slideUp();
    $this.addClass('panel-collapsed');
    $this.find('i').removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
  } else {
    $this.parents('.panel').find('.panel-body').slideDown();
    $this.removeClass('panel-collapsed');
    $this.find('i').removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
  }
  })


//Sortable JavaScript Start
$("#widgets").sortable({
handle: ".panel-heading",
cursor: "move",
opacity: 0.5,
stop : function(event, ui){
  $("#json").val(
     JSON.stringify(
        $("#widgets").sortable(
           'toArray',
           {
              attribute : 'id'
           }
        )
     )
  );
}
});


//panel resize javascript Start
$(document).ready(function () {
//Toggle fullscreen
$(".btn-resize").click(function (e) {
    e.preventDefault();
    
    var $this = $(this);

    if ($this.children('i').hasClass('glyphicon-resize-full'))
    {
        $this.children('i').removeClass('glyphicon-resize-full');
        $this.children('i').addClass('glyphicon-resize-small');
    }
    else if ($this.children('i').hasClass('glyphicon-resize-small'))
    {
        $this.children('i').removeClass('glyphicon-resize-small');
        $this.children('i').addClass('glyphicon-resize-full');
    }
    $(this).closest('.panel').toggleClass('panel-fullscreen');
});
});


$(function(){
$('.clickable').on('click',function(){
    var effect = $(this).data('effect');
        $(this).closest('.panel')[effect]();
    })
})