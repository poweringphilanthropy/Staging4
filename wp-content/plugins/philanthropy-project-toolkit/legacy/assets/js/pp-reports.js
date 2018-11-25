(function($){
	var PP_Reports = {
		$dashboar_report_wrapper: $( '#dashboard-report-wrapper' ),
		items_to_process: [],
		report_data: [],
		init: function(){

			Handlebars.registerHelper('numberFormat', function (value, options) {
			    // Helper parameters
			    var dl = options.hash['decimalLength'] || 0;
			    var ts = options.hash['thousandsSep'] || ',';
			    var ds = options.hash['decimalSep'] || '.';

			    // Parse to float
			    var value = parseFloat(value);

			    // The regex
			    var re = '\\d(?=(\\d{3})+' + (dl > 0 ? '\\D' : '$') + ')';

			    // Formats the number with the decimals
			    var num = value.toFixed(Math.max(0, ~~dl));

			    // Returns the formatted number
			    return (ds ? num.replace('.', ds) : num).replace(new RegExp(re, 'g'), '$&' + ts);
			});

			Handlebars.registerHelper('ifMoreThan', function (i, index, options) {
			   if(parseInt(index) > parseInt(i)){
			      return options.fn(this);
			   } else {
			      return options.inverse(this);
			   }

			});

			this.init_dashboard_report();
		},
		init_dashboard_report: function(){
			if(!this.$dashboar_report_wrapper.length){
				return;
			}

			var campaign_ids = this.$dashboar_report_wrapper.attr('data-campaigns');
			
			if(!campaign_ids.trim()){
				// empty campaign ids
				return;
			}

			$.ajax({
                type: 'POST',
                data: {
                	action: 'get_dashboard_report_data',
                	campaign_ids: campaign_ids.split(','),
                	// timestamp: this.$dashboar_report_wrapper.attr('data-timestamp')
                },
                dataType: 'json',
                url: PP_DASHBOARD_REPORT.ajax_url,
                success: function (response) {

                	// alert(response.data);
                	// console.log(response.data);

					// reset 
                	PP_Reports.report_data = response.data;
					PP_Reports.items_to_process = response.to_process;

					// process
					PP_Reports.get_report_data( PP_Reports.items_to_process.shift() );
                },
				error: function( jqXHR, exception ) {

					console.log(jqXHR);
					
					// get error message
					var msg = '';
			        if (jqXHR.status === 0) {
			            msg = 'Not connect.\n Verify Network.';
			        } else if (jqXHR.status == 404) {
			            msg = 'Requested page not found. [404]';
			        } else if (jqXHR.status == 500) {
			            msg = 'Internal Server Error [500].';
			        } else if (exception === 'parsererror') {
			            msg = 'Requested JSON parse failed.';
			        } else if (exception === 'timeout') {
			            msg = 'Time out error.';
			        } else if (exception === 'abort') {
			            msg = 'Ajax request aborted.';
			        } else {
			            msg = 'Uncaught Error.\n' + jqXHR.responseText;
			        }

			        var response = new Object;
					response.status = 'failed';
					response.message = msg;

					alert(response.message);
				}

            });
		},
		get_report_data: function(report_type){

			// alert(item);
			// console.log(PP_Reports.items_to_process);

			var wrapper  = this.$dashboar_report_wrapper;
			var campaign_ids = this.$dashboar_report_wrapper.attr('data-campaigns');

			$.ajax({
				type: 'POST',
				url: PP_DASHBOARD_REPORT.ajax_url,
				dataType: 'json',
				data: { 
					action: 'get_report_data', 
                	campaign_ids: campaign_ids.split(','),
					report_type: report_type,
					data: JSON.stringify(PP_Reports.report_data),
				},
				success: function( response ) {

					console.log(response.data);

					// append 
				 	var source   = $("#report-"+report_type+"-template").html();
                    var template = Handlebars.compile(source);

                    wrapper.append( template( response.data ) );

					if ( PP_Reports.items_to_process.length ) {
						PP_Reports.get_report_data( PP_Reports.items_to_process.shift() );
					} else {
						$('.loading-report').hide();
					}
				},
				error: function( jqXHR, exception ) {

					console.log(jqXHR);
					
					// get error message
					var msg = '';
			        if (jqXHR.status === 0) {
			            msg = 'Not connect.\n Verify Network.';
			        } else if (jqXHR.status == 404) {
			            msg = 'Requested page not found. [404]';
			        } else if (jqXHR.status == 500) {
			            msg = 'Internal Server Error [500].';
			        } else if (exception === 'parsererror') {
			            msg = 'Requested JSON parse failed.';
			        } else if (exception === 'timeout') {
			            msg = 'Time out error.';
			        } else if (exception === 'abort') {
			            msg = 'Ajax request aborted.';
			        } else {
			            msg = 'Uncaught Error.\n' + jqXHR.responseText;
			        }

			        var response = new Object;
					response.status = 'failed';
					response.message = msg;

					alert(response.message);
				}
			});
		}
	};

	PP_Reports.init();

})(jQuery);