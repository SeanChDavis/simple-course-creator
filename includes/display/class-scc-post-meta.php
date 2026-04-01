<?php
/**
 * SCC_Post_Meta class
 *
 * Hooks into the scc_after_list_item action to output author and date
 * information beneath each post in the course listing.
 *
 * Visibility is controlled via Settings > Course Settings > Post Meta.
 * Both fields are shown by default.
 *
 * Output text is filterable:
 *   - written_by (default: "written by")
 *   - written_on (default: "on")
 *
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SCC_Post_Meta {


	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'scc_after_list_item',  array( $this, 'output_post_meta' ) );
		add_action( 'wp_enqueue_scripts',   array( $this, 'frontend_styles' ) );
	}


	/**
	 * Output author and/or date beneath a course listing item.
	 *
	 * Hooked to scc_after_list_item, which passes the post ID.
	 *
	 * @param int $post_id The ID of the list item post.
	 */
	public function output_post_meta( $post_id ) {

		$options = $this->get_options();

		$show_author = '1' === $options['show_author'];
		$show_date   = '1' === $options['show_date'];

		if ( ! $show_author && ! $show_date ) {
			return;
		}

		$written_by = esc_html( apply_filters( 'written_by', __( 'written by', 'scc' ) ) );
		$written_on = esc_html( apply_filters( 'written_on', __( 'on', 'scc' ) ) );

		echo '<p class="scc-post-meta">';

		if ( $show_author ) {
			$author_name = get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) );
			echo '<span class="sccpm-author">' . $written_by . '</span> ' . esc_html( $author_name );
		}

		if ( $show_date ) {
			if ( $show_author ) {
				echo ' <span class="sccpm-date">' . $written_on . '</span> ';
			}
			echo esc_html( get_the_date( '', $post_id ) );
		}

		echo '</p>';
	}


	/**
	 * Enqueue the post meta stylesheet on single post pages.
	 */
	public function frontend_styles() {

		if ( is_single() ) {
			wp_enqueue_style( 'scc-post-meta-css', SCC_URL . 'assets/css/scc-post-meta.css', array(), SCC_VERSION );
		}
	}


	/**
	 * Return post meta settings with defaults applied.
	 *
	 * @return array
	 */
	private function get_options(): array {

		$defaults = array(
			'show_author' => '1',
			'show_date'   => '1',
		);

		return wp_parse_args( get_option( 'course_display_settings', array() ), $defaults );
	}
}

new SCC_Post_Meta();
