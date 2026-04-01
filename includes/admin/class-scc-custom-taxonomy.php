<?php
/**
 * SCC_Custom_Taxonomy class
 *
 * Registers the 'course' taxonomy and handles all related admin UI:
 * the Post Listing Title meta field on add/edit term screens, a Course
 * column on the manage posts screen, and a course filter dropdown.
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
		add_action( 'pre_get_posts', array( $this, 'course_archive' ) );

		add_action( 'course_add_form_fields',  array( $this, 'course_meta_title' ), 10, 2 );
		add_action( 'course_edit_form_fields', array( $this, 'edit_course_meta_title' ), 10, 2 );

		add_action( 'create_course',  array( $this, 'save_course_meta_title' ), 10, 2 );
		add_action( 'edited_course',  array( $this, 'save_course_meta_title' ), 10, 2 );

		add_filter( 'manage_edit-post_columns',    array( $this, 'columns' ) );
		add_action( 'manage_post_posts_custom_column', array( $this, 'custom_columns' ) );

		add_action( 'restrict_manage_posts', array( $this, 'course_posts' ) );
	}


	/**
	 * Register the 'course' taxonomy.
	 *
	 * Non-hierarchical, applied to posts only. REST API enabled.
	 * A custom metabox callback is stubbed but not yet active — the
	 * default tag-style checkbox UI is used until a select-only UI
	 * is built to enforce single-course assignment.
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

		register_taxonomy( 'course', array( 'post' ), $args );
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
				$options = get_option( 'course_display_settings', array() );
				$query->set( 'posts_per_page', -1 );
				$query->set( 'orderby', $options['scc_orderby'] ?? 'date' );
				$query->set( 'order',   $options['scc_order']   ?? 'asc' );
			}
		}
	}


	/**
	 * Return the first course term assigned to a post.
	 *
	 * @param  int            $post_id
	 * @return WP_Term|false  The course term, or false if none assigned.
	 */
	public function retrieve_course( $post_id ) {

		$course = wp_get_post_terms( $post_id, 'course' );

		if ( ! is_wp_error( $course ) && ! empty( $course ) && is_array( $course ) ) {
			return current( $course );
		}

		return false;
	}


	/**
	 * Return the term ID of the first course assigned to a post.
	 *
	 * @param  int $post_id
	 * @return int Term ID, or 0 if none assigned.
	 */
	public function retrieve_course_id( $post_id ) {

		$course = $this->retrieve_course( $post_id );

		return $course ? $course->term_id : 0;
	}


	/**
	 * Custom metabox for assigning a single course from the edit post screen.
	 *
	 * Not currently active — registered taxonomy uses default UI.
	 * TODO: activate once a select-only UI is built to enforce single-course
	 * assignment per post.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function course_meta_box( $post ) {

		$current_course = $this->retrieve_course_id( $post->ID );
		$tax            = get_taxonomy( 'course' );
		$courses        = get_terms( 'course', array( 'hide_empty' => false, 'orderby' => 'name' ) );
		?>
		<div id="taxonomy-<?php echo esc_attr( lcfirst( $tax->labels->name ) ); ?>" class="categorydiv">
			<label class="screen-reader-text">
				<?php echo esc_html( $tax->labels->parent_item_colon ); ?>
			</label>
			<select name="tax_input[course]" style="width:100%">
				<option value="0"><?php esc_html_e( 'Select Course', 'scc' ); ?></option>
				<?php foreach ( $courses as $course ) : ?>
					<option value="<?php echo esc_attr( $course->slug ); ?>" <?php selected( $current_course, $course->term_id ); ?>>
						<?php echo esc_html( $course->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		do_action( 'scc_meta_box_add', $post->ID );
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

		$term_meta = get_option( 'taxonomy_' . $term->term_id, array() );
		$value     = ! empty( $term_meta['post_list_title'] ) ? $term_meta['post_list_title'] : '';
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

		$term_meta = get_option( 'taxonomy_' . $term_id, array() );

		foreach ( $_POST['term_meta'] as $key => $value ) {
			$term_meta[ sanitize_key( $key ) ] = sanitize_text_field( $value );
		}

		update_option( 'taxonomy_' . $term_id, $term_meta );
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

		return $new_columns;
	}


	/**
	 * Output the course value for each post row in the Course column.
	 *
	 * @param string $column The current column slug.
	 */
	public function custom_columns( $column ) {

		global $post;

		if ( 'course' !== $column ) {
			return;
		}

		$current_course = $this->retrieve_course( $post->ID );

		if ( $current_course ) {
			echo '<a href="' . esc_url( admin_url( 'edit.php?course=' . $current_course->slug ) ) . '">' . esc_html( $current_course->name ) . '</a>';
		} else {
			esc_html_e( 'no course selected', 'scc' );
		}
	}


	/**
	 * Output a course filter dropdown on the manage posts screen.
	 */
	public function course_posts() {

		global $typenow;

		if ( 'post' !== $typenow ) {
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
