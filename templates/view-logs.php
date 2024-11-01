<?php
/**
 * View Logs
 *
 * @package USERLOGS
 */

if ( empty( $log ) ) {
	return;
}

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

<div class="wrap">
	<h1 class="wp-heading-inline">View Logs</h1>
	<hr class="wp-header-end">
</div>

<div class="wrap" id="poststuff">

	<table class="wp-list-table widefat striped table-view-list posts">
		<thead>
		<tr>
			<th colspan="2" class="manage-column column-title">Log Details</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<th class="manage-column column-title" width="200">Log ID</th>
			<th class="manage-column column-title"><?php echo intval( $log->log_id ); ?></th>
		</tr>

		<tr>
			<td class="title column-title">Request Type</td>
			<td><?php echo esc_html( $request_type ); ?></td>
		</tr>

		<tr>
			<td class="title column-title">User ID</td>
			<td>
				<?php
				if ( ! empty( $log->log_user_id ) ) {
					echo esc_html( $log->log_user_id );
				} else {
					echo '--NA--';
				}
				?>
			</td>
		</tr>

		<tr>
			<td class="title column-title">Login Name</td>
			<td>
				<?php
				$edit_link = admin_url( 'user-edit.php?user_id=' . $log->log_user_id );

				if ( empty( $log->log_user_login ) ) {
					echo '<i title="User not registered.">--NA--</i>';
				} else {
					printf( '<a href="%s">%s</a>', esc_url( $edit_link ), esc_html( $log->log_user_login ) );
				}
				?>
			</td>
		</tr>
		<tr>
			<td class="title column-title">Display Name</td>
			<td><?php echo esc_html( $log->log_display_name ); ?></td>
		</tr>
		<tr>
			<td class="title column-title">User Email</td>
			<td><?php echo esc_html( $log->log_email ); ?></td>
		</tr>
		<tr>
			<td class="title column-title">User IP</td>
			<td>
				<a href="https://www.ip2location.com/demo/<?php echo esc_html( $log->log_user_ip ); ?>" target="_blank">
					<?php echo esc_html( $log->log_user_ip ); ?>
				</a>
			</td>
		</tr>
		<tr>
			<td class="title column-title">Date</td>
			<td><?php echo esc_html( gmdate( 'd M y, h:i a', strtotime( $log->log_date ) ) ); ?></td>
		</tr>

		<tr>
			<td class="title column-title" colspan="2">
				<a class="button" href="javascript:history.back();"> &laquo; Back</a>
				<a class="button" href="javascript:if ( confirm('Are you sure you want to delete?') ) { window.location='admin.php?page=userlogs&userlogs_delete_single=<?php echo esc_attr( $log->log_id ); ?>' } "> Delete Log</a>
			</td>
		</tr>

		</tbody>
	</table>

	<?php
	if ( ! empty( $user_info->data->ID ) ) {
		?>
		<br />
		<table class="wp-list-table widefat striped table-view-list posts">
			<thead>
			<tr>
				<th colspan="2" class="manage-column column-title">User Activity</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<th class="manage-column column-title" width="200">User Registration Date</th>
				<th class="manage-column column-title"><?php echo esc_html( gmdate( 'd M y, h:i a', strtotime( $user_info->data->user_registered ) ) ); ?></th>
			</tr>
			<tr>
				<th class="manage-column column-title">Last Login Date</th>
				<th class="manage-column column-title">
					<?php
					if ( ! empty( $last_login_date->log_date ) ) {
						echo esc_html( gmdate( 'd M y, h:i a', strtotime( $last_login_date->log_date ) ) );
					} else {
						echo '--NA--';
					}
					?>
				</th>
			</tr>

			<tr>
				<th class="manage-column column-title">User Posts</th>
				<th class="manage-column column-title">
					<?php
					if ( ! empty( $user_posts->total ) ) {
						echo esc_html( $user_posts->total );

						printf( " &nbsp; <a href='edit.php?author=%d'>view posts &raquo; </a>", intval( $user_info->data->ID ) );
					} else {
						echo '0';
					}
					?>
				</th>
			</tr>

			<tr>
				<th class="manage-column column-title">Total Comments</th>
				<th class="manage-column column-title">
					<?php
					if ( ! empty( $user_comments->total ) ) {
						echo esc_html( $user_comments->total );
						printf( " &nbsp;  <a href='edit-comments.php?user_id=%d'>view comments &raquo; </a>", intval( $user_info->data->ID ) );
					} else {
						echo '0';
					}
					?>
				</th>
			</tr>

			</tbody>
		</table>
		<?php
	}
	?>

	<?php
	if ( ! empty( $comment->comment_ID ) ) {
		?>
		<br />
		<table class="wp-list-table widefat striped table-view-list posts">
			<thead>
			<tr>
				<th colspan="2" class="manage-column column-title">User Comment</th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<th class="manage-column column-title" width="200">Comment Author</th>
				<th class="manage-column column-title"><?php echo esc_html( $comment->comment_author ); ?></th>
			</tr>

			<tr>
				<th class="manage-column column-title" width="200">Comment Author Email</th>
				<th class="manage-column column-title"><?php echo esc_html( $comment->comment_author_email ); ?></th>
			</tr>

			<tr>
				<th class="manage-column column-title" width="200">Comment Author URL</th>
				<th class="manage-column column-title"><?php echo esc_html( $comment->comment_author_url ); ?></th>
			</tr>

			<tr>
				<th class="manage-column column-title" width="200">Comment Author URL</th>
				<th class="manage-column column-title"><?php echo esc_html( $comment->comment_content ); ?></th>
			</tr>

			</tbody>
		</table>
		<?php
	}
	?>


</div>


