<?php
/**
 * SCC_Settings_Page class
 *
 * Creates the Course Settings submenu page under Settings. Registers
 * all plugin settings across three sections: Course Container Display,
 * Post Meta, and Front Display.
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SCC_Settings_Page {


	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}


	/**
	 * Register the Course Settings submenu page under Settings.
	 */
	public function settings_menu(): void {
		add_options_page(
			SCC_NAME,
			__( 'Course Settings', 'scc' ),
			'manage_options',
			'simple_course_creator',
			array( $this, 'settings_page' )
		);
	}


	/**
	 * Register all settings, sections, and fields.
	 */
	public function register_settings(): void {

		register_setting(
			'scc_display_settings',
			'scc_display_settings',
			array( $this, 'save_settings' )
		);

		// -------------------------------------------------------------------------
		// Section: Course Container Display
		// -------------------------------------------------------------------------

		add_settings_section(
			'course_display_settings',
			__( 'Course Container Display', 'scc' ),
			array( $this, 'section_course_display' ),
			'simple_course_creator'
		);

		add_settings_field(
			'display_position',
			__( 'Container Position', 'scc' ),
			array( $this, 'field_display_position' ),
			'simple_course_creator',
			'course_display_settings',
			array( 'label_for' => 'display_position' )
		);

		add_settings_field(
			'list_type',
			__( 'List Style', 'scc' ),
			array( $this, 'field_list_type' ),
			'simple_course_creator',
			'course_display_settings',
			array( 'label_for' => 'list_type' )
		);

		add_settings_field(
			'scc_orderby',
			__( 'Sort Posts By', 'scc' ),
			array( $this, 'field_orderby' ),
			'simple_course_creator',
			'course_display_settings',
			array( 'label_for' => 'scc_orderby' )
		);

		add_settings_field(
			'scc_order',
			__( 'Sort Order', 'scc' ),
			array( $this, 'field_order' ),
			'simple_course_creator',
			'course_display_settings',
			array( 'label_for' => 'scc_order' )
		);

		add_settings_field(
			'current_post',
			__( 'Current Post Style', 'scc' ),
			array( $this, 'field_current_post' ),
			'simple_course_creator',
			'course_display_settings',
			array( 'label_for' => 'current_post' )
		);

		add_settings_field(
			'disable_js',
			__( 'Disable JavaScript', 'scc' ),
			array( $this, 'field_disable_js' ),
			'simple_course_creator',
			'course_display_settings',
			array( 'label_for' => 'disable_js' )
		);

		// -------------------------------------------------------------------------
		// Section: Post Meta
		// -------------------------------------------------------------------------

		add_settings_section(
			'scc_post_meta_settings',
			__( 'Post Meta', 'scc' ),
			array( $this, 'section_post_meta' ),
			'simple_course_creator'
		);

		add_settings_field(
			'show_author',
			__( 'Show Author', 'scc' ),
			array( $this, 'field_show_author' ),
			'simple_course_creator',
			'scc_post_meta_settings',
			array( 'label_for' => 'show_author' )
		);

		add_settings_field(
			'show_date',
			__( 'Show Date', 'scc' ),
			array( $this, 'field_show_date' ),
			'simple_course_creator',
			'scc_post_meta_settings',
			array( 'label_for' => 'show_date' )
		);

		// -------------------------------------------------------------------------
		// Section: Front Display
		// -------------------------------------------------------------------------

		add_settings_section(
			'scc_front_display_settings',
			__( 'Front Display', 'scc' ),
			array( $this, 'section_front_display' ),
			'simple_course_creator'
		);

		add_settings_field(
			'enable_front_display',
			__( 'Enable Front Display', 'scc' ),
			array( $this, 'field_enable_front_display' ),
			'simple_course_creator',
			'scc_front_display_settings',
			array( 'label_for' => 'enable_front_display' )
		);
	}


	// =============================================================================
	// Section callbacks
	// =============================================================================

	/**
	 * Section description — Course Container Display.
	 */
	public function section_course_display(): void {
		echo '<p>' . esc_html__( 'Control the position, style, and ordering of the course post listing inside single posts.', 'scc' ) . '</p>';
	}

	/**
	 * Section description — Post Meta.
	 */
	public function section_post_meta(): void {
		echo '<p>' . esc_html__( 'Show or hide author and date information beneath each post in the course listing.', 'scc' ) . '</p>';
	}

	/**
	 * Section description — Front Display.
	 */
	public function section_front_display(): void {
		echo '<p>' . esc_html__( 'On the blog home, archives, and search results, indicate that posts belong to a course.', 'scc' ) . '</p>';
	}


	// =============================================================================
	// Field callbacks — Course Container Display
	// =============================================================================

	/**
	 * Field — Container Position.
	 */
	public function field_display_position(): void {

		$options = $this->get_options();

		$choices = array(
			'above' => __( 'Above Content', 'scc' ),
			'below' => __( 'Below Content', 'scc' ),
			'both'  => __( 'Above & Below Content', 'scc' ),
			'hide'  => __( 'Hide', 'scc' ),
		);
		?>
		<select id="display_position" name="scc_display_settings[display_position]">
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $options['display_position'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Where to display the course container relative to post content.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Field — List Style.
	 */
	public function field_list_type(): void {

		$options = $this->get_options();

		$choices = array(
			'ordered'   => __( 'Numbered List', 'scc' ),
			'unordered' => __( 'Bullet Points', 'scc' ),
			'none'      => __( 'No List Indicator', 'scc' ),
		);
		?>
		<select id="list_type" name="scc_display_settings[list_type]">
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $options['list_type'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Choose your preferred list element style.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Field — Sort Posts By.
	 */
	public function field_orderby(): void {

		$options = $this->get_options();

		$choices = array(
			'date'          => __( 'Date', 'scc' ),
			'author'        => __( 'Author', 'scc' ),
			'title'         => __( 'Title', 'scc' ),
			'modified'      => __( 'Last Modified', 'scc' ),
			'rand'          => __( 'Random', 'scc' ),
			'comment_count' => __( 'Comment Count', 'scc' ),
		);
		?>
		<select id="scc_orderby" name="scc_display_settings[scc_orderby]">
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $options['scc_orderby'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'The parameter used to order posts in the course listing.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Field — Sort Order.
	 */
	public function field_order(): void {

		$options = $this->get_options();

		$choices = array(
			'asc'  => __( 'Ascending', 'scc' ),
			'desc' => __( 'Descending', 'scc' ),
		);
		?>
		<select id="scc_order" name="scc_display_settings[scc_order]">
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $options['scc_order'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Whether the post listing is sorted ascending or descending.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Field — Current Post Style.
	 */
	public function field_current_post(): void {

		$options = $this->get_options();

		$choices = array(
			'none'   => __( 'No Style', 'scc' ),
			'bold'   => __( 'Bold', 'scc' ),
			'italic' => __( 'Italic', 'scc' ),
			'strike' => __( 'Strikethrough', 'scc' ),
		);
		?>
		<select id="current_post" name="scc_display_settings[current_post]">
			<?php foreach ( $choices as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $options['current_post'], $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php esc_html_e( 'How the currently viewed post is styled in the course listing.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Field — Disable JavaScript.
	 */
	public function field_disable_js(): void {

		$options = $this->get_options();
		?>
		<input id="disable_js" type="checkbox" name="scc_display_settings[disable_js]" value="1" <?php checked( 1, $options['disable_js'] ); ?>>
		<label for="disable_js"><?php esc_html_e( 'Display the course listing without a toggle link.', 'scc' ); ?></label>
		<?php
	}


	// =============================================================================
	// Field callbacks — Post Meta
	// =============================================================================

	/**
	 * Field — Show Author.
	 */
	public function field_show_author(): void {

		$options = $this->get_options();
		?>
		<input id="show_author" type="checkbox" name="scc_display_settings[show_author]" value="1" <?php checked( 1, $options['show_author'] ); ?>>
		<label for="show_author"><?php esc_html_e( 'Show the post author beneath each item in the course listing.', 'scc' ); ?></label>
		<?php
	}


	/**
	 * Field — Show Date.
	 */
	public function field_show_date(): void {

		$options = $this->get_options();
		?>
		<input id="show_date" type="checkbox" name="scc_display_settings[show_date]" value="1" <?php checked( 1, $options['show_date'] ); ?>>
		<label for="show_date"><?php esc_html_e( 'Show the publish date beneath each item in the course listing.', 'scc' ); ?></label>
		<?php
	}


	// =============================================================================
	// Field callbacks — Front Display
	// =============================================================================

	/**
	 * Field — Enable Front Display.
	 */
	public function field_enable_front_display(): void {

		$options = $this->get_options();
		?>
		<input id="enable_front_display" type="checkbox" name="scc_display_settings[enable_front_display]" value="1" <?php checked( 1, $options['enable_front_display'] ); ?>>
		<label for="enable_front_display"><?php esc_html_e( 'Show a course label in post excerpts on the blog home, archives, and search results.', 'scc' ); ?></label>
		<?php
	}


	// =============================================================================
	// Sanitization
	// =============================================================================

	/**
	 * Sanitize and validate all settings on save.
	 *
	 * @param  array $input Raw input from the settings form.
	 * @return array        Sanitized settings.
	 */
	public function save_settings( $input ) {

		$clean = array();

		$allowed_positions   = array( 'above', 'below', 'both', 'hide' );
		$allowed_list_types  = array( 'ordered', 'unordered', 'none' );
		$allowed_orderby     = array( 'date', 'author', 'title', 'modified', 'rand', 'comment_count' );
		$allowed_orders      = array( 'asc', 'desc' );
		$allowed_post_styles = array( 'none', 'bold', 'italic', 'strike' );

		$clean['display_position'] = isset( $input['display_position'] ) && in_array( $input['display_position'], $allowed_positions, true )
			? $input['display_position']
			: 'above';

		$clean['list_type'] = isset( $input['list_type'] ) && in_array( $input['list_type'], $allowed_list_types, true )
			? $input['list_type']
			: 'ordered';

		$clean['scc_orderby'] = isset( $input['scc_orderby'] ) && in_array( $input['scc_orderby'], $allowed_orderby, true )
			? $input['scc_orderby']
			: 'date';

		$clean['scc_order'] = isset( $input['scc_order'] ) && in_array( $input['scc_order'], $allowed_orders, true )
			? $input['scc_order']
			: 'asc';

		$clean['current_post'] = isset( $input['current_post'] ) && in_array( $input['current_post'], $allowed_post_styles, true )
			? $input['current_post']
			: 'none';

		$clean['disable_js']           = ! empty( $input['disable_js'] ) ? '1' : '0';
		$clean['show_author']          = ! empty( $input['show_author'] ) ? '1' : '0';
		$clean['show_date']            = ! empty( $input['show_date'] ) ? '1' : '0';
		$clean['enable_front_display'] = ! empty( $input['enable_front_display'] ) ? '1' : '0';

		return $clean;
	}


	// =============================================================================
	// Settings page output
	// =============================================================================

	/**
	 * Render the settings page.
	 */
	public function settings_page(): void {

		?>
		<div class="wrap">
			<h1><?php echo esc_html( SCC_NAME . ' ' . __( 'Settings', 'scc' ) ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'scc_display_settings' );
				do_settings_sections( 'simple_course_creator' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}


	// =============================================================================
	// Helpers
	// =============================================================================

	/**
	 * Return the full settings array with defaults applied.
	 *
	 * @return array
	 */
	private function get_options(): array {

		$defaults = array(
			'display_position'    => 'above',
			'list_type'           => 'ordered',
			'scc_orderby'         => 'date',
			'scc_order'           => 'asc',
			'current_post'        => 'none',
			'disable_js'          => '0',
			'show_author'         => '1',
			'show_date'           => '1',
			'enable_front_display' => '1',
		);

		return wp_parse_args( get_option( 'scc_display_settings', array() ), $defaults );
	}


}

new SCC_Settings_Page();
