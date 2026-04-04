<?php
/**
 * SCC_Customizer class
 *
 * Registers all Simple Course Creator Customizer controls under a single
 * "Simple Course Creator Design" section, covering the course container,
 * post meta output, and front display indicator.
 *
 * All settings use type 'option' with array notation (scc_customizer[key])
 * so values are stored in a single scc_customizer option and persist
 * across theme switches.
 *
 * All generated CSS is output in a single <style> block via wp_head.
 *
 * The scc_add_to_styles action fires inside that block so third-party
 * code can inject additional CSS without opening a new <style> tag.
 *
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class SCC_Customizer {


	/**
	 * Constructor — register hooks.
	 */
	public function __construct() {
		add_action( 'customize_register',              array( $this, 'settings' ) );
		add_action( 'customize_controls_print_styles', array( $this, 'customizer_styles' ) );
		add_action( 'wp_head',                         array( $this, 'head_styles' ) );
	}


	/**
	 * Register the SCC Customizer section, settings, and controls.
	 *
	 * All settings use type 'option' with array notation (scc_customizer[key])
	 * so all values land in a single DB row independent of the active theme.
	 *
	 * Settings are grouped by component with priority spacing:
	 *   1–20   Course container
	 *   100    Post meta
	 *   200+   Front display
	 *
	 * @param WP_Customize_Manager $wp_customize
	 */
	public function settings( $wp_customize ) {

		$wp_customize->add_section( 'scc_customizer', array(
			'title'       => __( 'Simple Course Creator Design', 'scc' ),
			'description' => __( 'Customize the appearance of SCC course listings and indicators. Untouched options inherit your theme\'s default styles. For complete control, write custom CSS targeting the relevant classes.', 'scc' ),
			'priority'    => 100,
		) );

		// -------------------------------------------------------------------------
		// Course container and front display — integer settings
		// -------------------------------------------------------------------------

		$integer_settings = array(
			array(
				'slug'     => 'scc_customizer[border_px]',
				'label'    => __( 'Course Box: Border Width', 'scc' ),
				'priority' => 1,
			),
			array(
				'slug'     => 'scc_customizer[border_radius]',
				'label'    => __( 'Course Box: Border Radius', 'scc' ),
				'priority' => 2,
			),
			array(
				'slug'     => 'scc_customizer[padding_px]',
				'label'    => __( 'Course Box: Padding', 'scc' ),
				'priority' => 4,
			),
			array(
				'slug'     => 'scc_customizer[fd_font_size]',
				'label'    => __( 'Front Display: Font Size', 'scc' ),
				'priority' => 201,
			),
			array(
				'slug'     => 'scc_customizer[fd_padding_top_bottom]',
				'label'    => __( 'Front Display: Padding (Top / Bottom)', 'scc' ),
				'priority' => 205,
			),
			array(
				'slug'     => 'scc_customizer[fd_padding_left_right]',
				'label'    => __( 'Front Display: Padding (Left / Right)', 'scc' ),
				'priority' => 206,
			),
			array(
				'slug'     => 'scc_customizer[fd_border]',
				'label'    => __( 'Front Display: Border Width', 'scc' ),
				'priority' => 207,
			),
			array(
				'slug'     => 'scc_customizer[fd_border_radius]',
				'label'    => __( 'Front Display: Border Radius', 'scc' ),
				'priority' => 209,
			),
			array(
				'slug'     => 'scc_customizer[fd_margin_bottom]',
				'label'    => __( 'Front Display: Bottom Margin', 'scc' ),
				'priority' => 210,
			),
		);

		foreach ( $integer_settings as $setting ) {
			$wp_customize->add_setting( $setting['slug'], array(
				'type'              => 'option',
				'default'           => '',
				'sanitize_callback' => array( $this, 'sanitize_integer' ),
			) );
			$wp_customize->add_control( $setting['slug'], array(
				'label'    => $setting['label'],
				'section'  => 'scc_customizer',
				'settings' => $setting['slug'],
				'priority' => $setting['priority'],
			) );
		}

		// -------------------------------------------------------------------------
		// Front display — bold checkbox
		// -------------------------------------------------------------------------

		$wp_customize->add_setting( 'scc_customizer[fd_font_weight]', array(
			'type'              => 'option',
			'default'           => 0,
			'sanitize_callback' => array( $this, 'sanitize_checkbox' ),
		) );
		$wp_customize->add_control( 'scc_customizer[fd_font_weight]', array(
			'label'    => __( 'Front Display: Bold Font', 'scc' ),
			'section'  => 'scc_customizer',
			'type'     => 'checkbox',
			'priority' => 202,
		) );

		// -------------------------------------------------------------------------
		// Color settings
		// -------------------------------------------------------------------------

		$color_settings = array(
			array(
				'slug'     => 'scc_customizer[border_color]',
				'label'    => __( 'Course Box: Border Color', 'scc' ),
				'priority' => 3,
			),
			array(
				'slug'     => 'scc_customizer[background]',
				'label'    => __( 'Course Box: Background Color', 'scc' ),
				'priority' => 5,
			),
			array(
				'slug'     => 'scc_customizer[text_color]',
				'label'    => __( 'Course Box: Text Color', 'scc' ),
				'priority' => 6,
			),
			array(
				'slug'     => 'scc_customizer[link_color]',
				'label'    => __( 'Course Box: Link Color', 'scc' ),
				'priority' => 7,
			),
			array(
				'slug'     => 'scc_customizer[link_hover_color]',
				'label'    => __( 'Course Box: Link Hover Color', 'scc' ),
				'priority' => 8,
			),
			array(
				'slug'     => 'scc_customizer[pm_text_color]',
				'label'    => __( 'Post Meta: Text Color', 'scc' ),
				'priority' => 100,
			),
			array(
				'slug'     => 'scc_customizer[fd_text_color]',
				'label'    => __( 'Front Display: Text Color', 'scc' ),
				'priority' => 203,
			),
			array(
				'slug'     => 'scc_customizer[fd_background]',
				'label'    => __( 'Front Display: Background Color', 'scc' ),
				'priority' => 204,
			),
			array(
				'slug'     => 'scc_customizer[fd_border_color]',
				'label'    => __( 'Front Display: Border Color', 'scc' ),
				'priority' => 208,
			),
		);

		foreach ( $color_settings as $color ) {
			$wp_customize->add_setting( $color['slug'], array(
				'type'              => 'option',
				'capability'        => 'edit_theme_options',
				'sanitize_callback' => 'sanitize_hex_color',
			) );
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $color['slug'], array(
				'label'    => $color['label'],
				'section'  => 'scc_customizer',
				'settings' => $color['slug'],
				'priority' => $color['priority'],
			) ) );
		}
	}


	/**
	 * Sanitize an integer Customizer input.
	 *
	 * Returns an empty string for blank inputs (no value set),
	 * otherwise returns the absolute integer value.
	 *
	 * @param  string|int $input
	 * @return string|int
	 */
	public function sanitize_integer( $input ) {

		if ( '' === $input ) {
			return '';
		}

		return absint( $input );
	}


	/**
	 * Sanitize a checkbox Customizer input.
	 *
	 * @param  mixed $input
	 * @return int  1 if checked, 0 otherwise.
	 */
	public function sanitize_checkbox( $input ) {
		return 1 === (int) $input ? 1 : 0;
	}


	/**
	 * Output admin-only inline CSS to narrow the px input fields in the
	 * Customizer panel and append a "- px" label to each.
	 *
	 * Control DOM IDs are derived from the setting ID with [ replaced by -
	 * and ] stripped, per WordPress Customizer conventions.
	 */
	public function customizer_styles() {
		?>
		<style type="text/css">
			#customize-control-scc_customizer-border_px input[type="text"],
			#customize-control-scc_customizer-border_radius input[type="text"],
			#customize-control-scc_customizer-padding_px input[type="text"],
			#customize-control-scc_customizer-fd_font_size input[type="text"],
			#customize-control-scc_customizer-fd_padding_top_bottom input[type="text"],
			#customize-control-scc_customizer-fd_padding_left_right input[type="text"],
			#customize-control-scc_customizer-fd_border input[type="text"],
			#customize-control-scc_customizer-fd_border_radius input[type="text"],
			#customize-control-scc_customizer-fd_margin_bottom input[type="text"] { width: 50px; }

			#customize-control-scc_customizer-border_px label:after,
			#customize-control-scc_customizer-border_radius label:after,
			#customize-control-scc_customizer-padding_px label:after,
			#customize-control-scc_customizer-fd_font_size label:after,
			#customize-control-scc_customizer-fd_padding_top_bottom label:after,
			#customize-control-scc_customizer-fd_padding_left_right label:after,
			#customize-control-scc_customizer-fd_border label:after,
			#customize-control-scc_customizer-fd_border_radius label:after,
			#customize-control-scc_customizer-fd_margin_bottom label:after { content: " - px"; }

			#customize-control-scc_customizer-fd_font_weight { display: inline-block; margin-top: 20px; }
		</style>
		<?php
	}


	/**
	 * Output all SCC-generated CSS in a single <style> block.
	 *
	 * Reads all values from the scc_customizer option array in a single
	 * get_option() call. Fires scc_add_to_styles inside the block so
	 * third-party code can append additional CSS without opening another
	 * <style> tag.
	 */
	public function head_styles() {

		$c = get_option( 'scc_customizer', array() );

		// Course container values.
		$border_px        = $c['border_px'] ?? '';
		$border_radius    = $c['border_radius'] ?? '';
		$border_color     = sanitize_hex_color( $c['border_color'] ?? '' );
		$padding_px       = $c['padding_px'] ?? '';
		$bg_color         = sanitize_hex_color( $c['background'] ?? '' );
		$text_color       = sanitize_hex_color( $c['text_color'] ?? '' );
		$link_color       = sanitize_hex_color( $c['link_color'] ?? '' );
		$link_hover_color = sanitize_hex_color( $c['link_hover_color'] ?? '' );

		// Post meta values.
		$pm_text_color = sanitize_hex_color( $c['pm_text_color'] ?? '' );

		// Front display values.
		$fd_font_size          = $c['fd_font_size'] ?? '';
		$fd_font_weight        = $c['fd_font_weight'] ?? 0;
		$fd_text_color         = sanitize_hex_color( $c['fd_text_color'] ?? '' );
		$fd_bg_color           = sanitize_hex_color( $c['fd_background'] ?? '' );
		$fd_border             = $c['fd_border'] ?? '';
		$fd_border_color       = sanitize_hex_color( $c['fd_border_color'] ?? '' );
		$fd_border_radius      = $c['fd_border_radius'] ?? '';
		$fd_padding_top_bottom = $c['fd_padding_top_bottom'] ?? '';
		$fd_padding_left_right = $c['fd_padding_left_right'] ?? '';
		$fd_margin_bottom      = $c['fd_margin_bottom'] ?? '';

		// Only output a style block if there is something to write.
		$has_course_box_styles = $border_px !== '' || $border_radius !== '' || $border_color || $padding_px !== '' || $bg_color || $text_color || $link_color || $link_hover_color;
		$has_pm_styles         = (bool) $pm_text_color;
		$has_fd_styles         = $fd_font_size !== '' || $fd_font_weight || $fd_text_color || $fd_bg_color || $fd_border !== '' || $fd_border_radius !== '' || $fd_padding_top_bottom !== '' || $fd_padding_left_right !== '' || $fd_margin_bottom !== '';
		$has_third_party       = has_action( 'scc_add_to_styles' );

		if ( ! $has_course_box_styles && ! $has_pm_styles && ! $has_fd_styles && ! $has_third_party ) {
			return;
		}

		echo '<style type="text/css">' . "\n";

		// ----- #scc-wrap -----
		if ( $has_course_box_styles ) {
			echo '#scc-wrap{';

			// Border width & style.
			if ( '0' === (string) $border_px ) {
				echo 'border:none;';
			} elseif ( $border_px !== '' ) {
				echo 'border-width:' . intval( $border_px ) . 'px;border-style:solid;';
			}

			// Border radius (independent of border).
			if ( $border_radius !== '' ) {
				echo 'border-radius:' . intval( $border_radius ) . 'px;';
			}

			// Border color.
			if ( $border_color ) {
				echo 'border-color:' . $border_color . ';';
			}

			// Padding.
			if ( '0' === (string) $padding_px ) {
				echo 'padding:0;';
			} elseif ( $padding_px !== '' ) {
				echo 'padding:' . intval( $padding_px ) . 'px;';
			}

			// Background.
			if ( $bg_color ) {
				echo 'background:' . $bg_color . ';';
			}

			// Text color.
			if ( $text_color ) {
				echo 'color:' . $text_color . ';';
			}

			echo '}' . "\n";

			// Adjust toggle link position when the box has visual presence.
			if ( ( $padding_px !== '' && '0' !== (string) $padding_px ) || ( $border_px !== '' && '0' !== (string) $border_px ) || $bg_color ) {
				echo '#scc-wrap .scc-toggle-post-list{right:10px}' . "\n";
			}

			// Link colors.
			if ( $link_color ) {
				echo '#scc-wrap a{color:' . $link_color . '}' . "\n";
			}

			if ( $link_hover_color ) {
				echo '#scc-wrap a:hover{color:' . $link_hover_color . '}' . "\n";
			}
		}

		// ----- Post meta -----
		if ( $has_pm_styles ) {
			echo '#scc-wrap .scc-post-meta{';
			echo 'color:' . $pm_text_color . ';';
			echo '}' . "\n";
		}

		// ----- Front display -----
		if ( $has_fd_styles ) {
			echo '.scc-front-display{';

			if ( $fd_font_size !== '' ) {
				echo 'font-size:' . intval( $fd_font_size ) . 'px;';
			}

			if ( 1 === (int) $fd_font_weight ) {
				echo 'font-weight:bold;';
			}

			if ( $fd_bg_color ) {
				echo 'background:' . $fd_bg_color . ';';
			}

			if ( $fd_border !== '' ) {
				echo 'border:' . intval( $fd_border ) . 'px solid ' . ( $fd_border_color ?: 'currentColor' ) . ';';
			}

			if ( $fd_border_radius !== '' ) {
				echo 'border-radius:' . intval( $fd_border_radius ) . 'px;';
			}

			if ( $fd_padding_top_bottom !== '' ) {
				echo 'padding-top:' . intval( $fd_padding_top_bottom ) . 'px;';
				echo 'padding-bottom:' . intval( $fd_padding_top_bottom ) . 'px;';
			}

			if ( $fd_padding_left_right !== '' ) {
				echo 'padding-right:' . intval( $fd_padding_left_right ) . 'px;';
				echo 'padding-left:' . intval( $fd_padding_left_right ) . 'px;';
			}

			if ( $fd_text_color ) {
				echo 'color:' . $fd_text_color . ';';
			}

			if ( $fd_margin_bottom !== '' ) {
				echo 'margin-bottom:' . intval( $fd_margin_bottom ) . 'px;';
			}

			echo '}' . "\n";
		}

		// Third-party styles hook.
		do_action( 'scc_add_to_styles' );

		echo '</style>' . "\n";
	}
}

new SCC_Customizer();
