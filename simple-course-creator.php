<?php
/**
 * Plugin Name: Simple Course Creator
 * Plugin URI: https://scc.crispydiv.com/
 * Description: Organize WordPress posts into courses and display a course listing within each post.
 * Version: 2.0.0
 * Author: Sean Davis
 * Author URI: https://crispydiv.com/
 * License: GPL2
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * Tested up to: 6.7
 * Text Domain: scc
 * Domain Path: /languages/
 *
 * This plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see http://www.gnu.org/licenses/.
 *
 * The basic foundation of this plugin was highly influenced by Mike
 * Jolley's WP Post Series plugin. Special thanks to him.
 *
 * @package Simple_Course_Creator
 * @author  Sean Davis
 * @license GNU GENERAL PUBLIC LICENSE Version 2 - /license.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Primary class for Simple Course Creator.
 *
 * @since 1.0.0
 */
class Simple_Course_Creator {


	/**
	 * Constructor — define constants, load text domain, enqueue assets, require files.
	 */
	public function __construct() {

		define( 'SCC_NAME',    'Simple Course Creator' );
		define( 'SCC_VERSION', '2.0.0' );
		define( 'SCC_DIR',     trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'SCC_URL',     trailingslashit( plugin_dir_url( __FILE__ ) ) );

		add_action( 'plugins_loaded',        array( $this, 'upgrade_check' ), 1 );
		add_action( 'init',                  array( $this, 'load_textdomain' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

		$this->includes();
	}


	/**
	 * Run database migrations when the plugin version changes.
	 *
	 * Compares the stored scc_db_version against the current SCC_VERSION
	 * and applies any necessary data migrations for the current upgrade path.
	 * Updates scc_db_version after all migrations complete.
	 */
	public function upgrade_check(): void {

		$stored_version = get_option( 'scc_db_version', '1.0.0' );

		if ( version_compare( $stored_version, SCC_VERSION, '>=' ) ) {
			return;
		}

		if ( version_compare( $stored_version, '2.0.0', '<' ) ) {
			$this->migrate_v2();
		}

		update_option( 'scc_db_version', SCC_VERSION );
	}


	/**
	 * Migrations for v2.0.0.
	 *
	 * - display_author (1 = hide) → show_author (1 = show)
	 * - display_date   (1 = hide) → show_date   (1 = show)
	 * - Adds enable_front_display = '1' (on by default for existing installs)
	 * - Removes the now-unused display_author and display_date keys
	 */
	private function migrate_v2(): void {

		$settings = get_option( 'course_display_settings', array() );

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		// Migrate display_author → show_author (inverted logic).
		if ( ! isset( $settings['show_author'] ) ) {
			$settings['show_author'] = isset( $settings['display_author'] ) && '1' === (string) $settings['display_author'] ? '0' : '1';
		}
		unset( $settings['display_author'] );

		// Migrate display_date → show_date (inverted logic).
		if ( ! isset( $settings['show_date'] ) ) {
			$settings['show_date'] = isset( $settings['display_date'] ) && '1' === (string) $settings['display_date'] ? '0' : '1';
		}
		unset( $settings['display_date'] );

		// Enable front display for all existing installs.
		if ( ! isset( $settings['enable_front_display'] ) ) {
			$settings['enable_front_display'] = '1';
		}

		update_option( 'course_display_settings', $settings );
	}


	/**
	 * Load the SCC text domain.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain( 'scc', false, SCC_DIR . 'languages/' );
	}


	/**
	 * Enqueue back-end styles, scoped to SCC admin pages only.
	 */
	public function admin_assets(): void {
		wp_register_style( 'scc-admin', SCC_URL . 'assets/css/admin-style.css' );

		if ( 'settings_page_simple_course_creator' === get_current_screen()->id ) {
			wp_enqueue_style( 'scc-admin' );
		}
	}


	/**
	 * Require all plugin class files.
	 */
	private function includes(): void {

		// Admin
		require_once SCC_DIR . 'includes/admin/class-scc-custom-taxonomy.php';
		require_once SCC_DIR . 'includes/admin/class-scc-settings-page.php';
		require_once SCC_DIR . 'includes/admin/class-scc-customizer.php';

		// Display
		require_once SCC_DIR . 'includes/display/class-scc-post-listing.php';
		require_once SCC_DIR . 'includes/display/class-scc-front-display.php';
		require_once SCC_DIR . 'includes/display/class-scc-post-meta.php';
	}
}

new Simple_Course_Creator();
