<?php
/**
 * SCC course listing output template.
 *
 * THEME OVERRIDE: Create a folder called scc_templates/ in the root of your
 * active theme and copy this file into it. Your version takes priority over
 * the plugin's. The template variables below will be available in your copy.
 *
 *
 * Template variables
 * ------------------
 *
 *   $course          (WP_Term) — the course being rendered
 *   $is_multi_course (bool)    — true when the current post belongs to more
 *                                than one course; false otherwise
 *
 * These are set by SCC_Post_Listing::get_template() before this file is
 * included. Do not access $args directly.
 *
 *
 * Action hooks (in order of appearance)
 * --------------------------------------
 *
 *   scc_before_container              before the outer .scc-post-list div
 *   scc_container_top                 after the opening .scc-post-list div
 *   scc_below_title                   after the course title h3
 *   scc_below_description             after the course description paragraph(s)
 *   scc_before_toggle                 inside the toggle button, before the label
 *   scc_after_toggle                  inside the toggle button, after the label
 *   scc_above_list                    before the opening ol/ul tag
 *   scc_before_list_item ($post_id)   before each li; receives the post ID
 *   scc_after_list_item  ($post_id)   after each li; receives the post ID
 *   scc_below_list                    after the closing ol/ul tag (inside the list)
 *   scc_container_bottom              before the closing .scc-post-list div
 *   scc_after_container               after the closing .scc-post-list div
 *
 *
 * Filter hooks
 * ------------
 *
 *   course_toggle    string  Toggle button label. Default: "full course".
 *
 *   scc_post_types   array   Post types to include in the course listing query.
 *                            Default: array( 'post' ).
 *                            Add CPT support:
 *                              add_filter( 'scc_post_types', function( $types ) {
 *                                  $types[] = 'lesson';
 *                                  return $types;
 *                              } );
 *
 *
 * CSS reference
 * -------------
 *
 * See scc.css (this same directory) for a full annotated reference of every
 * selector this template outputs and the conditions under which each appears.
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

// Fetch all post IDs in this course, sorted per settings.
$post_ids = get_posts( array(
	'post_type'      => apply_filters( 'scc_post_types', array( 'post' ) ),
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

// A course listing is only meaningful on singular views with at least 2 posts.
if ( ! is_singular() || count( $post_ids ) <= 1 ) {
	return;
}

// Title falls back to the course name when no custom Post Listing Title is set.
$post_list_title = get_term_meta( $course->term_id, 'scc_post_list_title', true ) ?: $course->name;

// Course description is the term description set on the Edit Course screen.
$course_desc = term_description( $course->term_id, 'course' );

// Toggle label; filterable per the course_toggle filter.
$course_toggle = apply_filters( 'course_toggle', __( 'full course', 'scc' ) );

// List element type (ol, ul) and optional inline style for no-indicator mode.
$list_tag      = 'ordered' === $options['list_type'] ? 'ol' : 'ul';
$no_list_style = 'none' === $options['list_type'] ? 'style="list-style: none;"' : '';

// Inline style applied to the current post span based on the Current Post Style setting.
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
<div data-course-id="<?php echo absint( $course->term_id ); ?>" class="scc-post-list<?php echo $is_multi_course ? ' scc-multiple-courses' : ' scc-single-course'; ?>">

	<?php do_action( 'scc_container_top' ); ?>

	<?php // Course title — hidden when Post Listing Title is set to an empty string. ?>
	<?php if ( $post_list_title !== '' ) : ?>
		<h3 class="scc-post-list-title"><?php echo esc_html( $post_list_title ); ?></h3>
		<?php do_action( 'scc_below_title' ); ?>
	<?php endif; ?>

	<?php // Course description — only output when a term description exists. ?>
	<?php if ( $course_desc !== '' ) : ?>
		<div class="scc-course-description"><?php echo wp_kses_post( $course_desc ); ?></div>
		<?php do_action( 'scc_below_description' ); ?>
	<?php endif; ?>

	<?php // Toggle button — hidden when Disable JavaScript is on; .scc-show-posts used instead. ?>
	<?php if ( '1' !== $options['disable_js'] ) : ?>
		<button type="button" class="scc-toggle-post-list">
			<?php do_action( 'scc_before_toggle' ); ?>
			<?php echo esc_html( $course_toggle ); ?>
			<?php do_action( 'scc_after_toggle' ); ?>
		</button>
	<?php else : ?>
		<?php $no_js_class = 'scc-show-posts'; ?>
	<?php endif; ?>

	<?php // Post container — hidden by default, revealed by JS toggle; always visible when JS is disabled. ?>
	<div class="scc-post-container<?php echo isset( $no_js_class ) ? ' ' . esc_attr( $no_js_class ) : ''; ?>">

		<?php do_action( 'scc_above_list' ); ?>

		<<?php echo esc_html( $list_tag ); ?> class="scc-posts">
			<?php foreach ( $post_ids as $post_id ) : ?>
				<li class="scc-post-item" <?php echo $no_list_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- hardcoded safe string based on validated setting ?>>
					<?php do_action( 'scc_before_list_item', $post_id ); ?>
					<span class="scc-list-item">
						<?php // Current post rendered as a span; all others as links. ?>
						<?php if ( ! is_singular( false ) || get_the_ID() !== $post_id ) : ?>
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

	</div><!-- .scc-post-container -->

	<?php do_action( 'scc_container_bottom' ); ?>

</div><!-- .scc-post-list[data-course-id="<?php echo absint( $course->term_id ); ?>"] -->
<?php do_action( 'scc_after_container' ); ?>
