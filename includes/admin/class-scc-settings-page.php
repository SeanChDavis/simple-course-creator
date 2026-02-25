<?php
/**
 * Settings page class
 *
 * This class creates the settings menu item as well as the settings
 * page. The menu item is added to WP Dashboard -> Settings -> Course
 * Settings.
 *
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // no accessing this file directly


class SCC_Settings_Page {


	/**
	 * Constructor for SCC_Settings_Page class
	 */
	public function __construct() {

		// load settings page
		add_action( 'admin_menu', array( $this, 'settings_menu' ) );

		// register settings
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}


	/**
	 * Add a new settings page under the Settings menu
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
	 * Register display settings
	 */
	public function register_settings(): void {

		// register display settings
		register_setting(
				'course_display_settings',
				'course_display_settings',
				array( $this, 'save_settings' )
		);

		// add display settings section
		add_settings_section(
				'course_display_settings',
				__( 'Course Container Display Settings', 'scc' ),
				array( $this, 'course_display_settings' ),
				'simple_course_creator'
		);

		// add option for choosing the display position of the course
		add_settings_field(
				'display_position',
				__( 'Course Container Position', 'scc' ),
				array( $this, 'course_list_position' ),
				'simple_course_creator',
				'course_display_settings',
				array( 'label_for' => 'display_position' )
		);

		// add option for choosing the list style (ul, ol, none)
		add_settings_field(
				'list_type',
				__( 'HTML List Style', 'scc' ),
				array( $this, 'course_list_type' ),
				'simple_course_creator',
				'course_display_settings',
				array( 'label_for' => 'list_type' )
		);

		// add option for ordering posts
		add_settings_field(
				'scc_orderby',
				__( 'Sort Posts By', 'scc' ),
				array( $this, 'course_orderby' ),
				'simple_course_creator',
				'course_display_settings',
				array( 'label_for' => 'scc_orderby' )
		);

		// add option for ordering posts ASC or DESC
		add_settings_field(
				'scc_order',
				__( 'Order Posts', 'scc' ),
				array( $this, 'course_order' ),
				'simple_course_creator',
				'course_display_settings',
				array( 'label_for' => 'scc_order' )
		);

		// add option for choosing current post text/font properties
		add_settings_field(
				'current_post',
				__( 'Current Post Style', 'scc' ),
				array( $this, 'course_current_post' ),
				'simple_course_creator',
				'course_display_settings',
				array( 'label_for' => 'current_post' )
		);

		// add option for disabling JS
		add_settings_field(
				'disable_js',
				__( 'Disable JavaScript', 'scc' ),
				array( $this, 'course_disable_js' ),
				'simple_course_creator',
				'course_display_settings',
				array( 'label_for' => 'disable_js' )
		);
	}


	/**
	 * Section - Course Container Display Settings
	 *
	 * @callback_for 'course_display_settings' section
	 */
	public function course_display_settings() {
		echo '<p>' . __( 'These settings control the front-end display of the post listing container inside of posts that are part of courses.',
						'scc' ) . '</p>';
	}


	/**
	 * Option - Course Container Position
	 *
	 * @callback_for 'display_position' field
	 */
	public function course_list_position() {

		// set default option value
		$default = array( 'display_position' => 'above' );
		$options = get_option( 'course_display_settings', $default );
		$options = wp_parse_args( $options, $default );

		// possible list options
		$course_container = array(
				'above' => array( 'value' => 'above', 'desc' => __( 'Above Content', 'scc' ) ),
				'below' => array( 'value' => 'below', 'desc' => __( 'Below Content', 'scc' ) ),
				'both'  => array( 'value' => 'both', 'desc' => __( 'Above & Below Content', 'scc' ) ),
				'hide'  => array( 'value' => 'hide', 'desc' => __( 'Hide Course Container', 'scc' ) ),
		);
		?>
		<select id="display_position" name="course_display_settings[display_position]">
			<?php foreach ( $course_container as $c ) { // display options from $course_container array ?>
				<option value="<?php echo $c['value']; ?>" <?php selected( $options['display_position'],
						$c['value'] ); ?>><?php echo $c['desc']; ?></option>
			<?php } ?>
		</select>
		<p class="description"><?php _e( 'Choose where to display your course container.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Option - HTML List Style
	 *
	 * @callback_for 'list_type' field
	 */
	public function course_list_type() {

		// set default option value
		$default = array( 'list_type' => 'ordered' );
		$options = get_option( 'course_display_settings', $default );
		$options = wp_parse_args( $options, $default );

		// possible list options
		$list_type = array(
				'ordered'   => array( 'value' => 'ordered', 'desc' => __( 'Numbered List', 'scc' ) ),
				'unordered' => array( 'value' => 'unordered', 'desc' => __( 'Bullet Points', 'scc' ) ),
				'none'      => array( 'value' => 'none', 'desc' => __( 'No List Indicator', 'scc' ) ),
		);
		?>
		<select id="list_type" name="course_display_settings[list_type]">
			<?php foreach ( $list_type as $l ) { // display options from $list_type array ?>
				<option value="<?php echo $l['value']; ?>" <?php selected( $options['list_type'],
						$l['value'] ); ?>><?php echo $l['desc']; ?></option>
			<?php } ?>
		</select>
		<p class="description"><?php _e( 'Choose your preferred list element style.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Option - Sort Posts By
	 *
	 * @callback_for 'scc_orderby' field
	 */
	public function course_orderby() {

		// set default option value
		$default = array( 'scc_orderby' => 'date' );
		$options = get_option( 'course_display_settings', $default );
		$options = wp_parse_args( $options, $default );

		// possible list options
		$orderby = array(
				'date'          => array( 'value' => 'date', 'desc' => __( 'Date', 'scc' ) ),
				'author'        => array( 'value' => 'author', 'desc' => __( 'Author', 'scc' ) ),
				'title'         => array( 'value' => 'title', 'desc' => __( 'Title', 'scc' ) ),
				'modified'      => array( 'value' => 'modified', 'desc' => __( 'Last Modified', 'scc' ) ),
				'random'        => array( 'value' => 'random', 'desc' => __( 'Random', 'scc' ) ),
				'comment_count' => array( 'value' => 'comment_count', 'desc' => __( 'Comment Count', 'scc' ) ),
		);
		?>
		<select id="scc_orderby" name="course_display_settings[scc_orderby]">
			<?php foreach ( $orderby as $o ) { // display options from $orderby array ?>
				<option value="<?php echo $o['value']; ?>" <?php selected( $options['scc_orderby'],
						$o['value'] ); ?>><?php echo $o['desc']; ?></option>
			<?php } ?>
		</select>
		<p class="description"><?php _e( 'Choose a parameter for ordering your post listing.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Option - Order Posts
	 *
	 * @callback_for 'scc_order' field
	 */
	public function course_order() {

		// set default option value
		$default = array( 'scc_order' => 'ASC' );
		$options = get_option( 'course_display_settings', $default );
		$options = wp_parse_args( $options, $default );

		// possible list options
		$order = array(
				'asc'  => array( 'value' => 'asc', 'desc' => __( 'Ascending', 'scc' ) ),
				'desc' => array( 'value' => 'desc', 'desc' => __( 'Descending', 'scc' ) ),
		);
		?>
		<select id="scc_order" name="course_display_settings[scc_order]">
			<?php foreach ( $order as $o ) { // display options from $order array ?>
				<option value="<?php echo $o['value']; ?>" <?php selected( $options['scc_order'],
						$o['value'] ); ?>><?php echo $o['desc']; ?></option>
			<?php } ?>
		</select>
		<p class="description"><?php _e( 'Choose whether your list should be in ascending or descending order.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Option - Current Post Style
	 *
	 * @callback_for 'current_post' field
	 * @since 1.0.3
	 */
	public function course_current_post() {

		// set default option value
		$default = array( 'current_post' => 'none' );
		$options = get_option( 'course_display_settings', $default );
		$options = wp_parse_args( $options, $default );

		// possible list options
		$current_post = array(
				'none'   => array( 'value' => 'none', 'desc' => __( 'No Style', 'scc' ) ),
				'bold'   => array( 'value' => 'bold', 'desc' => __( 'Bold', 'scc' ) ),
				'strike' => array( 'value' => 'strike', 'desc' => __( 'Strike', 'scc' ) ),
				'italic' => array( 'value' => 'italic', 'desc' => __( 'Italic', 'scc' ) )
		);
		?>
		<select id="list_type" name="course_display_settings[current_post]">
			<?php foreach ( $current_post as $cp ) { // display options from $current_post array ?>
				<option value="<?php echo $cp['value']; ?>" <?php selected( $options['current_post'],
						$cp['value'] ); ?>><?php echo $cp['desc']; ?></option>
			<?php } ?>
		</select>
		<p class="description"><?php _e( 'Choose your preferred current post text/font style.', 'scc' ); ?></p>
		<?php
	}


	/**
	 * Option - Disable JavaScript
	 *
	 * @callback_for 'disable_js' field
	 * @since 1.0.1
	 */
	public function course_disable_js() {

		// set default option value
		$default = array( 'disable_js' => 0 );
		$options = get_option( 'course_display_settings', $default );
		$options = wp_parse_args( $options, $default );
		?>
		<input id="disable_js" type="checkbox" name="course_display_settings[disable_js]" value="1" <?php echo checked( 1,
				isset( $options['disable_js'] ) ? $options['disable_js'] : 0, false ); ?>>
		<p class="description"><?php _e( 'The course list will display without a toggle link.',
					'scc' ); ?></p>
		<?php
	}


	/**
	 * Save display settings
	 *
	 * @used_by course_list_position(), course_list_type(), & course_disable_js()
	 */
	public function save_settings( $input ) {

		// validate the display position option
		if ( ! isset( $input['display_position'] ) ) {
			$input['display_position'] = 'above';
		} else {
			update_option( 'display_position', $input['display_position'] );
		}

		// validate the list style option
		if ( ! isset( $input['list_type'] ) ) {
			$input['list_type'] = 'ordered';
		} else {
			update_option( 'list_type', $input['list_type'] );
		}

		// validate the orderby option
		if ( ! isset( $input['scc_orderby'] ) ) {
			$input['scc_orderby'] = 'date';
		} else {
			update_option( 'scc_orderby', $input['scc_orderby'] );
		}

		// validate the order option
		if ( ! isset( $input['scc_order'] ) ) {
			$input['scc_order'] = 'ASC';
		} else {
			update_option( 'scc_order', $input['scc_order'] );
		}

		// validate the current post style option
		if ( ! isset( $input['current_post'] ) ) {
			$input['current_post'] = 'none';
		} else {
			update_option( 'current_post', $input['current_post'] );
		}

		// validate the disable JS option
		$input['disable_js'] = ( isset( $input['disable_js'] ) && $input['disable_js'] == true ? '1' : '0' );

		return $input;
	}


	/**
	 * Plugin settings page
	 *
	 * SCC has a single menu link as a submenu under the "Settings" section
	 * of the WordPress dashboard. Within that page are tabbed settings pages.
	 * Based on which tab is selected, different settings or information will
	 * be shown.
	 */
	public function settings_page(): void {
		?>
		<div class="wrap">
			<h2><?php echo SCC_NAME . __( ' Settings', 'scc' ); ?></h2>
			<?php $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'simple_course_creator'; ?>
			<h2 class="nav-tab-wrapper">
				<a href="?page=simple_course_creator&tab=simple_course_creator" class="nav-tab <?php echo $active_tab == 'simple_course_creator' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Display',
							'scc' ); ?></a>
				<a href="?page=simple_course_creator&tab=simple_course_creator_info" class="nav-tab <?php echo $active_tab == 'simple_course_creator_info' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Information',
							'scc' ); ?></a>
			</h2>
			<form method="post" action="options.php">
				<?php
				if ( $active_tab == 'simple_course_creator' ) {
					settings_fields( 'course_display_settings' );
					do_settings_sections( 'simple_course_creator' );
					submit_button();
				} elseif ( $active_tab == 'simple_course_creator_info' ) {
					?>
					<h3><?php echo SCC_NAME . __( ' Information', 'scc' ); ?></h3>
					<table class="form-table plugin-info">
						<tbody>
							<tr>
								<th scope="row"><?php _e( 'Plugin Resources', 'scc' ); ?></th>
								<td>
									<p class="resources">
										<?php
										printf( __( 'New courses are created under the Posts menu. Once a course is created, posts can be assigned to a course through the manage posts and edit post screens. To add content to the post listing output, see the hooks & filters documentation %1$s. To completely override the output, CSS, and JS files, see the override plugin files documentation %2$s.',
												'scc' ),
												'<a href="https://github.com/seanchdavis/simple-course-creator#wordpress-hooks--filters" target="_blank">[?]</a>',
												'<a href="https://github.com/seanchdavis/simple-course-creator#active-theme-file-overrides" target="_blank">[?]</a>'
										);
										?>
									</p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Plugin Contributors', 'scc' ); ?></th>
								<td>
									<?php echo $this->scc_contributors(); ?>
									<p>
										<?php
										printf( __( 'Fork %s on GitHub and submit a pull request if you would like to pitch in.',
												'scc' ),
												'<a href="https://github.com/seanchdavis/simple-course-creator" target="_blank">' . SCC_NAME . '</a>'
										);
										?>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
				<?php } ?>
			</form>
		</div>
		<?php
	}


	/**
	 * The contributors
	 */
	public function scc_contributors(): string {
		$contributors     = $this->scc_get_contributors();
		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf( '<a href="%s" title="%s">',
					esc_url( 'https://github.com/' . $contributor->login ),
					esc_html( sprintf( __( 'View %s', 'scc' ), $contributor->login ) )
			);
			$contributor_list .= sprintf( '<img src="%s" width="30" height="30" class="gravatar" alt="%s" />',
					esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= sprintf( '<a class="web" href="%s">%s</a>',
					esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= '</li>';
		}
		$contributor_list .= '</ul>';

		return $contributor_list;
	}


	/**
	 * Get the repo contributors
	 */
	public function scc_get_contributors() {
		$transient_key = 'scc_contributors';
		$contributors  = get_transient( $transient_key );
		if ( false !== $contributors ) {
			return $contributors;
		}

		$response = wp_remote_get( 'https://api.github.com/repos/seanchdavis/simple-course-creator/contributors' );
		if ( is_wp_error( $response ) ) {
			return array();
		}

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );
		if ( ! is_array( $contributors ) ) {
			return array();
		}

		set_transient( $transient_key, $contributors, 3600 );

		return (array) $contributors;
	}
}

new SCC_Settings_Page();