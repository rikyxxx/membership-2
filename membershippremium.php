<?php
/*
Plugin Name: Membership Premium
Version: 3.4.5 RC 3
Plugin URI: http://premium.wpmudev.org/project/membership
Description: The most powerful, easy to use and flexible membership plugin for WordPress, Multisite and BuddyPress sites available. Offer downloads, posts, pages, forums and more to paid members.
Author: Incsub
Author URI: http://premium.wpmudev.org
WDP ID: 140
License: GNU General Public License (Version 2 - GPLv2)
 */

// +----------------------------------------------------------------------+
// | Copyright Incsub (http://incsub.com/)                                |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License, version 2, as  |
// | published by the Free Software Foundation.                           |
// |                                                                      |
// | This program is distributed in the hope that it will be useful,      |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
// | GNU General Public License for more details.                         |
// |                                                                      |
// | You should have received a copy of the GNU General Public License    |
// | along with this program; if not, write to the Free Software          |
// | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston,               |
// | MA 02110-1301 USA                                                    |
// +----------------------------------------------------------------------+

// Load the new config file
require_once('membershipincludes/includes/config.php');
// Load the old config file - depreciated
require_once('membershipincludes/includes/membership-config.php');
// Load the common functions
require_once('membershipincludes/includes/functions.php');
// Set up my location
set_membership_url(__FILE__);
set_membership_dir(__FILE__);

// Load required classes
// Rules class
require_once( membership_dir('membershipincludes/classes/class.rule.php') );
// Rules class
require_once( membership_dir('membershipincludes/classes/class.advancedrule.php') );
// Gateways class
require_once( membership_dir('membershipincludes/classes/class.gateway.php') );
// Levels class
require_once( membership_dir('membershipincludes/classes/class.level.php') );
// Subscriptions class
require_once( membership_dir('membershipincludes/classes/class.subscription.php') );
// Pagination class
require_once( membership_dir('membershipincludes/classes/class.pagination.php') );
// Members class
require_once( membership_dir('membershipincludes/classes/class.membership.php') );
// Shortcodes class
require_once( membership_dir('membershipincludes/classes/class.shortcodes.php') );
// Communications class
require_once( membership_dir('membershipincludes/classes/class.communication.php') );
// URL groups class
require_once( membership_dir('membershipincludes/classes/class.urlgroup.php') );
// Pings class
require_once( membership_dir('membershipincludes/classes/class.ping.php') );
// Add in the coupon
require_once( membership_dir('membershipincludes/classes/class.coupon.php') );
// Add in the Admin bar
require_once( membership_dir('membershipincludes/classes/class.adminbar.php') );
// Set up the default rules
require_once( membership_dir('membershipincludes/includes/default.rules.php') );
// Set up the default advanced rules
require_once( membership_dir('membershipincludes/includes/default.advrules.php') );

// Load the Cron process
require_once( membership_dir('membershipincludes/classes/membershipcron.php') );

// Create the default actions
require_once( membership_dir('membershipincludes/includes/default.actions.php') );

if (is_admin()) {
    include_once( membership_dir('membershipincludes/external/wpmudev-dash-notification.php') );
    // Administration interface
    // Add in the contextual help
    require_once( membership_dir('membershipincludes/classes/class.help.php') );
    // Add in the wizard and tutorial
    require_once( membership_dir('membershipincludes/classes/class.wizard.php') );
    require_once( membership_dir('membershipincludes/classes/class.tutorial.php') );
    // Add in the tooltips class - from social marketing app by Ve
    require_once( membership_dir('membershipincludes/includes/class_wd_help_tooltips.php') );
    // Add in the main class
    require_once( membership_dir('membershipincludes/classes/membershipadmin.php') );

    $membershipadmin = new membershipadmin();

    // Register an activation hook
    register_activation_hook(__FILE__, 'M_activation_function');
} else {
    // Public interface
    require_once( membership_dir('membershipincludes/classes/membershippublic.php') );

    $membershippublic = new membershippublic();
}

// Load secondary plugins
load_all_membership_addons();
load_membership_gateways();


/******************************************************************************/
/************** WE ARE IN THE PROCESS OF REWRITING OF THE PLUGIN **************/
/********** EVERYTHING ABOVE IS OLD VERSION AND BELOW IS NEW VERSION **********/
/******************************************************************************/


/**
 * Automatically loads classes for the plugin. Checks a namespace and loads only
 * approved classes.
 *
 * @since 4.0.0
 *
 * @param string $class The class name to autoload.
 * @return boolean Returns TRUE if the class is located. Otherwise FALSE.
 */
function membership_autoloader( $class ) {
	$basedir = dirname( __FILE__ );
	$namespaces = array( 'Membership' );
	foreach ( $namespaces as $namespace ) {
		if ( substr( $class, 0, strlen( $namespace ) ) == $namespace ) {
			$filename = $basedir . str_replace( '_', DIRECTORY_SEPARATOR, "_classes_{$class}.php" );
			if ( is_readable( $filename ) ) {
				require $filename;
				return true;
			}
		}
	}

	return false;
}

/**
 * Instantiates the plugin and setups all modules.
 *
 * @since 3.4.5
 *
 * @global wpdb $wpdb The database connection.
 */
function membership_launch() {
	global $wpdb;

	// setup environment
	define( 'MEMBERSHIP_BASEFILE', __FILE__ );
	define( 'MEMBERSHIP_ABSURL', plugins_url( '/', __FILE__ ) );
	define( 'MEMBERSHIP_ABSPATH', dirname( __FILE__ ) );

	// database tables
	$prefix = defined( 'MEMBERSHIP_GLOBAL_TABLES' ) && MEMBERSHIP_GLOBAL_TABLES && isset( $wpdb->base_prefix ) ? $wpdb->base_prefix : $wpdb->prefix;
	define( 'MEMBERSHIP_TABLE_LEVELS',              "{$prefix}m_membership_levels" );
	define( 'MEMBERSHIP_TABLE_RULES',               "{$prefix}m_membership_rules" );
	define( 'MEMBERSHIP_TABLE_SUBSCRIPTIONS',       "{$prefix}m_subscriptions" );
	define( 'MEMBERSHIP_TABLE_SUBSCRIPTION_LEVELS', "{$prefix}m_subscriptions_levels" );
	define( 'MEMBERSHIP_TABLE_RELATIONS',           "{$prefix}m_membership_relationships" );

	$plugin = Membership_Plugin::instance();

	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
	} elseif ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	} elseif ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
	} else {
		if ( is_admin() ) {
		} else {
			// frontend modules
			$plugin->set_module( Membership_Module_Frontend_Register::NAME );
		}
	}
}

// register autoloader function
spl_autoload_register( 'membership_autoloader' );

// launch the plugin
membership_launch();
