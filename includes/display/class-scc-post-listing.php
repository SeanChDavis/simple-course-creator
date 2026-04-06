<?php
/**
 * SCC_Post_Listing class
 *
 * Hooks the course post listing into single post content and manages
 * front-end asset loading with theme override support.
 *
 * Template files (scc-output.php, scc.css, scc-post-listing.js) can be
 * overridden by placing them in a scc_templates/ directory in the active
 * theme or child theme.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SCC_Post_Listing {


	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_filter( 'the_content',       array( $this, 'post_listing' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );
	}


	/**
	 * Return all course terms assigned to a post.
	 *
	 * @param  int   $post_id
	 * @return WP_Term[]  Array of course terms, empty array if none assigned.
	 */
	public function retrieve_courses( $post_id ) {

		$courses = wp_get_post_terms( $post_id, 'course' );

		if ( is_wp_error( $courses ) || empty( $courses ) ) {
			return array();
		}

		return $courses;
	}


	/**
	 * Inject the course post listing into single post content.
	 *
	 * If a post belongs to multiple courses, a separate listing is rendered
	 * for each one, wrapped in a shared .scc-course-group container.
	 * Position is controlled by the display_position setting:
	 * above (default), below, both, or hide.
	 *
	 * @param  string $content The post content.
	 * @return string          Content with course listing(s) injected.
	 */
	public function post_listing( $content ) {

		global $post;

		if ( ! is_singular() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$courses = $this->retrieve_courses( $post->ID );

		if ( empty( $courses ) ) {
			return $content;
		}

		$options  = $this->get_options();
		$position = $options['display_position'];

		if ( 'hide' === $position ) {
			return $content;
		}

		if ( '1' !== $options['disable_js'] ) {
			wp_enqueue_script( 'scc-post-list-js' );
		}

		$is_multi     = count( $courses ) > 1;
		$post_listing = '';

		foreach ( $courses as $course ) {
			ob_start();
			$this->get_template( 'scc-output.php', array(
				'course'         => $course,
				'is_multi_course' => $is_multi,
			) );
			$post_listing .= ob_get_clean();
		}

		if ( $is_multi ) {
			$post_listing = '<div class="scc-course-group">' . $post_listing . '</div>';
		}

		switch ( $position ) {
			case 'below':
				return $content . $post_listing;
			case 'both':
				return $post_listing . $content . $post_listing;
			default:
				return $post_listing . $content;
		}
	}


	/**
	 * Locate and include a template file.
	 *
	 * Checks child theme then parent theme before falling back to the
	 * plugin's own templates directory.
	 *
	 * @param string $template_name Filename of the template (e.g. 'scc-output.php').
	 * @param array  $args          Variables to expose to the template.
	 *                              Accepts 'course' (WP_Term) and 'is_multi_course' (bool).
	 */
	public function get_template( $template_name, $args = array() ) {

		$course          = isset( $args['course'] )          ? $args['course']          : null;
		$is_multi_course = isset( $args['is_multi_course'] ) ? $args['is_multi_course'] : false;

		include $this->locate_template( $template_name, $course ? $course->slug : '' );
	}


	/**
	 * Resolve the path to a template file, respecting the theme override hierarchy.
	 *
	 * @param  string $template_name Filename of the template.
	 * @param  string $slug          Course slug, passed through to locate_template().
	 * @return string                Absolute path to the resolved template file.
	 */
	public function locate_template( $template_name, $slug = '' ) {

		$template = locate_template(
			array(
				trailingslashit( 'scc_templates' ) . $template_name,
				$template_name,
			),
			false,
			false,
			array( 'slug' => $slug )
		);

		if ( ! $template ) {
			$template = SCC_DIR . 'includes/scc_templates/' . $template_name;
		}

		return $template;
	}


	/**
	 * Register and enqueue front-end styles and scripts.
	 *
	 * Checks child theme, then parent theme, then falls back to the
	 * plugin's own files. Assets are only enqueued on singular post pages.
	 *
	 * @credits Stylesheet hierarchy approach inspired by Easy Digital Downloads.
	 */
	public function frontend_styles() {

		if ( ! is_singular() ) {
			return;
		}

		$primary_script = $this->resolve_asset_url( 'scc_templates/scc-post-listing.js', SCC_URL . 'includes/scc_templates/scc-post-listing.js' );
		$primary_style  = $this->resolve_asset_url( 'scc_templates/scc.css',             SCC_URL . 'includes/scc_templates/scc.css' );

		wp_enqueue_style( 'scc-post-listing-css', $primary_style );
		wp_register_script( 'scc-post-list-js', $primary_script, array( 'jquery' ), SCC_VERSION, true );
	}


	/**
	 * Resolve the URL for a theme-overridable asset.
	 *
	 * Checks child theme directory, then parent theme directory, then
	 * returns the fallback URL.
	 *
	 * @param  string $relative_path Path relative to theme root (e.g. 'scc_templates/scc.css').
	 * @param  string $fallback_url  Plugin asset URL to use if no theme override is found.
	 * @return string                Resolved asset URL.
	 */
	private function resolve_asset_url( $relative_path, $fallback_url ) {

		$child_path  = trailingslashit( get_stylesheet_directory() ) . $relative_path;
		$parent_path = trailingslashit( get_template_directory() ) . $relative_path;

		if ( file_exists( $child_path ) ) {
			return trailingslashit( get_stylesheet_directory_uri() ) . $relative_path;
		}

		if ( file_exists( $parent_path ) ) {
			return trailingslashit( get_template_directory_uri() ) . $relative_path;
		}

		return $fallback_url;
	}


	/**
	 * Return settings with defaults applied.
	 *
	 * @return array
	 */
	private function get_options(): array {

		$defaults = array(
			'display_position' => 'above',
			'disable_js'       => '0',
		);

		return wp_parse_args( get_option( 'scc_display_settings', array() ), $defaults );
	}
}

new SCC_Post_Listing();
