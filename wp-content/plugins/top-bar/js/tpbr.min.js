/**
 * Top Bar Back JS (minified)
 */

;(function($){$(document).ready(function(){$('.dmb_color_picker').wpColorPicker();var want_button=$(".tpbr_yn_button").val();if(want_button=='button'){$(".tpbr_button_box").show()}
if(want_button=='nobutton'){$(".tpbr_button_box").hide()}
$(".tpbr_yn_button").on('change',function(){var want_button=$(".tpbr_yn_button").val();if(want_button=='button'){$(".tpbr_button_box").slideDown(100)}
if(want_button=='nobutton'){$(".tpbr_button_box").slideUp(100)}})})})(jQuery);
  