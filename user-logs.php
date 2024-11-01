<?php
/**
 * Plugin Name:       User Logs
 * Plugin URI:        https://websolutionideas.com/
 * Description:       Monitor user activity on your website. View user logins, logouts, comments and user registrations.
 * Version:           1.0.2
 * Requires at least: 5.2
 * Requires PHP:      5.6
 * Author:            Vikas Sharma
 * Author URI:        https://websolutionideas.com/vikas/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       userlogs_user_logs
 *
 * User Logs
 * Copyright (C) 2021, Vikas Sharma <vikas@websolutionideas.com>
 *
 * 'User Logs' is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * 'User Logs' is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with 'User Logs'. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 *
 * @package USERLOGS
 */

// Prohibit direct script loading.
defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );

// Store plugin file location.
define( 'USERLOGS_FILE', __FILE__ );

require_once __DIR__ . '/classes/class-userlogs-main.php';
require_once __DIR__ . '/classes/class-userlogs-common.php';

// Initiate classes.
new Userlogs_Common();
new Userlogs_Main();

