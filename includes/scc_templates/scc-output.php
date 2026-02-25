<?php
/**
 * output the HTML for post listings (course container)
 */
global $post;

$course = false;
if ( isset( $args['course'] ) ) {
	$course = $args['course'];
}

// get/set the option values
$default_options = array(
	'display_position' => 'above',
	'list_type'        => 'ordered',
	'scc_orderby'      => 'date',
	'scc_order'        => 'ASC',
	'current_post'     => 'none',
	'disable_js'       => 0,
);
$options         = get_option( 'course_display_settings', $default_options );
$options         = wp_parse_args( $options, $default_options );

// build the post listing based on course
$the_posts     = get_posts( array(
	'post_type'         => 'post',
	'posts_per_page'    => -1,
	'fields'            => 'ids',
	'no_found_rows'     => true,
	'orderby'           => $options['scc_orderby'],
	'order'             => $options['scc_order'],
	'tax_query'         => array(
		array( 'taxonomy' => 'course', 'field' => 'slug', 'terms' => $course->slug )
) ) );

$course_toggle = apply_filters( 'course_toggle', __( 'full course', 'scc' ) );
$posts         = 1;

foreach ( $the_posts as $post_id ) {
	if ( $post_id == $post->ID ) break;	$posts ++;
}

$term_meta			= get_option( 'taxonomy_' . $course->term_id );
$post_list_title    = ! empty( $term_meta['post_list_title'] ) ? $term_meta['post_list_title'] : $course->name;
$course_description = term_description( $course->term_id, 'course' );
$list_container     = $options['list_type'] == 'ordered' ? 'ol' : 'ul';
$no_list            = $options['list_type'] == 'none' ? 'style="list-style: none;"' : '';

switch ( $options['current_post'] ) {
	case 'bold':
		$current_post = ' style="font-weight: bold;"';
		break;
	case 'italic':
		$current_post = ' style="font-style: italic;"';
		break;
	case 'strike':
		$current_post = ' style="text-decoration: line-through;"';
		break;
	default :
		$current_post = '';
}

/**
 * To override...
 *
 * OPTION 1
 *
 * Create a folder called "scc_templates" in the root of your theme
 * and COPY this file into it. It will override the default plugin template.
 *
 * OPTION 2
 *
 * Notice the placement of multiple do_action() functions. It may be easier to
 * hook into this template rather than override it. If you'd like to do so,
 * use the following PHP in your own theme functions file.
 *
 *			function your_function_name() { ?>
 *				-- your custom content --
 *			<?php }
 *			add_action( 'hook_name', 'your_function_name' );
 *
 * To change the "full course" link without overriding the template, use the
 * following PHP in your own theme functions file.
 *
 *			function your_filter_name( $content ) {
 *				$content = str_replace( 'full course', 'complete series', $content );
 *				return $content;
 *			}
 *			add_filter( 'course_toggle', 'your_filter_name' );
 */

if ( is_single() && sizeof( $the_posts ) > 1 ) :
	do_action( 'scc_before_container' );
	?>
	<div id="scc-wrap" class="scc-post-list">
		<?php
		do_action( 'scc_container_top' );
		if ( $post_list_title != '' ) : ?>
			<h3 class="scc-post-list-title"><?php echo $post_list_title; ?></h3>
			<?php
			do_action( 'scc_below_title' );
		endif;
		if ( $course_description != '' ) :
			echo $course_description;
			do_action( 'scc_below_description' );
		endif;

		if (  ! isset( $options['disable_js'] ) || $options['disable_js'] != '1' ) { // only show toggle link if JS is enabled ?>
			<a href="#" class="scc-toggle-post-list">
				<?php
				do_action( 'scc_before_toggle' );
				echo $course_toggle;
				do_action( 'scc_after_toggle' );
				?>
			</a>
		<?php
		} else {
			$no_js_class = 'scc-show-posts';
		}
		?>
		<div class="scc-post-container<?php echo ' ' . ( isset( $no_js_class ) ? $no_js_class : '' ); ?>">
			<?php do_action( 'scc_above_list' ); ?>
			<<?php echo $list_container; ?> class="scc-posts">
				<?php foreach ( $the_posts as $key => $post_id ) : ?>
					<li <?php echo $no_list; ?>>
						<?php do_action( 'scc_before_list_item', $post_id ); ?>
						<span class="scc-list-item">
							<?php
							if ( ! is_single( $post_id ) ) :
								echo '<a href="' . get_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a>';
							else :
								echo '<span class="scc-current-post"' . $current_post . '>' . get_the_title( $post_id ) . '</span>';
							endif;
							?>
						</span>
						<?php do_action( 'scc_after_list_item', $post_id ); ?>
					</li>
				<?php endforeach;
				do_action( 'scc_below_list' ); ?>
			</<?php echo $list_container; ?>>
		</div>
		<?php do_action( 'scc_container_bottom' ); ?>
	</div><!-- #scc-wrap -->
	<?php
do_action( 'scc_after_container' );
endif;