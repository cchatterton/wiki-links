<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://alphasys.com.au/
 * @since             1.0.0
 * @package           Wiki_Links
 *
 * @wordpress-plugin
 * Plugin Name:       Wiki Links
 * Description:       Create ACF-powered wiki links.
 * Version:           1.0.1
 * Author:            AlphaSys
 * Author URI:        https://alphasys.com.au
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wiki-links
 * Domain Path:       /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Update URI: https://github.com/cchatterton/wiki-links
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WIKI_LINKS_VERSION', '1.0.1' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wiki-links-activator.php
 */
function activate_wiki_links() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wiki-links-activator.php';
	Wiki_Links_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wiki-links-deactivator.php
 */
function deactivate_wiki_links() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wiki-links-deactivator.php';
	Wiki_Links_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_wiki_links' );
register_deactivation_hook( __FILE__, 'deactivate_wiki_links' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wiki-links.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wiki_links() {

	$plugin = new Wiki_Links();
	$plugin->run();

}
run_wiki_links();
