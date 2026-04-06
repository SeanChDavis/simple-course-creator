<?php
/**
 * SCC_Custom_Taxonomy class
 *
 * Registers the 'course' taxonomy and handles all related admin UI:
 * the Post Listing Title meta field on add/edit term screens, a Course
 * column on the manage posts screen, and a course filter dropdown.
 *
 * The post types the taxonomy is registered on are filterable via
 * apply_filters( 'scc_post_types', array( 'post' ) ). Column hooks
 * and the filter dropdown are registered dynamically for each post type.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SCC_Custom_Taxonomy {


	/**
	 * Constructor — register all hooks.
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'register_taxonomy_course' ) );
		add_action( 'init', array( $this, 'register_column_hooks' ) );
		add_action( 'pre_get_posts', array( $this, 'course_archive' ) );

		add_action( 'course_add_form_fields',  array( $this, 'course_meta_title' ), 10, 2 );
		add_action( 'course_edit_form_fields', array( $this, 'edit_course_meta_title' ), 10, 2 );

		add_action( 'create_course',  array( $this, 'save_course_meta_title' ), 10, 2 );
		add_action( 'edited_course',  array( $this, 'save_course_meta_title' ), 10, 2 );

		add_action( 'restrict_manage_posts', array( $this, 'course_posts' ) );
	}


	/**
	 * Register the 'course' taxonomy.
	 *
	 * The post types it applies to are filterable:
	 *
	 *   add_filter( 'scc_post_types', function( $types ) {
	 *       $types[] = 'my_custom_post_type';
	 *       return $types;
	 *   } );
	 *
	 * Non-hierarchical. REST API enabled.
	 */
	public function register_taxonomy_course() {

		$labels = array(
			'name'              => _x( 'Courses', 'taxonomy general name', 'scc' ),
			'singular_name'     => _x( 'Course', 'taxonomy singular name', 'scc' ),
			'search_items'      => __( 'Search Courses', 'scc' ),
			'all_items'         => __( 'All Courses', 'scc' ),
			'parent_item'       => __( 'Parent Course', 'scc' ),
			'parent_item_colon' => __( 'Parent Course:', 'scc' ),
			'edit_item'         => __( 'Edit Course', 'scc' ),
			'update_item'       => __( 'Update Course', 'scc' ),
			'add_new_item'      => __( 'Add New Course', 'scc' ),
			'new_item_name'     => __( 'New Course Name', 'scc' ),
			'menu_name'         => __( 'Courses', 'scc' ),
			'popular_items'     => __( 'Popular Courses', 'scc' ),
		);

		$args = array(
			'hierarchical' => false,
			'labels'       => $labels,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => array( 'slug' => 'course' ),
			'show_in_rest' => true,
		);

		register_taxonomy( 'course', apply_filters( 'scc_post_types', array( 'post' ) ), $args );
	}


	/**
	 * Register the Course column and filter hooks for each supported post type.
	 *
	 * Fires on init so the scc_post_types filter value is available.
	 */
	public function register_column_hooks() {

		$post_types = apply_filters( 'scc_post_types', array( 'post' ) );

		foreach ( $post_types as $post_type ) {
			add_filter( "manage_edit-{$post_type}_columns",        array( $this, 'columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( $this, 'custom_columns' ) );
		}
	}


	/**
	 * Apply display settings to the course taxonomy archive query.
	 *
	 * @param WP_Query $query The current query object (passed by reference).
	 */
	public function course_archive( $query ) {

		if ( $query->is_archive && ! is_admin() ) {
			$queried_object = get_queried_object();
			if ( $queried_object && isset( $queried_object->taxonomy ) && 'course' === $queried_object->taxonomy ) {
				$options = get_option( 'scc_display_settings', array() );
				$query->set( 'posts_per_page', -1 );
				$query->set( 'orderby', $options['scc_orderby'] ?? 'date' );
				$query->set( 'order',   $options['scc_order']   ?? 'asc' );
			}
		}
	}


	/**
	 * Output the Post Listing Title field on the Add Course screen.
	 */
	public function course_meta_title() {
		?>
		<div class="form-field">
			<?php wp_nonce_field( 'scc_course_meta', 'scc_course_meta_nonce' ); ?>
			<label for="term_meta[post_list_title]"><?php esc_html_e( 'Post Listing Title', 'scc' ); ?></label>
			<input type="text" name="term_meta[post_list_title]" id="term_meta[post_list_title]" value="">
			<p class="description"><?php esc_html_e( 'This is the displayed title of your post listing container.', 'scc' ); ?></p>
		</div>
		<?php
	}


	/**
	 * Output the Post Listing Title field on the Edit Course screen.
	 *
	 * @param WP_Term $term The current term object.
	 */
	public function edit_course_meta_title( $term ) {

		$value = get_term_meta( $term->term_id, 'scc_post_list_title', true );
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="term_meta[post_list_title]"><?php esc_html_e( 'Post Listing Title', 'scc' ); ?></label>
			</th>
			<td>
				<?php wp_nonce_field( 'scc_course_meta', 'scc_course_meta_nonce' ); ?>
				<input type="text" name="term_meta[post_list_title]" id="term_meta[post_list_title]" value="<?php echo esc_attr( $value ); ?>">
				<p class="description"><?php esc_html_e( 'This is the displayed title of your post listing container.', 'scc' ); ?></p>
			</td>
		</tr>
		<?php
	}


	/**
	 * Save the Post Listing Title field on create and edit.
	 *
	 * @param int $term_id The term ID being saved.
	 */
	public function save_course_meta_title( $term_id ) {

		if ( ! isset( $_POST['scc_course_meta_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['scc_course_meta_nonce'], 'scc_course_meta' ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}

		if ( ! isset( $_POST['term_meta'] ) || ! is_array( $_POST['term_meta'] ) ) {
			return;
		}

		if ( isset( $_POST['term_meta']['post_list_title'] ) ) {
			update_term_meta( $term_id, 'scc_post_list_title', sanitize_text_field( $_POST['term_meta']['post_list_title'] ) );
		}
	}


	/**
	 * Add a Course column to the manage posts screen.
	 *
	 * @param  array $columns Existing columns.
	 * @return array          Modified columns with Course inserted after Categories.
	 */
	public function columns( $columns ) {

		if ( ! is_array( $columns ) ) {
			return $columns;
		}

		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;
			if ( 'categories' === $key ) {
				$new_columns['course'] = __( 'Course', 'scc' );
			}
		}

		// Fallback: if no categories column, append at the end.
		if ( ! isset( $new_columns['course'] ) ) {
			$new_columns['course'] = __( 'Course', 'scc' );
		}

		return $new_columns;
	}


	/**
	 * Output the course value(s) for each post row in the Course column.
	 *
	 * If a post belongs to multiple courses, all are shown as comma-separated links.
	 *
	 * @param string $column The current column slug.
	 */
	public function custom_columns( $column ) {

		global $post;

		if ( 'course' !== $column ) {
			return;
		}

		$courses = wp_get_post_terms( $post->ID, 'course' );

		if ( is_wp_error( $courses ) || empty( $courses ) ) {
			esc_html_e( 'no course selected', 'scc' );
			return;
		}

		$links = array();

		foreach ( $courses as $course ) {
			$links[] = '<a href="' . esc_url( admin_url( 'edit.php?course=' . $course->slug ) ) . '">' . esc_html( $course->name ) . '</a>';
		}

		echo implode( ', ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- URLs and names already escaped above
	}


	/**
	 * Output a course filter dropdown on the manage posts screen.
	 *
	 * @param string $post_type The current post type slug.
	 */
	public function course_posts( $post_type ) {

		$allowed_post_types = apply_filters( 'scc_post_types', array( 'post' ) );

		if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
			return;
		}

		$current_course = isset( $_REQUEST['course'] ) ? sanitize_text_field( $_REQUEST['course'] ) : '';
		$all_courses    = get_terms( 'course', array( 'hide_empty' => true, 'orderby' => 'name' ) );

		if ( empty( $all_courses ) ) {
			return;
		}
		?>
		<select name="course">
			<option value=""><?php esc_html_e( 'Show all courses', 'scc' ); ?></option>
			<?php foreach ( $all_courses as $course ) : ?>
				<option value="<?php echo esc_attr( $course->slug ); ?>" <?php selected( $current_course, $course->slug ); ?>>
					<?php echo esc_html( $course->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}

new SCC_Custom_Taxonomy();
