/* bootstrap onclick toltip js */
$(document).ready(function(){
    $('[data-toggle="popover"]').popover();   
});
/* bootstrap onclick toltip js */

/* bootstrap toltip js */
$(function () {
  $('[data-toggle="tooltip"]').tooltip()
})
/* bootstrap toltip js */

/* Onclick checkbox table row selected js */
$(document).ready(function () {
  $('.record_table tr').click(function (event) {
      if (event.target.type !== 'checkbox') {
          $(':checkbox', this).trigger('click');
      }
  });

  $("input[type='checkbox']").change(function (e) {
      if ($(this).is(":checked")) {
          $(this).closest('tr').addClass("highlight_row");
      } else {
          $(this).closest('tr').removeClass("highlight_row");
      }
  });
});
/* Onclick checkbox table row selected js */

/* Multiple Select Drop Down */
$(document).ready(function() {
    $('.selectmultiple').multiselect({
        enableFiltering: true,
        includeSelectAllOption: true,
        selectAllJustVisible: false
    });
});
/* Multiple Select Drop Down */

/* Date Time Picker */
$('.form_datetime').datetimepicker({
  //language:  'en',
  weekStart: 1,
  todayBtn:  1,
  autoclose: 1,
  todayHighlight: 1,
  startView: 2,
  forceParse: 0,
  showMeridian: 1
  });
  $('.form_date').datetimepicker({
  language:  'en',
  weekStart: 1,
  todayBtn:  1,
  autoclose: 1,
  todayHighlight: 1,
  startView: 2,
  minView: 2,
  forceParse: 0
  });
  $('.form_time').datetimepicker({
  language:  'en',
  weekStart: 1,
  todayBtn:  1,
  autoclose: 1,
  todayHighlight: 1,
  startView: 1,
  minView: 0,
  maxView: 1,
  forceParse: 0
});
/* Date Time Picker */