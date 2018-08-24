$(document).on('beforeSubmit', 'form#service-instance-form', function(e) {

    e.preventDefault();

    $(".js-form-msg").html("").hide();
    var total_device = $('#serviceinstance-service-data ul li').length;
    var j;
    var error_str = '';

    error_str += '<ul>';
    for (j = 0; j < total_device; j++) {

        var device_chk = $('#serviceinstance-device_id' + j).val();
        if (device_chk == '') {
            error_str += '<li>Device for endpoint ' + j + ' is required.</li>';
        }

        var role_chk = $('#role-' + j).val();
        if (role_chk == '') {
            error_str += '<li>Role required for endpoint ' + j + '</li>';
        }

        var user_data = $('#service-device-data-' + j + ' div input');
        var flag = true;
        $('#service-device-data-' + j + ' div input').each(function() {

            var user_defined_arr = $(this).attr('id').split('-');
            user_defined_arr.splice(0, 1);
            var user_defined_arr = user_defined_arr.join('-');

            if ($(this).val() == '') {
                error_str += '<li>Please fill the value for ' + user_defined_arr + '</li>';
            }
        });

    } // end for loop
    error_str += '</ul>';

    $('#endpoint_error').html(error_str);

    if (error_str == '<ul></ul>') {
        console.log("here...");
        var formDiv = $(".js-service-instance-form");
        var dryRunDiv = $(".js-dry-run-output");
        var form = $(this);
        var formData = form.serialize();
        response = commonJs.callAjax(form.attr("action"),form.attr("method"),formData);
        console.log(response);
        if(response.success){
              console.log("Success");  
            $(".js-form-msg").html("<div class='alert alert-success'><strong>Success!</strong> Service Deployed Successfully.</div>").show();
            formDiv.hide();
            console.log(response.data,response.data.service_instance_id);
            dryRunResponse = commonJs.callAjax(baseUrl+"service-deploy/dry-run","GET",{id:response.data.service_instance_id});
            if(dryRunResponse.success){
                dryRunDiv.show().html(dryRunResponse.data);
            }
        }else{
            console.log("errors updateMessages");
            form.yiiActiveForm('updateMessages', response.errors, true);
        }
        return true;
    } else {        
        return false;
    }

}).on("submit", function(e){
        e.preventDefault();
        e.stopPropagation();
});



// 	$(document).on("click", ".js-dry-run",function(e) {			
// 			e.preventDefault();
// 			//$(".js-form-msg").html("").hide();
// 			var form = $(this).closest("form#service-instance-form");

// 			form.yiiActiveForm("validate",true);
// 			return false;
// 			res = form.yiiActiveForm('submitForm');
// 			console.log(res);

// 			if(!res){

// 			}
// 			return false;

// 			if (form.find(".has-error").length) {
// 				return false;
// 			}					
// 			return false;			
// });

/*Delete Service from NSO */
$(document).on("click", ".js-delete-service", function(e) {
    e.preventDefault();
    if (!confirm("Are you sure you want to delete this service from devices?")) {
        return false;
    }
    cur = $(this);
    $(this).button('loading');
    $.ajax({
        url: $(this).attr('href'),
        method: "POST",
        success: function(data) {
            console.log(data);
            cur.button('reset');
            return false;
        },
        error: function() {
            alert("Error in removing service");
            cur.button('reset');
        }

    });
});

$("#p0").yiiGridView("destroy");