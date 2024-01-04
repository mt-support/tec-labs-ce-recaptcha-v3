<?php
/**
 * Settings Object.
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\CeRecaptchaV3
 */
namespace Tribe\Extensions\CeRecaptchaV3;

use Tribe__Settings_Manager;

/**
 * Do the Settings.
 */
class Settings {

	/**
	 * The Settings Helper class.
	 *
	 * @var Settings_Helper
	 */
	protected $settings_helper;

	/**
	 * The prefix for our settings keys.
	 *
	 * @see get_options_prefix() Use this method to get this property's value.
	 *
	 * @var string
	 */
	private $options_prefix = '';

	/**
	 * Settings constructor.
	 *
	 * @param string $options_prefix Recommended: the plugin text domain, with hyphens converted to underscores.
	 */
	public function __construct( $options_prefix ) {
		$this->settings_helper = new Settings_Helper();

		$this->set_options_prefix( $options_prefix );

		// Add settings specific to the extension
		add_action( 'admin_init', [ $this, 'add_settings' ] );
	}

	/**
	 * Allow access to set the Settings Helper property.
	 *
	 * @see get_settings_helper()
	 *
	 * @param Settings_Helper $helper
	 *
	 * @return Settings_Helper
	 */
	public function set_settings_helper( Settings_Helper $helper ) {
		$this->settings_helper = $helper;

		return $this->get_settings_helper();
	}

	/**
	 * Allow access to get the Settings Helper property.
	 *
	 * @see set_settings_helper()
	 */
	public function get_settings_helper() {
		return $this->settings_helper;
	}

	/**
	 * Set the options prefix to be used for this extension's settings.
	 *
	 * Recommended: the plugin text domain, with hyphens converted to underscores.
	 * Is forced to end with a single underscore. All double-underscores are converted to single.
	 *
	 * @see get_options_prefix()
	 *
	 * @param string $options_prefix
	 */
	private function set_options_prefix( $options_prefix = '' ) {
		if ( empty( $opts_prefix ) ) {
			$opts_prefix = str_replace( '-', '_', 'tec-labs-ce-recaptcha-v3' ); // The text domain.
		}

		$opts_prefix = $opts_prefix . '_';

		$this->options_prefix = str_replace( '__', '_', $opts_prefix );
	}

	/**
	 * Get this extension's options prefix.
	 *
	 * @see set_options_prefix()
	 *
	 * @return string
	 */
	public function get_options_prefix() {
		return $this->options_prefix;
	}

	/**
	 * Given an option key, get this extension's option value.
	 *
	 * This automatically prepends this extension's option prefix so you can just do `$this->get_option( 'a_setting' )`.
	 *
	 * @see tribe_get_option()
	 *
	 * @param string $key
	 * @param string $default
	 *
	 * @return mixed
	 */
	public function get_option( $key = '', $default = '' ) {
		$key = $this->sanitize_option_key( $key );

		return tribe_get_option( $key, $default );
	}

	/**
	 * Get an option key after ensuring it is appropriately prefixed.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function sanitize_option_key( $key = '' ) {
		$prefix = $this->get_options_prefix();

		if ( 0 === strpos( $key, $prefix ) ) {
			$prefix = '';
		}

		return $prefix . $key;
	}

	/**
	 * Get an array of all of this extension's options without array keys having the redundant prefix.
	 *
	 * @return array
	 */
	public function get_all_options() {
		$raw_options = $this->get_all_raw_options();

		$result = [];

		$prefix = $this->get_options_prefix();

		foreach ( $raw_options as $key => $value ) {
			$abbr_key            = str_replace( $prefix, '', $key );
			$result[ $abbr_key ] = $value;
		}

		return $result;
	}

	/**
	 * Get an array of all of this extension's raw options (i.e. the ones starting with its prefix).
	 *
	 * @return array
	 */
	public function get_all_raw_options() {
		$tribe_options = Tribe__Settings_Manager::get_options();

		if ( ! is_array( $tribe_options ) ) {
			return [];
		}

		$result = [];

		foreach ( $tribe_options as $key => $value ) {
			if ( 0 === strpos( $key, $this->get_options_prefix() ) ) {
				$result[ $key ] = $value;
			}
		}

		return $result;
	}

	/**
	 * Given an option key, delete this extension's option value.
	 *
	 * This automatically prepends this extension's option prefix so you can just do `$this->delete_option( 'a_setting' )`.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function delete_option( $key = '' ) {
		$key = $this->sanitize_option_key( $key );

		$options = Tribe__Settings_Manager::get_options();

		unset( $options[ $key ] );

		return Tribe__Settings_Manager::set_options( $options );
	}

	/**
	 * Adds a new section of fields to Events > Settings > General tab, appearing after the "Map Settings" section
	 * and before the "Miscellaneous Settings" section.
	 */
	public function add_settings() {
		$fields = [
			'Intro'   => [
				'type' => 'html',
				'html' => $this->recaptcha_v3_settings_intro_text(),
			],
			'site_key' => [ // TODO: Change setting.
				'type'            => 'text',
				'label'           => esc_html__( 'Site Key', 'tec-labs-ce-recaptcha-v3' ),
				'tooltip'         => sprintf( esc_html__( 'Get your Site Key at %s.', 'tec-labs-ce-recaptcha-v3' ), '<a href="https://www.Google.com/recaptcha/admin/create" target="_blank">https://www.google.com/recaptcha/admin/create</a>' ),
				'validation_type' => 'html',
				'default'         => '',
				'can_be_empty'    => true,
				'parent_option'   => \Tribe__Events__Community__Main::OPTIONNAME,  // We're using the Community Events entry, instead of the default TEC.
				'size'            => 'large',
			],
			'theme' => [
				'type'            => 'dropdown',
				'label'           => esc_html__( 'Theme', 'tec-labs-ce-recaptcha-v3' ),
				'tooltip'         => esc_html__( 'The color theme of the widget.', 'tec-labs-ce-recaptcha-v3' ),
				'default'         => 'light',
				'validation_type' => 'options',
				'size'            => 'small',
				'parent_option'   => \Tribe__Events__Community__Main::OPTIONNAME,  // We're using the Community Events entry, instead of the default TEC.
				'options'         => [
					'light' => esc_html__( 'Light', 'tec-labs-ce-recaptcha-v3' ),
					'dark'  => esc_html__( 'Dark', 'tec-labs-ce-recaptcha-v3' ),
				],
			],

		];

		$this->settings_helper->add_fields(
			$this->prefix_settings_field_keys( $fields ),
			'addons',
			'google_maps_js_api_key',
			false
		);
	}

	/**
	 * Add the options prefix to each of the array keys.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private function prefix_settings_field_keys( array $fields ) {
		$prefixed_fields = array_combine(
			array_map(
				function ( $key ) {
					return $this->get_options_prefix() . $key;
				}, array_keys( $fields )
			),
			$fields
		);

		return (array) $prefixed_fields;
	}

	/**
	 * HTML for the Settings Header.
	 *
	 * @return string
	 */
	private function recaptcha_v3_settings_intro_text() {
		$result = '<h3>' . esc_html_x( 'reCAPTCHA v3 API Key', 'Settings header', 'tec-labs-ce-recaptcha-v3' ) . '</h3>';
		$result .= '<div style="margin-left: 20px;">';
		$result .= '<p>';
		$result .= esc_html_x( 'Provide reCAPTCHA v3 API key to enable reCAPTCHA on your Community Events form.', 'Setting section description', 'tec-labs-ce-recaptcha-v3' );
		$result .= '</p>';
		$result .= '</div>';

		return $result;
	}

}
