var $ = jQuery.noConflict();

function redirect_to_giving_fuel( redirect_url, project_id, update_status, check_status ) {

	var $ = jQuery.noConflict();
	var has_error = 0;

	if ( check_status == 'true') { // need to check the inventory status - if it's not available, display an error message

		var data = {
		    action: 'check_project_inventory_status',
		    project_id: project_id
		};
		$.post( {
			url: virtuous_ajax_script.ajaxurl,
			data: data,
			async: false,
			success: function( response ) {

				if ( response['error'] == 'true' ) {
					has_error = 1;

					var url = document.referrer;
					var msg = 'We\'re sorry, it looks like this child has already been sponsored. Please <a href="' + url + '">click here</a> to select another child.';

					$( '#vrdm_inv_error_container' ).fadeIn( 'slow', function() {
						$( '#vrdm_inv_error_msg' ).css( 'color', 'red' );
						$( '#vrdm_inv_error_msg' ).html( msg );
						$( '#vrdm_inv_error_msg' ).prepend( '<br/>' );
					});
				}
			},
			error: function( response ) {
				console.log( response );
			}
		} );		
	}

	if ( has_error == 0 ) {

		if ( update_status == 'true' ) {
			var data = {
			    action: 'update_project_status',
			    project_id: project_id
			};
			$.post( {
				url: virtuous_ajax_script.ajaxurl,
				data: data,
				success: function( response ) {
					console.log( response );
				},
				error: function( response ) {
					console.log( response );
				}
			} );		
		}
		
		window.location.href = redirect_url;
	}
}
