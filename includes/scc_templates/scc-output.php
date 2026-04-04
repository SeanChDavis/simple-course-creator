<?php
/**
 * SCC course listing output template.
 *
 * $course (WP_Term) is set by SCC_Post_Listing::get_template() before
 * this file is included. Do not access $args here.
 *
 * To override this template, create an scc_templates/ directory in your
 * active theme (or child theme) and copy this file into it. The theme
 * version takes priority over the plugin version.
 *
 * Available action hooks (in order of appearance):
 *
 *   scc_before_container   — before the #scc-wrap div
 *   scc_container_top      — after the opening #scc-wrap div
 *   scc_below_title        — after the course title
 *   scc_below_description  — after the course description
 *   scc_before_toggle      — before the toggle link text
 *   scc_after_toggle       — after the toggle link text
 *   scc_above_list         — before the list opening tag
 *   scc_before_list_item   — before each list item (receives $post_id)
 *   scc_after_list_item    — after each list item (receives $post_id)
 *   scc_below_list         — after the list closing tag
 *   scc_container_bottom   — before the closing #scc-wrap div
 *   scc_after_container    — after the #scc-wrap div
 *
 * Available filter hooks:
 *
 *   course_toggle          — toggle link text (default: "full course")
 */

global $post;

$defaults = array(
	'list_type'    => 'ordered',
	'scc_orderby'  => 'date',
	'scc_order'    => 'asc',
	'current_post' => 'none',
	'disable_js'   => '0',
);
$options = wp_parse_args( get_option( 'scc_display_settings', array() ), $defaults );

$post_ids = get_posts( array(
	'post_type'      => 'post',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'no_found_rows'  => true,
	'orderby'        => $options['scc_orderby'],
	'order'          => $options['scc_order'],
	'tax_query'      => array(
		array(
			'taxonomy' => 'course',
			'field'    => 'slug',
			'terms'    => $course->slug,
		),
	),
) );

if ( ! is_single() || count( $post_ids ) <= 1 ) {
	return;
}

$post_list_title = get_term_meta( $course->term_id, 'scc_post_list_title', true ) ?: $course->name;
$course_desc     = term_description( $course->term_id, 'course' );
$course_toggle   = apply_filters( 'course_toggle', __( 'full course', 'scc' ) );
$list_tag        = 'ordered' === $options['list_type'] ? 'ol' : 'ul';
$no_list_style   = 'none' === $options['list_type'] ? 'style="list-style: none;"' : '';

switch ( $options['current_post'] ) {
	case 'bold':
		$current_post_style = ' style="font-weight: bold;"';
		break;
	case 'italic':
		$current_post_style = ' style="font-style: italic;"';
		break;
	case 'strike':
		$current_post_style = ' style="text-decoration: line-through;"';
		break;
	default:
		$current_post_style = '';
}

do_action( 'scc_before_container' );
?>
<div id="scc-wrap" class="scc-post-list">

	<?php do_action( 'scc_container_top' ); ?>

	<?php if ( $post_list_title !== '' ) : ?>
		<h3 class="scc-post-list-title"><?php echo esc_html( $post_list_title ); ?></h3>
		<?php do_action( 'scc_below_title' ); ?>
	<?php endif; ?>

	<?php if ( $course_desc !== '' ) : ?>
		<?php echo wp_kses_post( $course_desc ); ?>
		<?php do_action( 'scc_below_description' ); ?>
	<?php endif; ?>

	<?php if ( '1' !== $options['disable_js'] ) : ?>
		<a href="#" class="scc-toggle-post-list">
			<?php do_action( 'scc_before_toggle' ); ?>
			<?php echo esc_html( $course_toggle ); ?>
			<?php do_action( 'scc_after_toggle' ); ?>
		</a>
	<?php else : ?>
		<?php $no_js_class = 'scc-show-posts'; ?>
	<?php endif; ?>

	<div class="scc-post-container<?php echo isset( $no_js_class ) ? ' ' . esc_attr( $no_js_class ) : ''; ?>">

		<?php do_action( 'scc_above_list' ); ?>

		<<?php echo esc_html( $list_tag ); ?> class="scc-posts">
			<?php foreach ( $post_ids as $post_id ) : ?>
				<li <?php echo $no_list_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded safe string based on validated setting ?>>
					<?php do_action( 'scc_before_list_item', $post_id ); ?>
					<span class="scc-list-item">
						<?php if ( ! is_single( $post_id ) ) : ?>
							<a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
						<?php else : ?>
							<span class="scc-current-post"<?php echo $current_post_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded safe string based on validated setting ?>><?php echo esc_html( get_the_title( $post_id ) ); ?></span>
						<?php endif; ?>
					</span>
					<?php do_action( 'scc_after_list_item', $post_id ); ?>
				</li>
			<?php endforeach; ?>
			<?php do_action( 'scc_below_list' ); ?>
		</<?php echo esc_html( $list_tag ); ?>>

	</div>

	<?php do_action( 'scc_container_bottom' ); ?>

</div><!-- #scc-wrap -->
<?php do_action( 'scc_after_container' ); ?>
