(function($){
	var G4G = {
		init: function(){
			this.initLogServiceHours();
			

			
		},
		initLogServiceHours: function(){

			var submitting = false;

			$(document).on('submit', '#save-user-service-hours', function(e){
				e.preventDefault();

				if(submitting){
					return false;
				}

				submitting = true;

				var button = $(this).find(':submit'),
					button_text = button.text();

				button.text('Processing..').attr('disabled','disabled');

				var data = $('#save-user-service-hours').serialize();
				$.ajax({
	                type: 'POST',
	                data: {
	                	action: 'save_user_service_hours',
	                	postdata: data,
	                },
	                dataType: 'json',
	                url: PP_OBJ.ajax_url,
	                success: function (response) {

	                	// alert(response);
	                	// console.log(response);

	                	if(response.success){
	                		// reset form
		                	var selectize = $selectCampaign[0].selectize;
		                	selectize.clear();

		                	$('#save-user-service-hours').find('input:text, input:password, input:file, select, textarea').val('');
						    $('#save-user-service-hours').find('input:radio, input:checkbox')
						         .removeAttr('checked').removeAttr('selected');


						    $('#log-service-hours').popup('hide');
	                	} else {
	                		alert(response.message);
	                	}
	                	
	                },
	                complete: function(jqXHR, textStatus){
	                	submitting = false;
	                	button.text(button_text).removeAttr('disabled');
	                }

	            });
			});

			$('#log-service-hours').on('popup:beforeopen', function(){
				if($.fn.datepicker){
					$( document ).on( 'focus', '.datepicker', function() {
		                if ( false === $( this ).hasClass( 'hasDatepicker' ) ) {
		                    $( this ).datepicker({
		                    	beforeShow: function (input) {
				                    $(input).css({
				                        "position": "relative",
				                        "z-index": 999999
				                    });
				                },
				                onClose: function () { 
				                	$('.ui-datepicker').css({ 'z-index': 0  }); 
				                }
		                    });
		                }        
		            });
				}
			});

			$selectCampaign = $('#save-user-service-hours select[name="campaign_id"]').selectize({
				preload: true,
			    valueField: 'campaign_id',
			    labelField: 'title',
			    searchField: 'title',
			    create: false,
			    render: {
			        option: function(item, escape) {
			            return '<div class="">' +
			                '<div class="select-image" style="background:url(\''+item.image+'\') no-repeat; background-size:cover; background-position: center;height: 50px;width: 80px;float: left; margin-right: 10px;"></div>' +
			                '<div class="select-content">'+
			                	'<div class="select-title">'+item.title+'</div>'+
			                	'<div class="select-desc" style="font-weight: 300;width:100%;text-overflow: ellipsis; white-space: nowrap;">'+item.description+'</div>'+
			                '</div>'+
			            '</div>';
			        }
			    },
			    load: function(query, callback) {

			        $.ajax({
			            url: PP_OBJ.ajax_url,
			            type: 'POST',
			            data: {
			            	action: 'get_campaign_options_for_service_hours',
			            	user_id: PP_OBJ.currect_user_id,
			            },
			            error: function() {
			                callback();
			            },
			            success: function(res) {
			            	// alert('ok');
			            	// console.log(res);
			                callback(res);
			            }
			        });
			    },
                onChange: function(value){
                	var selectize = $selectCampaign[0].selectize;
                    var _selected_objects = $.map(selectize.items, function(value) {
					    return selectize.options[value];
					});

                    var selected = {
                    	campaign_id: 0,
                    	image: '',
                    	title: '',
                    	description: ''
                    };

					if(_selected_objects.length){
						selected = $.extend(selected, _selected_objects[0]);
					}

					for (var key in selected) {
					    if (selected.hasOwnProperty(key)) {
					        // console.log(key + " -> " + selected[key]);
					        $('#save-user-service-hours [data-update="'+key+'"]').val( selected[key] );
					    }
					}

					// alert(selected);
					// console.log(selected);
                },
			});
		},
		// initAutocomplete: function(){
		// 	var options = {
		// 		data: ["blue", "green", "pink", "red", "yellow"],
		// 		adjustWidth: false,
		// 		list: {
		// 			match: {
		// 				enabled: true
		// 			}
		// 		}
		// 	};

		// 	$('#save-user-service-hours input[name="chapter_name"]').easyAutocomplete(options);
		// }
	};

	G4G.init();

})(jQuery);