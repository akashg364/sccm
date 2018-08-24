
var resourceManager = (function(){

	var minVal = $("#resourcemanager-parameter_min_value");
	var maxVal = $("#resourcemanager-parameter_max_value");
	var typeEle = $("#resourcemanager-type input[type=radio]");

	var maxValDiv = $(".js-parameter_max_value");
	var minLabel = minVal.closest(".form-group").find(".control-label");

	

	function onValueTypeChange(){
		$(document).on("change","#resourcemanager-value_type",function(e){	
			var valueType = $(this).val();
			if(valueType == "range"){
				maxValDiv.show();
				minLabel.html("Variable Min Value");

			}else{
				minLabel.html("Variable Value");	
				maxValDiv.hide();
			}
			console.log($(this).val());
		});
	}

	function setMinMaxInputOnVariableTypeChange(){
			type = typeEle.val();
			console.log("Variable Type : "+type);
			if(type == 'user'){
				minVal.val("").closest(".form-group").hide();
				maxVal.val("").closest(".form-group").hide();
			}else{
				minVal.closest(".form-group").show();
				maxVal.closest(".form-group").show();
			}
	}
	function onVariableTypeChange(){
		typeEle.on("change",setMinMaxInputOnVariableTypeChange);
	}

	function init(){
		onValueTypeChange();	
		onVariableTypeChange();
		minLabel.html("Variable Value");			
		$("#resourcemanager-value_type").trigger("change");
		setMinMaxInputOnVariableTypeChange();
	}

	return {
		init:init
	}
})();


$(function(){
	resourceManager.init();
});