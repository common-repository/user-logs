<?php
/**
 * Settings page.
 *
 * @package USERLOGS
 */

?>
<div class="wrap" id="userlogs-settings-wrap">

	<h2>User Logs Settings</h2>

	<h2 class="nav-tab-wrapper">
		<a class="nav-tab nav-tab-active" href="<?php echo esc_url( admin_url( 'admin.php?page=userlogs_settings' ) ); ?>">Settings</a>
		<a class="nav-tab" href="<?php echo esc_url( admin_url( 'admin.php?page=userlogs_settings&about=1' ) ); ?>">About</a>
	</h2>

	<section>

		<div class="userlogs-setting">
			<label>Purge all Logs</label>
			<a href="admin.php?page=userlogs_settings&action=purge" type="button" class="button userlogs-button">Delete All Logs</a>
		</div>

		<div class="userlogs-setting">

			<form action="admin.php" method="get">
				<input type="hidden" name="page" value="userlogs_settings">
				<input type="hidden" name="action" value="update-cron">

				<label>Automatically delete logs older then</label>
				<input type="text" value="<?php echo intval( get_option( 'userlogs_cron_cycle' ) ); ?>" class="userlogs_cron_days" name="userlogs_cron_days">
				<label>days</label>
				<input type="submit" value="Save" class="button userlogs_cron_button">
			</form>
		</div>

	</section>
</div>



