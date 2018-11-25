/*global jQuery, document*/
jQuery(document).ready(function($) {

    // Setup datepickers
	if($('.edd_pl_datepicker').length ) {
		var dateFormat = 'mm/dd/yy';
		$('.edd_pl_datepicker').datetimepicker({
			dateFormat: dateFormat,
			showOtherMonths: true,
			selectOtherMonths: true,
			showButtonPanel: true
		});
	}

    if($("input[name='edd_settings[edd_purchase_limit_g_start_date]']").length ) {
		$("input[name='edd_settings[edd_purchase_limit_g_start_date]']").datetimepicker({
			dateFormat: dateFormat,
			showOtherMonths: true,
			selectOtherMonths: true,
			showButtonPanel: true
		});
		$("input[name='edd_settings[edd_purchase_limit_g_end_date]']").datetimepicker({
			dateFormat: dateFormat,
			showOtherMonths: true,
			selectOtherMonths: true,
			showButtonPanel: true
		});
    }

    $('.edd_pl_datepicker').clearable();
	$("input[name='edd_settings[edd_purchase_limit_g_start_date]']").clearable();
	$("input[name='edd_settings[edd_purchase_limit_g_end_date]']").clearable();

    // Metabox globally disable
    $("input[name='_variable_pricing']").change(function () {
        var selectedItem = $("input[name='_variable_pricing']").is(':checked');

        if (selectedItem === true) {
            $("input[name='_edd_purchase_limit_variable_disable']").closest('p').css('display', 'block');
        } else {
            $("input[name='_edd_purchase_limit_variable_disable']").closest('p').css('display', 'none');
        }
    }).change();

    // Show remaining settings
    $("input[name='edd_settings[edd_purchase_limit_show_counts]']").change(function () {
        var selectedItem = $("input[name='edd_settings[edd_purchase_limit_show_counts]']").is(':checked');

        if (selectedItem === true) {
            $("input[name='edd_settings[edd_purchase_limit_remaining_label]']").closest('tr').css('display', 'table-row');
        } else {
            $("input[name='edd_settings[edd_purchase_limit_remaining_label]']").closest('tr').css('display', 'none');
        }
    }).change();

    // Restrict date settings
    $("input[name='edd_settings[edd_purchase_limit_restrict_date]']").change(function () {
        var selectedItem = $("input[name='edd_settings[edd_purchase_limit_restrict_date]']").is(':checked');
        
        if (selectedItem === true) {
            $("input[name='edd_settings[edd_purchase_limit_g_start_date]']").closest('tr').css('display', 'table-row');
            $("input[name='edd_settings[edd_purchase_limit_g_end_date]']").closest('tr').css('display', 'table-row');
            $("input[name='edd_settings[edd_purchase_limit_pre_date_label]']").closest('tr').css('display', 'table-row');
            $("input[name='edd_settings[edd_purchase_limit_post_date_label]']").closest('tr').css('display', 'table-row');
        } else {
            $("input[name='edd_settings[edd_purchase_limit_g_start_date]']").closest('tr').css('display', 'none');
            $("input[name='edd_settings[edd_purchase_limit_g_end_date]']").closest('tr').css('display', 'none');
            $("input[name='edd_settings[edd_purchase_limit_pre_date_label]']").closest('tr').css('display', 'none');
            $("input[name='edd_settings[edd_purchase_limit_post_date_label]']").closest('tr').css('display', 'none');
        }
    }).change();

    // Error handler settings
    $("select[name='edd_settings[edd_purchase_limit_error_handler]']").change(function () {
        var selectedItem = $("select[name='edd_settings[edd_purchase_limit_error_handler]'] option:selected");

        if (selectedItem.val() === 'redirect') {
            $("input[name='edd_settings[edd_purchase_limit_error_message]']").closest('tr').css('display', 'none');
            $("select[name='edd_settings[edd_purchase_limit_redirect_url]']").closest('tr').css('display', 'table-row');
        } else {
            $("input[name='edd_settings[edd_purchase_limit_error_message]']").closest('tr').css('display', 'table-row');
            $("select[name='edd_settings[edd_purchase_limit_redirect_url]']").closest('tr').css('display', 'none');
        }
    }).change();
});
