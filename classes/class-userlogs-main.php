<?php
/**
 * Userlogs_main class
 *
 * @package USERLOGS
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

/**
 * Class Userlogs_Main
 *
 * @package USERLOGS
 */
class Userlogs_Main {

	/**
	 * Plugin Name
	 *
	 * @var string
	 */
	public static $plugin_name = 'User Logs';

	/**
	 * Plugin Slug
	 *
	 * @var string
	 */
	public static $plugin_slug = 'userlogs';

	/**
	 * Userlogs_main constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {

			// Activation and Deactivation hooks.
			register_activation_hook( USERLOGS_FILE, array( $this, 'plugin_activation' ) );
			register_deactivation_hook( USERLOGS_FILE, array( $this, 'plugin_deactivation' ) );
			add_action( 'admin_init', array( $this, 'do_activation_redirect' ) );
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_and_styles' ) );
			add_action( 'admin_notices', array( $this, 'notice_welcome' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( USERLOGS_FILE ), array( $this, 'plugin_page_link' ) );
		}

		// Add Hooks.
		add_filter( 'wp_login', array( $this, 'login_action' ), 10, 2 );
		add_filter( 'wp_logout', array( $this, 'logout_action' ), 10, 1 );
		add_filter( 'user_register', array( $this, 'user_registered_action' ), 10, 1 );
		add_filter( 'comment_post', array( $this, 'comment_posted_action' ), 10, 2 );

		add_action( 'purge_user_logs_cron', array( $this, 'purge_user_logs_cron' ) );
		if ( ! wp_next_scheduled( 'purge_user_logs_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'purge_user_logs_cron' );
		}
	}

	/**
	 * Activate the plugin.
	 */
	public function plugin_activation() {

		global $wpdb;

		set_transient( 'userlogs_activation_redirect_transient', true, 30 );
		update_option( 'userlogs_welcome', 0 );
		update_option( 'userlogs_cron_cycle', 30 );

		$sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}user_logs` (
				`log_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				`log_user_id` bigint(20) UNSIGNED NOT NULL,
				`log_user_login` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`log_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`log_display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`log_comment_id` int(11) DEFAULT NULL,
				`log_user_ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
				`log_request_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=login, 2=logout, 3=registration, 4=comment',
				`log_date` datetime NOT NULL,
				PRIMARY KEY (`log_id`)
				) ENGINE=InnoDB {$wpdb->get_charset_collate()};";

		$wpdb->query( $wpdb->prepare( $sql ) ); // @codingStandardsIgnoreLine
	}

	/**
	 * Deactivate the plugin.
	 */
	public function plugin_deactivation() {
		global $wpdb;

		delete_option( 'userlogs_welcome' );
		delete_option( 'userlogs_cron_cycle' );

		$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}user_logs`" );
	}

	/**
	 * Activation redirect.
	 */
	public function do_activation_redirect() {
		// Bail if no activation redirect.
		if ( ! get_transient( 'userlogs_activation_redirect_transient' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( 'userlogs_activation_redirect_transient' );

		// Bail if activating from network, or bulk.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Redirect to plugin page.
		wp_safe_redirect( add_query_arg( array( 'page' => self::$plugin_slug ), admin_url( 'admin.php' ) ) );
	}

	/**
	 * Add menu item in the admin area.
	 */
	public function create_admin_menu() {
		add_menu_page( 'User Logs', 'User Logs', 'edit_posts', 'user-logs', '', 'dashicons-groups', 100 );
		add_submenu_page( 'user-logs', 'User Logs', 'User Logs', 'manage_options', self::$plugin_slug, array( $this, 'user_logs' ) );
		add_submenu_page( 'user-logs', 'Settings', 'Settings', 'manage_options', self::$plugin_slug . '_settings', array( $this, 'settings' ) );
		remove_submenu_page( 'user-logs', 'user-logs' );
	}

	/**
	 * Plugin settings link.
	 *
	 * @param array $links Plugin settings links.
	 * @return mixed
	 */
	public function plugin_page_link( $links ) {
		$settings_link = sprintf( '<a href="admin.php?page=%s">Settings</a>', self::$plugin_slug . '_settings' );
		array_unshift( $links, $settings_link );

		$view_link = sprintf( '<a href="admin.php?page=%s">View</a>', self::$plugin_slug );
		array_unshift( $links, $view_link );

		return $links;
	}

	/**
	 * Enqueue CSS for ou plugin in admin area.
	 */
	public function enqueue_admin_scripts_and_styles() {

		// Enqueue these scripts only if we are on the plugin settings page.
		if ( self::is_plugin_page() ) {
			wp_enqueue_style( 'userlogs_admin_style', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin-styles.css', array(), '1.0.0' );
			wp_register_style( 'jquery-ui', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/jquery-ui.min.css', array(), '1.0.0' );
			wp_enqueue_style( 'jquery-ui' );
			wp_enqueue_script( 'userlogs_admin_script', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-scripts.js', array( 'jquery', 'jquery-ui-datepicker' ), '1.0.0', true );
			wp_enqueue_script( 'google_charts_loader', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/google-charts-loader.js', array(), '1.0.0', true );
		}
	}

	/**
	 * Display welcome messages.
	 */
	public function notice_welcome() {
		global $pagenow;

		if ( self::is_plugin_page() ) {
			if ( ! get_option( 'userlogs_welcome' ) ) {
				?>
				<div class="notice notice-success is-dismissible">
						<p>Thank you for installing User Logs.</p>
				</div>
				<?php
				update_option( 'userlogs_welcome', 1 );
			}
		}
	}

	/**
	 * Plugin page in the admin area.
	 */
	public function user_logs() {
		global $wpdb;

		if ( ! empty( $_GET['view_log_id'] ) ) {
			$this->view_log( wp_unslash( intval( $_GET['view_log_id'] ) ) );
			return;
		}

		if ( ! empty( $_GET['userlogs_delete_single'] ) ) {
			$this->delete_single( wp_unslash( intval( $_GET['userlogs_delete_single'] ) ) );
		}

		if ( ! empty( $_GET['userlogs_delete_multiple'] ) ) {
			$this->delete_multiple( wp_unslash( intval( $_GET['userlogs_delete_multiple'] ) ) );
		}

		$current_page        = ! empty( $_GET['userlogs_current_page'] ) ? intval( wp_unslash( $_GET['userlogs_current_page'] ) ) : 1;
		$search_user_id      = ! empty( $_GET['userlogs_search_user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_user_id'] ) ) : '';
		$search_username     = ! empty( $_GET['userlogs_search_username'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_username'] ) ) : '';
		$search_display_name = ! empty( $_GET['userlogs_search_display_name'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_display_name'] ) ) : '';
		$search_ip_address   = ! empty( $_GET['userlogs_search_ip_address'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_ip_address'] ) ) : '';
		$search_email        = ! empty( $_GET['userlogs_search_email'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_email'] ) ) : '';
		$search_from_date    = ! empty( $_GET['userlogs_search_from_date'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_from_date'] ) ) : '';
		$search_to_date      = ! empty( $_GET['userlogs_search_to_date'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_to_date'] ) ) : '';
		$search_request_type = ! empty( $_GET['userlogs_search_request_type'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_search_request_type'] ) ) : '';
		$order_by            = ! empty( $_GET['userlogs_order_by'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_order_by'] ) ) : 'log_id';
		$order               = ! empty( $_GET['userlogs_order'] ) ? sanitize_text_field( wp_unslash( $_GET['userlogs_order'] ) ) : 'DESC';

		// Setting a default argument.
		$where = ' WHERE 1=%s ';
		$args  = array( 1 );

		if ( ! empty( $search_user_id ) ) {
			$where .= " AND log_user_id LIKE '%%%s%%'";
			$args[] = sanitize_text_field( $search_user_id );
		}

		if ( ! empty( $search_username ) ) {
			$where .= " AND log_user_login LIKE '%%%s%%'";
			$args[] = sanitize_text_field( $search_username );
		}

		if ( ! empty( $search_display_name ) ) {
			$where .= " AND log_display_name LIKE '%%%s%%'";
			$args[] = sanitize_text_field( $search_display_name );
		}

		if ( ! empty( $search_ip_address ) ) {
			$where .= " AND log_user_ip LIKE '%%%s%%'";
			$args[] = sanitize_text_field( $search_ip_address );
		}

		if ( ! empty( $search_email ) ) {
			$where .= " AND log_email LIKE '%%%s%%'";
			$args[] = sanitize_text_field( $search_email );
		}

		if ( ! empty( $search_from_date ) ) {

			$where .= ' AND DATE(log_date) >= %s';
			$args[] = sanitize_text_field( gmdate( 'Y-m-d', strtotime( $search_from_date ) ) );
		} else {
			// get first log date.
			$results               = $wpdb->get_row( "SELECT DATE(log_date) AS log_date FROM {$wpdb->prefix}user_logs ORDER BY log_id ASC LIMIT 0,1" );
			$placeholder_from_date = ! empty( $results->log_date ) ? $results->log_date : '';
		}

		if ( ! empty( $search_to_date ) ) {
			$where .= ' AND DATE(log_date) <= %s';
			$args[] = sanitize_text_field( gmdate( 'Y-m-d', strtotime( $search_to_date ) ) );
		} else {
			// get last log date.
			$results             = $wpdb->get_row( "SELECT DATE(log_date) AS log_date FROM {$wpdb->prefix}user_logs ORDER BY log_id DESC LIMIT 0,1" );
			$placeholder_to_date = ! empty( $results->log_date ) ? $results->log_date : '';
		}

		if ( ! empty( $search_request_type ) ) {
			$where .= ' AND log_request_type = %d';
			$args[] = sanitize_text_field( $search_request_type );
		}

		// Login Graph Data.
		$graph = self::get_graph_data( $where, $args );

		// Get rows.
		$sql = "SELECT COUNT(*) AS total FROM {$wpdb->prefix}user_logs {$where}";

		$results = $wpdb->get_row( $wpdb->prepare( $sql, $args ) );

		$total_rows = ! empty( $results->total ) ? $results->total : 0;

		// Pagination Variables.
		$limit        = 50; // Number of rows to show in page.
		$offset       = ( $current_page - 1 ) * $limit;
		$num_of_pages = ceil( $total_rows / $limit );

		$args[] = $offset;
		$args[] = $limit;

		$sql = "SELECT * FROM {$wpdb->prefix}user_logs
				{$where}
				ORDER BY {$order_by} {$order} 
				LIMIT %d, %d";

		$logs = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );

		include_once dirname( __DIR__ ) . '/templates/logs.php';
	}

	/**
	 * Get data for login graph.
	 *
	 * @param string $where where.
	 * @param array  $args args.
	 * @return array
	 */
	public static function get_graph_data( $where, $args ) {
		global $wpdb;

		$graph_data = array();
		for ( $request_type = 0; $request_type <= 4; $request_type ++ ) {

			$request_type_sql = '';
			if ( $request_type > 0 ) {
				$request_type_sql = 'AND log_request_type = ' . $request_type;
			}

			// Fetching max 365 data points for performance reasons.
			$sql = "SELECT COUNT(*) AS total_count, DATE(log_date) AS userlogs_log_date 
					FROM {$wpdb->prefix}user_logs
					{$where}
					{$request_type_sql}
					GROUP BY userlogs_log_date
					ORDER BY log_date ASC
					LIMIT 0, 365";

			$results = $wpdb->get_results( $wpdb->prepare( $sql, $args ) );
			if ( empty( $results ) ) {
				continue;
			}

			$graph_data[ $request_type ] = wp_list_pluck( (array) $results, 'total_count', 'userlogs_log_date' );
		}

		if ( empty( $graph_data[0] ) ) {
			return array();
		}

		$data_points = array_keys( $graph_data[0] );
		$dataset     = array();
		$ticks       = array();

		foreach ( (array) $data_points as $date ) {
			$timestamp = strtotime( $date );
			$year      = gmdate( 'Y', $timestamp );
			$month     = gmdate( 'm', $timestamp ) - 1;
			$day       = gmdate( 'd', $timestamp );

			// First element of a dataset is date.
			$ticks[] = "new Date($year, $month, $day)";

			$login_count    = ! empty( $graph_data[1][ $date ] ) ? $graph_data[1][ $date ] : 0;
			$logout_count   = ! empty( $graph_data[2][ $date ] ) ? $graph_data[2][ $date ] : 0;
			$reg_count      = ! empty( $graph_data[3][ $date ] ) ? $graph_data[3][ $date ] : 0;
			$comments_count = ! empty( $graph_data[4][ $date ] ) ? $graph_data[4][ $date ] : 0;

			$dataset[] = array( "new Date($year, $month, $day)", $login_count, $logout_count, $reg_count, $comments_count );
		}

		$ticks_json = str_replace( '"', '', wp_json_encode( $ticks ) );
		$data_json  = str_replace( '"', '', wp_json_encode( $dataset ) );

		return array(
			'ticks_json' => $ticks_json,
			'data_json'  => $data_json,
		);
	}

	/**
	 * View Logs.
	 *
	 * @param int $log_id Log ID.
	 */
	public function view_log( $log_id ) {
		global $wpdb;

		if ( empty( $log_id ) || ! intval( $log_id ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p>Record not found!</p></div>';
			return;
		}

		$log_id = intval( $log_id );

		$log = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}user_logs WHERE log_id = %d", $log_id ) );
		if ( empty( $log ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p>Record not found!</p></div>';
			return;
		}

		// Get user details.
		$user_id   = $log->log_user_id;
		$user_info = get_userdata( $log->log_user_id );

		if ( ! empty( $user_info ) ) {
			// Last login.
			$last_login_date = $wpdb->get_row( $wpdb->prepare( "SELECT log_date FROM {$wpdb->prefix}user_logs WHERE log_user_id = %d ORDER BY log_id DESC", $user_id ) );

			// No. of posts.
			$user_posts = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(*) AS total FROM {$wpdb->prefix}posts WHERE post_author = %d AND post_status != 'inherit'", $user_id ) );

			// No. of comments.
			$user_comments = $wpdb->get_row( $wpdb->prepare( "SELECT COUNT(*) AS total FROM {$wpdb->prefix}comments WHERE user_id = %d", $user_id ) );
		}

		if ( ! empty( $log->log_comment_id ) ) {
			// Get comment details.
			$comment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}comments WHERE comment_ID = %d", $log->log_comment_id ) );
		}

		include_once dirname( __DIR__ ) . '/templates/view-logs.php';
	}

	/**
	 * Plugin page in the admin area.
	 */
	public function settings() {
		global $wpdb;

		if ( ! empty( $_GET['action'] ) && 'purge' === $_GET['action'] ) {
			$wpdb->query( "TRUNCATE TABLE `{$wpdb->prefix}user_logs`" );
			echo '<div class="notice notice-success is-dismissible"><p>Logs data purged successfully!</p></div>';
		}

		if ( ! empty( $_GET['action'] ) && 'update-cron' === $_GET['action'] ) {

			$duration = ! empty( $_GET['userlogs_cron_days'] ) ? intval( $_GET['userlogs_cron_days'] ) : 0;
			if ( empty( $duration ) || $duration < 1 ) {

				echo '<div class="notice notice-error is-dismissible"><p>Invalid input!</p></div>';

			} else {

				update_option( 'userlogs_cron_cycle', $duration );

				// Manually trigger our cron function.
				$this->purge_user_logs_cron();

				echo '<div class="notice notice-success is-dismissible"><p>Cron job updated successfully!</p></div>';
			}
		}

		if ( ! empty( $_GET['about'] ) && 1 === intval( $_GET['about'] ) ) {
			$template = dirname( __DIR__ ) . '/templates/about.php';
		} else {
			$template = dirname( __DIR__ ) . '/templates/settings.php';
		}

		// Display the plugin page.
		include_once $template;
	}

	/**
	 * Are we on our plugin page?.
	 *
	 * @return bool
	 */
	public static function is_plugin_page() {
		global $pagenow;

		if ( 'admin.php' === $pagenow && isset( $_GET['page'] ) ) {

			if ( in_array( $_GET['page'], array( 'userlogs', 'userlogs_settings' ) ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * User Login.
	 *
	 * @param string $user_login login name.
	 * @param object $user user object.
	 */
	public function login_action( $user_login, $user ) {
		$args = array(
			'user_id'      => $user->ID,
			'user_login'   => $user_login,
			'user_email'   => $user->user_email,
			'display_name' => $user->display_name,
			'request_type' => 1,
		);

		self::insert_log( $args );
	}

	/**
	 * User logout.
	 *
	 * @param int $user_id user id.
	 */
	public function logout_action( $user_id ) {
		$user = get_userdata( $user_id );

		$args = array(
			'user_id'      => $user->ID,
			'user_login'   => $user->user_login,
			'user_email'   => $user->user_email,
			'display_name' => $user->display_name,
			'request_type' => 2,
		);

		self::insert_log( $args );
	}

	/**
	 * User Registration.
	 *
	 * @param int $user_id user id.
	 */
	public function user_registered_action( $user_id ) {
		$user = get_userdata( $user_id );

		$args = array(
			'user_id'      => $user->ID,
			'user_login'   => $user->user_login,
			'user_email'   => $user->user_email,
			'display_name' => $user->display_name,
			'request_type' => 3,
		);

		self::insert_log( $args );
	}

	/**
	 * Comment posted.
	 *
	 * @param int $comment_id comment id.
	 */
	public function comment_posted_action( $comment_id ) {
		$comment = get_comment( $comment_id );

		if ( empty( $comment ) ) {
			return;
		}

		if ( ! empty( $comment->user_id ) ) {

			$user = get_userdata( $comment->user_id );

			$args = array(
				'user_id'      => $user->ID,
				'user_login'   => $user->user_login,
				'user_email'   => $user->user_email,
				'display_name' => $user->display_name,
				'comment_id'   => $comment_id,
				'request_type' => 4,
			);
		} else {
			$args = array(
				'user_id'      => 0,
				'user_login'   => '',
				'user_email'   => $comment->comment_author_email,
				'display_name' => $comment->comment_author,
				'comment_id'   => $comment_id,
				'request_type' => 4,
			);
		}

		self::insert_log( $args );
	}

	/**
	 * Insert Logs.
	 *
	 * @param array $args args.
	 */
	public static function insert_log( $args ) {
		global $wpdb;

		$user_id      = ! empty( $args['user_id'] ) ? $args['user_id'] : 0;
		$user_login   = ! empty( $args['user_login'] ) ? $args['user_login'] : '';
		$user_email   = ! empty( $args['user_email'] ) ? $args['user_email'] : '';
		$display_name = ! empty( $args['display_name'] ) ? $args['display_name'] : '';
		$comment_id   = ! empty( $args['comment_id'] ) ? $args['comment_id'] : 0;
		$request_type = ! empty( $args['request_type'] ) ? $args['request_type'] : 0;
		$ip           = self::get_user_ip();

		$sql = "INSERT INTO {$wpdb->prefix}user_logs 
				SET log_user_id  = %d,
				log_user_login   = %s,
				log_email        = %s,
				log_display_name = %s,
				log_comment_id   = %d,
				log_user_ip      = %s,
				log_request_type = %d,
				log_date         = %s";

		$args = array(
			$user_id,
			$user_login,
			$user_email,
			$display_name,
			$comment_id,
			$ip,
			$request_type,
			gmdate( 'Y-m-d H:i:s' ),
		);

		$wpdb->query( $wpdb->prepare( $sql, $args ) );
	}

	/**
	 * Get user' IP.
	 */
	public static function get_user_ip() {
		$user_ip = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

		if ( empty( $user_ip ) ) {
			$user_ip = ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) : '';
		}

		if ( empty( $user_ip ) ) {
			$user_ip = ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) : '';
		}

		return $user_ip;
	}

	/**
	 * Delete single log.
	 *
	 * @param int $log_id log id.
	 */
	public function delete_single( $log_id ) {
		global $wpdb;

		if ( empty( $log_id ) ) {
			return;
		}

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}user_logs WHERE log_id = %d", $log_id ) );

		echo '<div class="notice notice-success is-dismissible"><p>Record deleted successfully!</p></div>';
	}

	/**
	 * Delete multiple logs.
	 *
	 * @param array $log_ids log ids.
	 */
	public function delete_multiple( $log_ids ) {
		global $wpdb;

		if ( empty( $log_ids ) ) {
			return;
		}

		$log_ids_array = explode( ',', $log_ids );
		if ( empty( $log_ids_array ) ) {
			return;
		}

		$format = Userlogs_Common::get_format( $log_ids_array );

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}user_logs WHERE log_id IN ({$format})", $log_ids_array ) );

		echo '<div class="notice notice-success is-dismissible"><p>Records deleted successfully!</p></div>';
	}

	/**
	 * Print column headings.
	 *
	 * @param string $label label.
	 * @param string $column column.
	 */
	public static function print_column( $label, $column ) {
		$order = ( ! empty( $_GET['userlogs_order'] ) && 'asc' === $_GET['userlogs_order'] ) ? 'desc' : 'asc';

		printf(
			"<th class='manage-column column-title sortable %s'>
				<a href='#' class='userlogs-sort-column' data-order='%s' data-orderby='%s'>
					<span>%s</span>
					<span class='sorting-indicator'></span>
				</a>
			</th>",
			esc_html( $order ),
			esc_html( $order ),
			esc_html( $column ),
			esc_html( $label )
		);
	}

	/**
	 * Daily cron job to purge old data.
	 */
	public function purge_user_logs_cron() {
		global $wpdb;

		$duration = get_option( 'userlogs_cron_cycle' );
		if ( empty( $duration ) ) {
			return;
		}

		$duration = intval( $duration );
		if ( empty( $duration ) || $duration <= 0 ) {
			return;
		}

		$past_date = gmdate( 'Y-m-d H:i:s', strtotime( intval( $duration ) . ' days ago' ) );

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}user_logs WHERE log_date < %s", $past_date ) );
	}
}
