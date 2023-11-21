<?php
/**
 * Plugin Class.
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\CeRecaptchaV3
 */

namespace Tribe\Extensions\CeRecaptchaV3;

use TEC\Common\Contracts\Service_Provider;
/**
 * Class Plugin
 *
 * @since 1.0.0
 *
 * @package Tribe\Extensions\CeRecaptchaV3
 */
class Plugin extends Service_Provider {
	/**
	 * Stores the version for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '1.0.0';

	/**
	 * Stores the base slug for the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const SLUG = 'ce-recaptcha-v3';

	/**
	 * Stores the base slug for the extension.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const FILE = TRIBE_EXTENSION_CE_RECAPTCHA_V3_FILE;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin Directory.
	 */
	public $plugin_dir;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin path.
	 */
	public $plugin_path;

	/**
	 * @since 1.0.0
	 *
	 * @var string Plugin URL.
	 */
	public $plugin_url;

	/**
	 * @since 1.0.0
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Set up the Extension's properties.
	 *
	 * This always executes even if the required plugins are not present.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		// Set up the plugin provider properties.
		$this->plugin_path = trailingslashit( dirname( static::FILE ) );
		$this->plugin_dir  = trailingslashit( basename( $this->plugin_path ) );
		$this->plugin_url  = plugins_url( $this->plugin_dir, $this->plugin_path );

		// Register this provider as the main one and use a bunch of aliases.
		$this->container->singleton( static::class, $this );
		$this->container->singleton( 'extension.ce_recaptcha_v3', $this );
		$this->container->singleton( 'extension.ce_recaptcha_v3.plugin', $this );
		$this->container->register( PUE::class );

		if ( ! $this->check_plugin_dependencies() ) {
			// If the plugin dependency manifest is not met, then bail and stop here.
			return;
		}

		// Do the settings.
		$this->get_settings();

		// Start binds.

		// Remove reCAPTCHA v2
		add_filter('tribe_community_events_captcha_plugin', '__return_null' );

		$this->maybe_do_recaptcha_v3();

		// End binds.

		$this->container->register( Hooks::class );
		$this->container->register( Assets::class );
	}

	/**
	 * Replace reCAPTCHA v2 with v3 if there is a license key set.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function maybe_do_recaptcha_v3() {
		$ce_options = \Tribe__Events__Community__Main::getOptions();
		$recaptcha_key = $ce_options['recaptchaPublicKey'];

		if ( $recaptcha_key != '' ) {
			// Template override for the main templates (in src/views/community).
			add_filter( 'tribe_events_template_paths', [ $this, 'template_base_paths' ] );

			// Template override for module templates (under src/views/community).
			add_filter( 'tribe_get_template_part_path', [ $this, 'custom_templates' ], 10, 4 );

			// Add scripts needed for v3.
			add_action( 'wp_head', [ $this, 'add_recaptcha_scripts' ] );
		}
	}

	/**
	 * Add template override location for the main template files.
	 * (Files in src/views/community.)
	 *
	 * @param array $paths The template paths.
	 *
	 * @return array The new template paths.
	 *
	 * @since 1.0.0
	 */
	function template_base_paths( array $paths ): array {
		$slug = "tec-labs-" . PUE::get_slug();
		$paths[$slug] = trailingslashit( plugin_dir_path( TRIBE_EXTENSION_CE_RECAPTCHA_V3_FILE ) );

		return $paths;
	}

	/**
	 * Add template override location for template parts.
	 * (Files under src/views/community.)
	 *
	 * @param string $file The template file name with full server path.
	 * @param string $template The template file name with the default override path. (E.g. community/modules/template.php)
	 * @param string $slug The template slug. (Same as file name but without extension.)
	 * @param string|null $name
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	function custom_templates( string $file, string $template, string $slug, ?string $name ): string {
		$custom_folder = "src/views";

		$plugin_path = implode( "", array_merge(
			(array) trailingslashit( plugin_dir_path( TRIBE_EXTENSION_CE_RECAPTCHA_V3_FILE ) ),
			(array) $custom_folder,
		) );

		$new_file = $plugin_path . "/" . $template;

		if ( file_exists( $new_file ) ) {
			return $new_file;
		}

		return $file;
	}

	/**
	 * Add recaptcha scripts to the head.
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 *
	 * @see https://developers.google.com/recaptcha/docs/v3
	 */
	function add_recaptcha_scripts() {
		if ( tribe_context()->get('view') == "community_event_page" ) {
			echo '<script src="https://www.google.com/recaptcha/api.js"></script>';
			echo '<script>function onSubmit(token) { document.getElementById("tec_ce_submission_form").submit(); }</script>';
		}
	}

	/**
	 * Checks whether the plugin dependency manifest is satisfied or not.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the plugin dependency manifest is satisfied or not.
	 */
	protected function check_plugin_dependencies() {
		$this->register_plugin_dependencies();

		return tribe_check_plugin( static::class );
	}

	/**
	 * Registers the plugin and dependency manifest among those managed by Tribe Common.
	 *
	 * @since 1.0.0
	 */
	protected function register_plugin_dependencies() {
		$plugin_register = new Plugin_Register();
		$plugin_register->register_plugin();

		$this->container->singleton( Plugin_Register::class, $plugin_register );
		$this->container->singleton( 'extension.ce_recaptcha_v3', $plugin_register );
	}

	/**
	 * Get this plugin's options prefix.
	 *
	 * Settings_Helper will append a trailing underscore before each option.
	 *
	 * @return string
     *
	 * @see \Tribe\Extensions\CeRecaptchaV3\Settings::set_options_prefix()
	 */
	private function get_options_prefix() {
		return (string) str_replace( '-', '_', 'tec-labs-ce-recaptcha-v3' );
	}

	/**
	 * Get Settings instance.
	 *
	 * @return Settings
	 */
	private function get_settings() {
		if ( empty( $this->settings ) ) {
			$this->settings = new Settings( $this->get_options_prefix() );
		}

		return $this->settings;
	}

	/**
	 * Get all of this extension's options.
	 *
	 * @return array
	 */
	public function get_all_options() {
		$settings = $this->get_settings();

		return $settings->get_all_options();
	}

	/**
	 * Get a specific extension option.
	 *
	 * @param $option
	 * @param string $default
	 *
	 * @return array
	 */
	public function get_option( $option, $default = '' ) {
		$settings = $this->get_settings();

		return $settings->get_option( $option, $default );
	}
}
