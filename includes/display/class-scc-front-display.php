<?php
/**
 * SCC_Front_Display class
 *
 * On the blog home, archives, and search results, appends a course
 * indicator to each post excerpt if the post belongs to a course.
 *
 * Can be disabled via Settings > Course Settings > Front Display.
 *
 * Output example:
 *   This post is part of the <a href="...">Course Name</a> course.
 *
 * Filterable text:
 *   - course_leading_text  (default: "This post is part of the")
 *   - course_trailing_text (default: "course.")
 *
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SCC_Front_Display {


	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_filter( 'the_excerpt', array( $this, 'display_course' ) );
	}


	/**
	 * Append a course indicator to excerpts on the front end.
	 *
	 * Only outputs on the blog home, search results, and non-course
	 * archives, and only when front display is enabled in settings.
	 *
	 * @param  string $content The post excerpt.
	 * @return string          Unmodified excerpt (output is echoed directly).
	 */
	public function display_course( $content ) {

		if ( ! $this->is_enabled() ) {
			return $content;
		}

		if ( ! ( is_home() || is_search() || ( is_archive() && ! is_tax( 'course' ) ) ) ) {
			return $content;
		}

		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();

		if ( ! $post_id ) {
			return $content;
		}

		$leading_text  = esc_html( apply_filters( 'course_leading_text',  __( 'This post is part of the', 'scc' ) ) );
		$trailing_text = esc_html( apply_filters( 'course_trailing_text', __( 'course.', 'scc' ) ) );

		$course_info = get_the_term_list( $post_id, 'course', $leading_text . ' ', ', ', ' ' . $trailing_text );

		if ( $course_info && ! is_wp_error( $course_info ) ) {
			echo '<p class="scc-front-display">' . wp_kses_post( $course_info ) . '</p>';
		}

		return $content;
	}


	/**
	 * Check whether front display is enabled in settings.
	 *
	 * @return bool
	 */
	private function is_enabled(): bool {

		$options = get_option( 'scc_display_settings', array() );

		// Default to enabled when the key is absent (fresh install or pre-v2 upgrade).
		if ( ! isset( $options['enable_front_display'] ) ) {
			return true;
		}

		return '1' === $options['enable_front_display'];
	}
}

new SCC_Front_Display();
