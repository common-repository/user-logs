(function($) {

	$( '#userlogs-clear-button' ).on('click', function (e) {
		$("#userlogs_search_user_id").val('');
		$("#userlogs_search_username").val('');
		$("#userlogs_search_display_name").val('');
		$("#userlogs_search_ip_address").val('');
		$("#userlogs_search_email").val('');
		$("#userlogs_search_from_date").val('');
		$("#userlogs_search_to_date").val('');
		$("#userlogs_search_request_type").val('0');
	})

	$("#userlogs_search_from_date").datepicker();
	$("#userlogs_search_to_date").datepicker();

	$( '.userlogs-sort-column' ).on('click', function (e) {
		$("#userlogs_order_by").val( $(this).data('orderby') );
		$("#userlogs_order").val( $(this).data('order') );

		$("#userlogs_search_form form").submit();
	})

	// Bulk action onClick
	$( '#userlogs-bulk-action-button' ).on('click', function (e) {

		// If trash was selected
		if ( 'trash' === $( '#userlogs-bulk-action-selector').val() ) {

			// Get the list of selected rows.
			var selected = [];
			$('.userlogs-cb').prop('checked', function () {
				if ($(this).is(":checked")) {
					selected.push($(this).val());
				}
			});

			// If user has selected some rows.
			if ( selected.length ) {
				if (confirm('Are you sure you want to delete?')) {

					$("#userlogs_delete_multiple").val( selected.toString() );
					$("#userlogs_search_form form").submit();
				}
			}
		}
	})


})( jQuery );

function userlogs_confirm_delete_single( log_id ) {

	if ( '' !== log_id ) {
		if ( confirm('Are you sure you want to delete?') ) {
			jQuery("#userlogs_delete_single").val( log_id );
			jQuery("#userlogs_search_form form").submit();
		}
	}
}

