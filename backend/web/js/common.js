var faLoader = '<center><i class="fa fa-spinner fa-spin" style="font-size:24px"></i></center>';
var isAjaxSubmitForm = false;

var commonJs = (function(){

    function ajaxCall(url,method,formData,dataType="json"){
      var response = false;
      $.ajax({
            url     : url,
            type    : method,
            data    : formData,
            async    : false,
            dataType:dataType,
            success : function (data) {
                response = data;
            },
            error:  handleAjaxError
        });
       return response;
    }

    function handleAjaxError(jqXHR, exception) {
            var msg = "";
            if (jqXHR.status === 0) {
                msg = "Not connect.\n Verify Network.";
            } else if (jqXHR.status == 404) {
                msg = "Requested page not found. [404]";
            } else if (jqXHR.status == 500) {
                msg = "Internal Server Error [500].";
            } else if (exception === "parsererror") {
                msg = "Requested JSON parse failed.";
            } else if (exception === "timeout") {
                msg = "Time out error.";
            } else if (exception === "abort") {
                msg = "Ajax request aborted.";
            } else {
                msg = "Uncaught Error.\n" + jqXHR.responseText;
            }
            alert(msg);
    }

    function ajaxSubmitForm(){
      
        $(document).on("beforeSubmit", "#modalContent form",function(e) {
                if(!isAjaxSubmitForm || isAjaxSubmitForm==undefined){
                    return true;
                }
                e.preventDefault();
                var form = $(this);
                var formData = form.serialize();
                response = ajaxCall(form.attr("action"),form.attr("method"),formData);
                console.log(response);
                if(response.success){
                    $("#modalContent").html("<div class='alert alert-success'><strong>Success!</strong> Saved Sussessfully.</div>");
                    setTimeout(function(){ window.location.reload(); }, 3000);
                    return response;
                }else{
                    console.log("Error on Ajax response");
                    $("#modalContent form").yiiActiveForm('updateMessages', response.errors,true);
                }
        }).on("submit", function(e){ 
             if(!isAjaxSubmitForm || isAjaxSubmitForm==undefined){
                    return true;
                }           
            e.preventDefault();
            e.stopPropagation();
        });
        return false;
    }

    function init(){
       ajaxSubmitForm();
    }

    return {
        'init':init,
        'callAjax':ajaxCall,
    }
})();

$(function(){

        commonJs.init();
       
        callAjax = function(){
            alert("call as commonJs.callAjax()");
        }

        loadMoal = function(content,modalTitle,modalType=""){

            function setModalHeader(modalTitle){
                    if(modalTitle)$('#modalHeader').html(modalTitle);
            }
            $(".modal-dialog").removeClass("modal-lg modal-full");
            if(modalType)$('#modal').find(".modal-dialog").addClass(modalType);
            var modalContent = $('#modal').find('#modalContent'); 
            modalContent.html(faLoader);
            $('#modal').find(".modal-content").addClass("seim-popup");
            if (!$('#modal').data('bs.modal').isShown) {
                    $('#modal').modal('show');
            } 
            modalContent.html(content);
            setModalHeader(modalTitle);
            if(modalContent.find("h1").length){
                modalTitle = modalContent.find("h1").hide().html();
                setModalHeader(modalTitle);
            }
           
        }    

        $(document).on('click', '.showModalButton', function(e){
                e.preventDefault();
                function setModalHeader(modalTitle){
                    if(modalTitle)$('#modalHeader').html(modalTitle);
                }
                function updateModalContent(content){
                    modalContent.html(content);
                }
                $(".modal-dialog").removeClass("modal-lg modal-full");
                var modalType = $(this).attr("modalType");
                if(modalType)$('#modal').find(".modal-dialog").addClass(modalType);
                var modalContent = $('#modal').find('#modalContent'); 
                modalContent.html(faLoader);
                $('#modal').find(".modal-content").addClass("seim-popup");
                var tagName     = $(this).prop("tagName");
                var ajaxUrl     = tagName=="A"?$(this).attr('href'):$(this).attr('value');
                var modalTitle  = $(this).attr('title');
                
                if (!$('#modal').data('bs.modal').isShown) {
                    $('#modal').modal('show');
                } 
                modalContent.load(ajaxUrl,function(){
                    setModalHeader(modalTitle);
                    if(modalContent.find("h1").length){
                        modalTitle = modalContent.find("h1").hide().html();
                        setModalHeader(modalTitle);
                    }
                });     
                
            });
			
});