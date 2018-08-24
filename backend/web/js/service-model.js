var serviceModelTemplate = (function(){

	var resourceManagerArray = [];
	var templateData;
	var deviceRole;
	var serviceModel;
	var templateForm;

	//var deviceRole = $("#servicemodeltemplate-device_role_id");
	// var serviceModel = $("#servicemodeltemplate-service_model_id");
	// var templateForm = $("form#service-model-template-form");

	function getTemplateData(device_role_id,service_model_id){
		console.log("getTemplateData");
		// getTemplateUrl = this variable set in views/service-model/service-template-form.php
		templateData = commonJs.callAjax(getTemplateUrl,'POST',{'device_role_id':device_role_id,'service_model_id':service_model_id},dataType="json");
		return templateData;
	}

	function onDeviceRoleChanged(){
		console.log("device Role changed");
		device_role_id = $(this).val();
		service_model_id = $("#servicemodeltemplate-service_model_id").val();
		getTemplateData(device_role_id,service_model_id);
		autoFillForm(templateData);
	}
   
	function onReferenceRoleChanged(reference_id){
		console.log("onReferenceRoleChanged");
		$(".js-ref-user-variables,.js-ref-system-variables").html("").closest(".form-group").hide();
    	$(".js-variables-wrapper").removeClass("variable-div1").addClass("variable-div");
		referenceTemplatedata = getTemplateData(reference_id,$("#servicemodeltemplate-service_model_id").val());
		role_name = referenceTemplatedata.role_name;
		template_id = referenceTemplatedata.id;

		console.log(referenceTemplatedata);
		variablesArr = referenceTemplatedata.variables;
		$(".js-variables-wrapper").removeClass("variable-div").addClass("variable-div1");
		$.each(variablesArr,function(variableType,variables){
			$(".js-ref-"+variableType+"-variables").closest(".form-group").show();
			$.each(variables,function(key,variable){
				$(".js-ref-"+variableType+"-variables").append("<span class='dragdrop'  draggable='true' resource-manager-id='"+template_id+"_"+variable.id+"'>{"+role_name+"_"+variable.name+"}</span>");
			});
			
		});
	}
    function setReferenceRoleData(){
    	$(".js-ref-user-variables,.js-ref-system-variables").html("").closest(".form-group").hide();
    	$(".js-variables-wrapper").removeClass("variable-div1").addClass("variable-div");
    	
    	// Set Related Device Role : referecne_id
		$('#servicemodeltemplate-reference_id').on('depdrop:afterChange', function(event, id, value, jqXHR, textStatus) {
			reference_id = templateData.reference_id=='0'?"":templateData.reference_id;
			$("#servicemodeltemplate-reference_id").val(reference_id);
			if(reference_id){
				onReferenceRoleChanged(reference_id);
			}
		});
    }

	function autoFillForm(templateData){
		console.log("autoFillForm");
		$("#servicemodeltemplate-resource_manager_id").val("");
		$.each(templateData,function(key, val){
			$("#servicemodeltemplate-"+key).val(val);
		});
		resourceManagerArray = templateData.resourceManagerArray;
		setReferenceRoleData();
	}
	
	function dragDrop(){
		 document.addEventListener('dragstart', function (event) {
		  resource_manager_id = event.target.getAttribute("resource-manager-id");
	      event.dataTransfer.setData('Text', event.target.innerHTML);
	    });
		$("#servicemodeltemplate-nso_payload").bind("drop", setTemplateVariables);
	}

	
	function setTemplateVariables(e){
		resourceManagerArray.push(resource_manager_id);
		resourceManagerArray = [...new Set(resourceManagerArray)]; //get unique from array
		$("#servicemodeltemplate-resource_manager_id").val(resourceManagerArray.join());
	}


	function onSubmitTemplateForm(e){
		$(document).on("beforeSubmit", "form#service-model-template-form",function(e) {
				$(".js-form-msg").html("").hide();
			    e.preventDefault();
			    var form = $(this);
			    var formData = form.serialize();
			    response = commonJs.callAjax(form.attr("action"),form.attr("method"),formData);
			    console.log(response);
			    if(response.success){
			        $(".js-form-msg").html("<div class='alert alert-success'><strong>Success!</strong> Service Template Saved Successfully.</div>").show();
			        //next_device_role_id = $("#servicemodeltemplate-next_device_role_id").val();
			        $("form#service-model-template-form")[0].reset();
			        //console.log($("#servicemodeltemplate-next_device_role_id").val());
			        //$("#servicemodeltemplate-device_role_id").val(next_device_role_id);
			        return false;
			    }else{
			    	yiiActiveForm('updateMessages', data.validation, true);
			        console.log("Error on saving service model");
			    }
			    return false;

		}).on("submit", function(e){
		    e.preventDefault();
		    e.stopPropagation();
		});
		return false;
	}
	
	function init(){
		$(document).on("change","#servicemodeltemplate-device_role_id",onDeviceRoleChanged);
		$(document).on("change","#servicemodeltemplate-reference_id",function(e){
			onReferenceRoleChanged($(this).val());
		});
		dragDrop();
		onSubmitTemplateForm();
	
	}

	return {
		'init':init,
		'dragDrop':dragDrop
	}
})();

$(function(){
	serviceModelTemplate.init();
});

 
	 
//$( ".allfields li" ).draggable({ snap: true,helper:'clone',cursor: "select" });
//initDroppable($("#servicemodeltemplate-nso_payload"));
// function initDroppable($elements) {
//     $elements.droppable({
//         hoverClass: "textarea",
//         accept: ":not(.ui-sortable-helper)",
//         drop: function(event, ui) {
//             var $this = $(this);

//             var tempid = ui.draggable.text();
//             var dropText;
//             dropText = " {" + tempid + "} ";
//             var droparea = document.getElementById('servicemodeltemplate-nso_payload');
//             var range1 = droparea.selectionStart;
//             var range2 = droparea.selectionEnd;
//             var val = droparea.value;
//             var str1 = val.substring(0, range1);
//             var str3 = val.substring(range1, val.length);
//             droparea.value = str1 + dropText + str3;
//         }
//     });
// }