<?php
/**
 * Logs page.
 *
 * @package USERLOGS
 */

$logs                  = ! empty( $logs ) ? $logs : array();
$total_rows            = ! empty( $total_rows ) ? $total_rows : array();
$num_of_pages          = ! empty( $num_of_pages ) ? $num_of_pages : array();
$current_page          = ! empty( $current_page ) ? $current_page : array();
$search_user_id        = ! empty( $search_user_id ) ? $search_user_id : '';
$search_username       = ! empty( $search_username ) ? $search_username : '';
$search_display_name   = ! empty( $search_display_name ) ? $search_display_name : '';
$search_ip_address     = ! empty( $search_ip_address ) ? $search_ip_address : '';
$search_email          = ! empty( $search_email ) ? $search_email : '';
$search_from_date      = ! empty( $search_from_date ) ? $search_from_date : '';
$search_to_date        = ! empty( $search_to_date ) ? $search_to_date : '';
$placeholder_from_date = ! empty( $placeholder_from_date ) ? $placeholder_from_date : '';
$placeholder_to_date   = ! empty( $placeholder_to_date ) ? $placeholder_to_date : '';
$search_request_type   = ! empty( $search_request_type ) ? $search_request_type : '';
?>

<div class="wrap">
	<h1 class="wp-heading-inline"> User Logs </h1>
	<hr class="wp-header-end">
</div>

<div class="wrap">
	<div class="tablenav top">
		<div class="alignleft bulkactions" id="userlogs_search_form">
			<form method="get" action="<?php echo esc_url( admin_url( 'admin.php' ) ); ?>">
				<input type="hidden" name="page" value="userlogs">
				<input type="hidden" name="userlogs_order_by" value="" id="userlogs_order_by">
				<input type="hidden" name="userlogs_order" value="" id="userlogs_order">
				<input type="hidden" name="userlogs_delete_single" value="" id="userlogs_delete_single">
				<input type="hidden" name="userlogs_delete_multiple" value="" id="userlogs_delete_multiple">

				<input type="text"
					name="userlogs_search_user_id"
					id="userlogs_search_user_id"
					placeholder="Search User ID"
					class="userlogs_input"
					value="<?php echo esc_attr( $search_user_id ); ?>" />
				<input type="text"
					name="userlogs_search_username"
					id="userlogs_search_username"
					placeholder="Login Name"
					class="userlogs_input"
					value="<?php echo esc_attr( $search_username ); ?>" />
				<input type="text"
					name="userlogs_search_display_name"
					id="userlogs_search_display_name"
					placeholder="Display Name"
					class="userlogs_input"
					value="<?php echo esc_attr( $search_display_name ); ?>" />
				<input type="text"
					name="userlogs_search_ip_address"
					id="userlogs_search_ip_address"
					placeholder="IP Address"
					class="userlogs_input"
					value="<?php echo esc_attr( $search_ip_address ); ?>" />
				<input type="text"
					name="userlogs_search_email"
					id="userlogs_search_email"
					placeholder="Email Address"
					class="userlogs_input"
					value="<?php echo esc_attr( $search_email ); ?>" />

				<input type="text"
					name="userlogs_search_from_date"
					id="userlogs_search_from_date"
					placeholder="From <?php echo esc_attr( $placeholder_from_date ); ?>"
					class="userlogs_input"
					value="<?php echo esc_attr( $search_from_date ); ?>" />
				<input type="text"
					name="userlogs_search_to_date"
					id="userlogs_search_to_date"
					placeholder="To <?php echo esc_attr( $placeholder_to_date ); ?>"
					class="userlogs_input"
					value="<?php echo esc_attr( $search_to_date ); ?>" />

				<select	name="userlogs_search_request_type"
					id="userlogs_search_request_type"
					class="userlogs_input">

					<option value="0" <?php selected( $search_request_type, '' ); ?>>Request Type</option>
					<option value="1" <?php selected( $search_request_type, '1' ); ?>>Login</option>
					<option value="2" <?php selected( $search_request_type, '2' ); ?>>Logout</option>
					<option value="3" <?php selected( $search_request_type, '3' ); ?>>Registration</option>
					<option value="4" <?php selected( $search_request_type, '4' ); ?>>Comment</option>
				</select>

				<input type="submit" value="Search" class="button button-primary" id="userlogs-submit-button">
				<input type="button" value="Clear" class="button userlogs-button" id="userlogs-clear-button">

			</form>
		</div>

		<div class="tablenav-pages alignright" id="userlogs_pagination">
			<div class="pagination-links">
				<?php
				$page_links = paginate_links(
					array(
						'base'               => add_query_arg( 'userlogs_current_page', '%#%' ),
						'format'             => '',
						'prev_text'          => '&laquo;',
						'next_text'          => '&raquo;',
						'total'              => $num_of_pages,
						'current'            => $current_page,
						'before_page_number' => '<span class="tablenav-pages-navspan button" aria-hidden="true">',
						'after_page_number'  => '</span>',
					)
				);
				echo wp_kses_post( $page_links );
				?>
				<span class="displaying-num">Total <?php echo intval( $total_rows ); ?> items</span>
			</div>
		</div>
	</div>
</div>

