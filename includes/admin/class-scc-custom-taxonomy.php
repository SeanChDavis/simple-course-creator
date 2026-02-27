<?php
/**
 * SCC_Custom_Taxonomy class
 *
 * This class is responsible for creating the new taxonomy and its
 * corresponding options and settings.
 *
 * The taxonomy includes the typical Name, Slug, and Description fields
 * but also adds a new field called "Post Listing Title" to the add/edit
 * screens.
 *
 * Posts can be assigned to the taxonomy from both the manage posts
 * screen and the edit post screens themselves. Posts can only be assigned
 * to one term.
 *
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) exit; // no accessing this file directly


class SCC_Custom_Taxonomy {


	/**
	 * constructor for SCC_Custom_Taxonomy class
	 */
	public function __construct() {

		// load new taxonomy
		add_action( 'init', array( $this, 'register_taxonomy_course' ) );

		// filter the course archive page
		add_action( 'pre_get_posts', array( $this, 'course_archive' ) );

		// add custom meta fields to new term
		add_action( 'course_add_form_fields', array( $this, 'course_meta_title' ), 10, 2 );
		add_action( 'course_edit_form_fields', array( $this, 'edit_course_meta_title' ), 10, 2 );

		// save the term custom meta field inputs
		add_action( 'edited_course', array( $this, 'save_course_meta_title' ), 10, 2 );
		add_action( 'create_course', array( $this, 'save_course_meta_title' ), 10, 2 );

		// taxonomy admin columns
		add_filter( 'manage_edit-post_columns', array( $this, 'columns' ) );
		add_action( 'manage_post_posts_custom_column', array( $this, 'custom_columns' ) );

		// taxonomy admin filtering
		add_action( 'restrict_manage_posts', array( $this, 'course_posts' ) );
	}


	/**
	 * Register "Course" taxonomy
	 *
	 * Also, set up a custom metabox to appear on the edit post screen
	 * using the course_meta_box() method.
	 */
	public function register_taxonomy_course() {
		$labels = array(
			'name'				=> _x( 'Courses', 'scc' ),
			'singular_name'		=> _x( 'Course', 'scc' ),
			'search_items'		=> __( 'Search Courses', 'scc' ),
			'all_items'			=> __( 'All Courses', 'scc' ),
			'parent_item'		=> __( 'Parent Course', 'scc' ),
			'parent_item_colon'	=> __( 'Parent Course:', 'scc' ),
			'edit_item'			=> __( 'Edit Course', 'scc' ),
			'update_item'		=> __( 'Update Course', 'scc' ),
			'add_new_item'		=> __( 'Add New Course', 'scc' ),
			'new_item_name'		=> __( 'New Course Name', 'scc' ),
			'menu_name'			=> __( 'Courses', 'scc' ),
			'popular_items'		=> __( 'Popular Courses', 'scc' )
		);
		$args = array(
			'hierarchical'		=> false,
			'labels'			=> $labels,
			'show_ui'			=> true,
			'query_var'			=> true,
			'rewrite'			=> array( 'slug' => 'course' ),
			'show_in_rest'      => true,
			//'meta_box_cb'		=> array( $this, 'course_meta_box' )
			//TODO: build custom select menu so only one course can be selected
		);
		register_taxonomy( 'course', array( 'post' ), $args );
	}


	/**
	 * Filter the course archive page
	 *
	 * The query should use the settings for the post listing rather than the default
	 * settings for the blog. This method modifies the query for the course archive page.
	 */
	public function course_archive( $query ) {

		if ( $query->is_archive && ! is_admin() ) {
			$queried_object = get_queried_object();
			if ( $queried_object && isset( $queried_object->taxonomy ) && 'course' === $queried_object->taxonomy ) {
				$options = get_option( 'course_display_settings' );
				$query->set( 'posts_per_page', -1 );
				$query->set( 'orderby', $options['scc_orderby'] ?? 'date' );
				$query->set( 'order', $options['scc_order'] ?? 'asc' );
			}
		}
		return $query;
	}


	/**
	 * Determine a post's Course
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
	 * Determine a post's Course ID
	 *
	 * @uses retrieve_course()
	 */
	public function retrieve_course_id( $post_id ) {
		$course = $this->retrieve_course( $post_id );
		if ( $course ) {
			return $course->term_id;
		} else {
			return 0;
		}
	}


	/**
	 * INCOMPATIBLE WITH GUTENBERG - assign post to a course from edit post screen
	 *
	 * TODO: build custom select menu so only one course can be selected
	 *
	 * If a post already belongs to a course, show that course as
	 * a selected option. Whether assigned to a course already or
	 * not, allow the course to be changed.
	 *
	 * A select form is used to prevent more than one course from
	 * being assigned to a post. Though it may make sense based on
	 * content, it doesn't make sense to output multiple post listings
	 * in your content for multiple courses, which is the point of the
	 * plugin.
	 *
	 * @uses retrieve_course_id()
	 */
	public function course_meta_box( $post ) {

		// get the current course for the post if set
		$current_course = $this->retrieve_course_id( $post->ID );

		// get a list of all courses and the taxonomy
		$tax = get_taxonomy( 'course' );
		$courses = get_terms( 'course', array( 'hide_empty' => false, 'orderby' => 'name' ) );
		?>
		<div id="taxonomy-<?php echo lcfirst( $tax->labels->name ); ?>" class="categorydiv">
			<label class="screen-reader-text">
				<?php echo $tax->labels->parent_item_colon; ?>
			</label>
			<select name="tax_input[course]" style="width:100%">
				<option value="0"><?php _e( 'Select Course', 'scc' ) ?></option>
				<?php foreach ( $courses as $course ) : ?>
					<option value="<?php echo esc_attr( $course->slug ); ?>" <?php selected( $current_course, $course->term_id ); ?>><?php echo esc_html( $course->name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		do_action( 'scc_meta_box_add', $post->ID ); // allow add-ons to add to this metabox
	}


	/**
	 * Add a title field when creating a course
	 *
	 * Under the "Posts" dashboard menu, "Courses" is a submenu page
	 * used to create new Courses. During the creation process, which
	 * is exactly the same as creating a new category or tag, a new
	 * field is available for adding the "Post Listing Title."
	 *
	 * This title appears on the actual posts assigned to an article.
	 * It is the title for the container holding the post listing.
	 */
	public function course_meta_title() { ?>
		<div class="form-field">
			<label for="term_meta[post_list_title]"><?php _e( 'Post Listing Title', 'scc' ); ?></label>
			<input type="text" name="term_meta[post_list_title]" id="term_meta[post_list_title]" value="">
			<p class="description"><?php _e( 'This is the displayed title of your post listing container.','scc' ); ?></p>
		</div>
	<?php }


	/**
	 * Add a title field for editing an existing course
	 *
	 * Now that the "Post Listing Title" field is in place, users need
	 * to be able to edit it on the term edit screen. This method adds
	 * the form field to the term edit screen and populates it with
	 * the saved title if it exists.
	 */
	public function edit_course_meta_title( $term ) {

		// retrieve the existing value for the course title
		$term_meta = get_option( 'taxonomy_' . $term->term_id )['post_list_title'] ?? '';
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="term_meta[post_list_title]"><?php _e( 'Post Listing Title', 'scc' ); ?></label>
			</th>
			<td>
				<input type="text" name="term_meta[post_list_title]" id="term_meta[post_list_title]" value="<?php echo $term_meta ?: ''; ?>">
				<p class="description"><?php _e( 'This is the displayed title of your post listing container.','scc' ); ?></p>
			</td>
		</tr>
	<?php }


	/**
	 * Save the course title
	 *
	 * From both the two above methods, save any edits made to
	 * the "Post Listing Title" field.
	 *
	 * @used_by course_meta_title() and edit_course_meta_title()
	 */
	public function save_course_meta_title( $term_id ) {
		if ( isset( $_POST['term_meta'] ) ) {
			$course_id = $term_id;
			$term_meta = get_option( "taxonomy_$course_id" );
			$course_keys = array_keys( $_POST['term_meta'] );
			foreach ( $course_keys as $key ) {
				if ( isset ( $_POST['term_meta'][$key] ) ) {
					$term_meta[$key] = $_POST['term_meta'][$key];
				}
			}
			update_option( "taxonomy_$course_id", $term_meta );
		}
	}


	/**
	 * output admin column header to the manage posts screen
	 */
	public function columns( $columns ) {
		if ( ! is_array( $columns ) ) {
			$new_columns = array();
		}
		foreach ( $columns as $key => $column ) {
			$new_columns[ $key ] = $column;
			if ( 'categories' == $key ) {
				$new_columns[ 'course' ] = __( 'Course', 'scc' );
			}
		}
		return $new_columns;
	}


	/**
	 * Output admin column values
	 *
	 * On the manage posts screen beneath the header added in the columns()
	 * method, output values for each post based on whether it is
	 * assigned to a course. If so, output the course name. If not,
	 * output a message.
	 *
	 * @uses retrieve_course()
	 */
	public function custom_columns( $column ) {
		global $post;
		if ( 'course' == $column ) {
			$current_course = $this->retrieve_course( $post->ID );
			if ( $current_course ) {
				echo '<a href="' . esc_url( admin_url( 'edit.php?course=' . $current_course->slug ) ) . '">' . esc_html( $current_course->name ) . '</a>';
			} else {
				_e( 'no course selected', 'scc' );
			}
		}
	}


	/**
	 * Filter posts by a particular course on the manage posts screen
	 */
	public function course_posts() {
		global $typenow, $wp_query;
	    if ( $typenow != 'post' ) {
	    	return;
	    }
	    $current_course = isset( $_REQUEST['course'] ) ? sanitize_text_field( $_REQUEST['course'] ) : '';
	    $all_courses = get_terms( 'course', array( 'hide_empty' => true, 'orderby' => 'name' ) );
	    if ( empty( $all_courses ) ) {
	    	return;
	    }
	    ?>
		<select name="course">
			<option value=""><?php _e( 'Show all courses', 'scc' ) ?></option>
			<?php foreach ( $all_courses as $course ) : ?>
				<option value="<?php echo esc_attr( $course->slug ); ?>" <?php selected( $current_course, $course->slug ); ?>><?php echo esc_html( $course->name ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}
}
new SCC_Custom_Taxonomy();