<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://stevefisher.org.uk/
 * @since             1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Members Signup
 * Plugin URI:        https://github.com/fisherab/members-signup
 * Description:       This is a to allow creation of signup forms and their completion by logged in users.
 * Version:           1.0.0
 * Author:            Steve Fisher
 * Author URI:        https://stevefisher.org.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       members-signup
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (! function_exists("write_log")) {
    function write_log($log) { // TODO delete when no longer needed or make it depend  on WP_DEBUG
        if (is_array($log) || is_object($log)){
            error_log(print_r($log,true));
        } else {
            error_log($log);
        }
    }
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MEMBERS_SIGNUP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-members-signup-activator.php
 */
function activate_members_signup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-members-signup-activator.php';
	Members_Signup_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-members-signup-deactivator.php
 */
function deactivate_members_signup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-members-signup-deactivator.php';
	Members_Signup_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_members_signup' );
register_deactivation_hook( __FILE__, 'deactivate_members_signup' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-members-signup.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_members_signup() {

	$plugin = new Members_Signup();
	$plugin->run();

}
run_members_signup();