<div class="wrap">
	<?php
	require_once 'graph.php';
	?>
</div>

<div class="wrap" id="poststuff">
	<?php
	$sort_by    = 'display_name';
	$sort_order = 'desc';
	?>

	<table class="wp-list-table widefat striped table-view-list posts">
		<thead>
		<tr>
			<td class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-1">Select All</label>
				<input id="cb-select-all-1" type="checkbox">
			</td>
			<?php
			self::print_column( 'User ID', 'log_user_id' );
			self::print_column( 'Username (Login Name)', 'user_login' );
			self::print_column( 'Display Name', 'display_name' );
			self::print_column( 'Email', 'user_email' );
			self::print_column( 'IP Address', 'log_user_ip' );
			self::print_column( 'Request Type', 'log_request_type' );
			self::print_column( 'Login Date', 'log_date' );
			?>
			<th class="manage-column column-title">
				Action
			</th>
		</tr>
		</thead>
		<tbody>

		<?php
		if ( empty( $logs ) ) {
			?>
		<thead>
		<tr>
			<td colspan="5">
				No records found!
				<?php
				if ( empty( $placeholder_from_date ) ) {
					echo 'waiting for user activity.';
				}
				?>
			</td>
		</tr>
		</thead>
			<?php
		}

		foreach ( $logs as $log ) {

			$edit_link = admin_url( 'user-edit.php?user_id=' . $log->log_user_id );
			$view_link = admin_url( 'admin.php?page=userlogs&view_log_id=' . $log->log_id );

			switch ( $log->log_request_type ) {
				case 1:
					$request_type = 'Login';
					break;
				case 2:
					$request_type = 'Logout';
					break;
				case 3:
					$request_type = 'User Registered';
					break;
				case 4:
					$request_type = 'Comment Added';
					break;
				default:
					$request_type = '--NA--';
			}

			?>
			<tr>
				<th class="check-column">
					<label class="screen-reader-text" for="userlogs-cb-<?php echo esc_html( $log->log_id ); ?>"> Select </label>
					<input type="checkbox" value="<?php echo esc_html( $log->log_id ); ?>" class="userlogs-cb">
				</th>

				<td class="title column-title"><?php echo esc_html( $log->log_user_id ); ?></td>
				<td class="title column-title">
					<?php
					if ( empty( $log->log_user_login ) ) {
						echo '<i title="User not registered.">--NA--</i>';
					} else {
						printf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html( $log->log_user_login ) );
					}
					?>
				</td>
				<td class="title column-title"><?php echo esc_html( $log->log_display_name ); ?></td>
				<td class="title column-title"><?php echo esc_html( $log->log_email ); ?></td>
				<td class="title column-title">
					<a href="https://www.ip2location.com/demo/<?php echo esc_html( $log->log_user_ip ); ?>" target="_blank">
						<?php echo esc_html( $log->log_user_ip ); ?>
					</a>
				</td>
				<td class="title column-title"><?php echo esc_html( $request_type ); ?></td>
				<td class="title column-title"><?php echo esc_html( gmdate( 'd M y, h:i a', strtotime( $log->log_date ) ) ); ?></td>
				<td class="title column-title">
					<a href="<?php echo esc_url( $view_link ); ?>">View</a>
					|
					<a href="javascript:userlogs_confirm_delete_single('<?php echo esc_attr( $log->log_id ); ?>')">Delete</a>
				</td>

			</tr>
			<?php
		}
		?>
		</tbody>

		<tfoot>
		<tr>
			<td class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-2">Select All</label>
				<input id="cb-select-all-2" type="checkbox">
			</td>
			<th scope="col" class="manage-column">User ID</th>
			<th scope="col" class="manage-column">Username (Login Name)</th>
			<th scope="col" class="manage-column">Display Name</th>
			<th scope="col" class="manage-column">Email</th>
			<th scope="col" class="manage-column">IP Address</th>
			<th scope="col" class="manage-column">Request Type</th>
			<th scope="col" class="manage-column">Login Date</th>
			<th scope="col" class="manage-column">Action</th>
		</tr>
		</tfoot>
	</table>


	<div class="tablenav bottom">

		<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>

			<select name="action2" id="userlogs-bulk-action-selector">
				<option value="-1">Bulk actions</option>
				<option value="trash">Delete</option>
			</select>
			<input type="submit" id="userlogs-bulk-action-button" class="button action" value="Apply">
		</div>
		<div class="alignleft actions"></div>

		<div class="tablenav-pages alignright" id="userlogs_pagination">
			<div class="pagination-links">
				<?php
				$page_links = paginate_links(
					array(
						'base'               => add_query_arg( 'userlogs_current_page', '%#%' ),
						'format'             => '',
						'prev_text'          => '&laquo;',
						'next_text'          => '&raquo;',
						'total'              => $num_of_pages,
						'current'            => $current_page,
						'before_page_number' => '<span class="tablenav-pages-navspan button" aria-hidden="true">',
						'after_page_number'  => '</span>',
					)
				);
				echo wp_kses_post( $page_links );
				?>
				<span class="displaying-num">Total <?php echo intval( $total_rows ); ?> items</span>
			</div>
		</div>


		<br class="clear">
	</div>

</div>


