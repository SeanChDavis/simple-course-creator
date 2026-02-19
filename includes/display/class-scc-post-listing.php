<?php
/**
 * SCC_Post_Listing class
 *
 * This class gathers all the necessary information for creating and
 * outputting the post listing on posts that are part of courses.
 *
 * The markup for output (as well as its corresponding front-end CSS
 * and JS files) is located in a remote folder so that users can
 * override the files directly from their active theme. However, the
 * complete setup of the system starts here.
 *
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // no accessing this file directly


class SCC_Post_Listing {


	/**
	 * constructor for SCC_Post_Listing class
	 */
	public function __construct() {

		// display post listing in content
		add_filter( 'the_content', array( $this, 'post_listing' ) );

		// load the correct post listing stylesheet based on hierarchy
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
	}


	/**
	 * determine a post's Course
	 */
	public function retrieve_course( $post_id ) {
		$course = wp_get_post_terms( $post_id, 'course' );
		if ( ! is_wp_error( $course ) && ! empty( $course ) && is_array( $course ) ) {
			$course = current( $course );
		} else {
			$course = false;
		}
		return $course;
	}


	/**
	 * add post listing to content
	 *
	 * @uses retrieve_course()
	 */
	public function post_listing( $content ) {
		global $post;
		$default_options = array(
			'display_position'  => 'above',
			'disable_js'        => 0,
		);
		$options = get_option( 'course_display_settings', $default_options );
		$options = wp_parse_args( $options, $default_options );

		if ( ! is_single() ) {
			return $content;
		}

		$course = $this->retrieve_course( $post->ID );

		// if there's no course, just display the content
		if ( ! $course ) {
			return $content;
		}

		if ( ! isset( $options['disable_js'] ) || $options['disable_js'] != '1' ) {
			wp_enqueue_script( 'scc-post-list-js' );
		}

		ob_start();

		// include *the appropriate* template file
		$this->get_template( 'scc-output.php', array( 'course' => $course ) );

		$post_listing = ob_get_clean();

		// display full course based on plugin display settings
		switch ( $options['display_position'] ) {
			case 'below':
				$content = $content . $post_listing;
				break;
			case 'both':
				$content = $post_listing . $content . $post_listing;
				break;
			case 'hide':
				$content = $content;
				break;
			default:
				$content = $post_listing . $content;
		}
		return $content;
	}


	/**
	 * get and include template files
	 *
	 * @uses locate_template()
	 */
	public function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		if ( $args && is_array($args) ) {
			extract( $args );
		}
		include( $this->locate_template( $template_name, $template_path, $default_path ) );
	}


	/**
	 * locate a template and return the path for inclusion
	 *
	 * @used_by get_template()
	 */
	public function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'scc_templates';
		}
		if ( ! $default_path ) {
			$default_path  = SCC_DIR . 'includes/scc_templates/';
		}

		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
				trailingslashit( $template_path ) . $template_name,
				$template_name
			)
		);

		// Get default template
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}
		return $template;
	}


	/**
	 * setup stylesheet and script for post listing
	 *
	 * @credits stylesheet hierarchy approach by Easy Digital Downloads
	 */
	public function frontend_styles() {

		// if the active theme has a properly named JS file in the correct
		// location within the theme, store it in a variable
		$child_theme_scc_script = trailingslashit( get_stylesheet_directory() ) . 'scc_templates/scc-post-listing.js';
		$parent_theme_scc_script = trailingslashit( get_template_directory() ) . 'scc_templates/scc-post-listing.js';

		// check to see if the above variables actually had files
		// if so, store those variables in a new variable
		// $primary_script will only hold one value based on which files exist
		if ( file_exists( $child_theme_scc_script ) ) {
			$primary_script = trailingslashit( get_stylesheet_directory_uri() ) . 'scc_templates/scc-post-listing.js';
		} elseif ( file_exists( $parent_theme_scc_script ) ) {
			$primary_script = trailingslashit( get_template_directory_uri() ) . 'scc_templates/scc-post-listing.js';
		} else {
			$primary_script = SCC_URL . 'includes/scc_templates/scc-post-listing.js';
		}

		// if the active theme has a properly named CSS file in the correct
		// location within the theme, store it in a variable
		$child_theme_scc_style = trailingslashit( get_stylesheet_directory() ) . 'scc_templates/scc.css';
		$parent_theme_scc_style = trailingslashit( get_template_directory() ) . 'scc_templates/scc.css';

		// check to see if the above variables actually had files
		// if so, store those variables in a new variable
		// $primary_style will only hold one value based on which files exist
		if ( file_exists( $child_theme_scc_style ) ) {
			$primary_style = trailingslashit( get_stylesheet_directory_uri() ) . 'scc_templates/scc.css';
		} elseif ( file_exists( $parent_theme_scc_style ) ) {
			$primary_style = trailingslashit( get_template_directory_uri() ) . 'scc_templates/scc.css';
		} else {
			$primary_style = SCC_URL . 'includes/scc_templates/scc.css';
		}

		// register and enqueue the appropriate assets based on above checks
		if ( is_single() ) {
			wp_enqueue_style( 'scc-post-listing-css', $primary_style );
			wp_register_script( 'scc-post-list-js', $primary_script, array( 'jquery' ), SCC_VERSION, true );
		}
	}
}
new SCC_Post_Listing();