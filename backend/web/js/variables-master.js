var variablesMapping;

var variableMaster = (function(){

	var value1Html = "";

	function setLabelInput(e){
		value_type = $("#variablesmaster-value_type").val();
		if(value_type == 'range'){
			$("#variablesmaster-value1_label").closest(".form-group").find(".control-label").html("Value1 Label");
			$("#variablesmaster-value2_label").closest(".form-group").show();
		}else{
			$("#variablesmaster-value1_label").closest(".form-group").find(".control-label").html("Value Label");
			$("#variablesmaster-value2_label").val("").closest(".form-group").hide();
		}
	}

	function setValueInput(){
		variableID = $("#variablesmapping-variable_id").val();
		variableData = variablesMapping[variableID];
		if(!variableData){
			return false;
		}
		value1  = $("#variablesmapping-value1");
		val1 = value1.val();
		value2 = $("#variablesmapping-value2");
		label1 = variableData.value1_label || "Value1 :";
		label2 = variableData.value2_label || "Value2 :";
		value1.closest(".form-group").find(".control-label").html(label1);
		
		if($.inArray(variableData.data_type.toLowerCase(),["ipv4","ipv6"])!=-1){
			
			if(value1Html==""){
				value1Html = $("<div />").append(value1.clone()).html();
			}
			value1.closest(".col-lg-6").addClass("col-lg-12").removeClass("col-lg-6");
			value2.closest(".form-group").find(".control-label").html("Value2");
			value2.val("").closest(".form-group").hide();
			setIpv4Ipv6Pool(variableData.data_type,val1);
			

		}else if(variableData.value_type == 'range'){
			
			value2.closest(".form-group").find(".control-label").html(label2);
			value1.closest(".col-lg-12").addClass("col-lg-6").removeClass("col-lg-12");
			value2.closest(".form-group").show();
			if(value1Html){
				value1.replaceWith(value1Html);
			}

		}else{
		
			value2.closest(".form-group").find(".control-label").html("Value2");
			value1.closest(".col-lg-6").addClass("col-lg-12").removeClass("col-lg-6");
			value2.val("").closest(".form-group").hide();
			
			if(value1Html){
				value1.replaceWith(value1Html);
			}
		}
	}

	function setIpv4Ipv6Pool(ip_type,value){
		ipData = commonJs.callAjax(ipPoolUrl,'GET',{'ip_type':ip_type},dataType="json");
		inputHtml = "";
		if(ipData){
			inputHtml = "<select name='VariablesMapping[value1]' id='variablesmapping-value1' class='form-control'><option value=''>Select Pool</option>";

			$.each(ipData,function(id,pool){
					selected = id==value?"selected='selected'":"";
					inputHtml+="<option value='"+id+"' "+selected+">"+pool+"</option>";
			});
			inputHtml+= "</select>";
		}
		$("#variablesmapping-value1").replaceWith(inputHtml);
	}
	
	function init(){
		setLabelInput();
		$(document).on('change','#variablesmaster-value_type',setLabelInput);
		$(document).on('change','#variablesmapping-variable_id',setValueInput);
	}

	return {
		init:init,
		setValueInput:setValueInput
	}
})();


$(function(){
	variableMaster.init();
});