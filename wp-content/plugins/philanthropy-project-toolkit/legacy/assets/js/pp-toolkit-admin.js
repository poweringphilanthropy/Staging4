(function($){

	PP_Toolkit_Admin = function() {
		var self = this;

		self.init = function() {
			$( document ).on('click','.pp-add-row', self.addRow );
			$( document ).on('click', '.pp-remove-row', self.removeRow );


			self.initHideShow();
			self.initSelectize();
		};

		self.initHideShow = function(){
			$(document).on('change', '.hide-show-trigger', function(e){
				e.preventDefault();

				var tag = $(this).tagName,
					type = $(this).attr("type"),
					val = $(this).val();

				if (tag == "input" && (type == "checkbox" || type == "radio")){
					val = $(this).is(":checked") ? $(this).attr('data-val') : '';
				}

				// alert(val);

				$(this).closest('.wrapper-hide-show').find('.hide-show').addClass('hidden');
				$(this).closest('.wrapper-hide-show').find('.hide-show.show-if-'+val).removeClass('hidden');
				
			});

			$('.hide-show-trigger').each(function(){
				$(this).trigger('change');
			});
		};

		self.initSelectize = function(){

			if(!$.fn.selectize){
				return;
			}

			$('#select-non-profit').selectize();

			// $('select[name="_campaign_connected_stripe_id"]').
		}

		/**
		 * Repeatable
		 * @param  {[type]} e) {				e.preventDefault();				var fieldset [description]
		 * @return {[type]}    [description]
		 */
		self.addRow = function(e){
			e.preventDefault();
			var wrapper = $(this).closest('.pp-repeatable-fields'),
				container = wrapper.find('tbody').first(),
				total = wrapper.data('total'),
				parent_index = wrapper.data('parent-index') || 1,
				row = wrapper.find('.empty-row.screen-reader-text');
			
			if(typeof(total) == 'undefined'){
				total = container.children('.repeatable-field').length;
			}

			var total = parseInt(total) + 1;

			var template = row.clone(true).removeClass( 'empty-row screen-reader-text' ).get(0).outerHTML;
			template = template.replace(/{\?}/g, total ); 	// {?} => iterated placeholder
			template = template.replace(/{parent_index}/g, parent_index); 	// {valuePlaceholder} => ""
			container.append(template);
			wrapper.data('total', total);
		};

		self.removeRow = function(e){
			e.preventDefault();

			var wrapper = $(this).closest('.pp-repeatable-fields'),
				container = wrapper.find('tbody').first(),
				total = wrapper.data('total');

			$(this).closest('.repeatable-field').remove();

			// update total
			if(typeof(total) == 'undefined'){
				total = container.children('.repeatable-field').length;
			} else {
				total = total - 1;
			}

			// no need to update, so we dont have duplicate key when user remove 
			// wrapper.data('total', total);
		};
		
		return self;
	}

	var PP_Toolkit_Admin_Js;

	$(document).ready(function() {
		if(PP_Toolkit_Admin_Js == null) {
			PP_Toolkit_Admin_Js = new PP_Toolkit_Admin();
			PP_Toolkit_Admin_Js.init();
		}
	});

})(jQuery);