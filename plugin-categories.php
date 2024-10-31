<?php

/*
Plugin Name: Plugin Categories
Plugin URI: http://tormorten.no
Description: Categorize your WordPress Plugins just like you would posts.
Version: 0.2.2
Author: Tor Morten Jensen
Author URI: http://tormorten.no
*/

/**
 * Copyright (c) 2014 Tor Morten Jensen. All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * **********************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Access denied.' );
}

/**
 * Only allow the plugin files to be loaded while in admin
 *
 * @return bool
 */

if( !is_admin() )
	return;

define( 'PCAT_NAME',                 'Plugin Categories' );
define( 'PCAT_DOMAIN',               'plugin-categories' );
define( 'PCAT_DIR',	                 __FILE__ );
define( 'PCAT_REQUIRED_PHP_VERSION', '5' );
define( 'PCAT_REQUIRED_WP_VERSION',  '3.8' );

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function pcat_requirements_met() {
	global $wp_version;

	if ( version_compare( PHP_VERSION, PCAT_REQUIRED_PHP_VERSION, '<' ) ) {
		return false;
	}

	if ( version_compare( $wp_version, PCAT_REQUIRED_WP_VERSION, '<' ) ) {
		return false;
	}

	return true;
}

if( pcat_requirements_met() ) {


	require_once( __DIR__ . '/classes/plugin.categories.php' );
	require_once( __DIR__ . '/classes/views.php' );

	if ( class_exists( 'Plugin_Categories' ) && is_admin() ) {
		$GLOBALS['pcat'] = new Plugin_Categories;
		register_activation_hook(   __FILE__, array( $GLOBALS['pcat'], 'activate' ) );
		register_deactivation_hook( __FILE__, array( $GLOBALS['pcat'], 'deactivate' ) );
	}

	

}
else {

	add_action( 'admin_notices', 'pcat_error' );

}



/**
 * Throw and error upon activation if requirements are not met
 *
 * @return 
 */

function pcat_error() {

	global $wp_version;

	?>

	<div class="error">
		<p><?php echo PCAT_NAME; ?> error: Your environment doesn't meet all of the system requirements listed below.</p>

		<ul class="ul-disc">
			<li>
				<strong>PHP <?php echo PCAT_REQUIRED_PHP_VERSION; ?>+</strong>
				<em>(You're running version <?php echo PHP_VERSION; ?>)</em>
			</li>

			<li>
				<strong>WordPress <?php echo PCAT_REQUIRED_WP_VERSION; ?>+</strong>
				<em>(You're running version <?php echo esc_html( $wp_version ); ?>)</em>
			</li>
		</ul>

		<p>If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.</p>

		<p>You might be getting this error if there is already an instance of the plugin installed.</p>
	</div>

	<?php

}

?>