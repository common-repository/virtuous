<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'VirtuousCore' ) ) :

class VirtuousCore {

	function __construct() {

		$this->_init();
	}

	private function _init() {

		$this->load_config();

		if ( $this->load_class( 'utilities' ) ) {
			global $VirtuousUtilities;
			$VirtuousUtilities = new VirtuousUtilities();
		}

		if ( $this->load_class( 'api' ) ) {
			global $VirtuousApi;
			$VirtuousApi = new VirtuousApi();
		}

		if ( $this->load_class( 'project' ) ) {
			global $VirtuousProject;
			$VirtuousProject = new VirtuousProject();
		}
	}

	function load_class( $class_name ) {

		$class_file = VIRTUOUS::get_base_path() . '/lib/core/class-virtuous-' . $class_name . '.php';

		if ( file_exists( $class_file ) ) {
			include_once( $class_file );
			return true;
		}
	}

	function load_config() {

		$config_file = VIRTUOUS::get_base_path() . '/lib/conf/config.php';

		if ( file_exists( $config_file ) ) {
			include_once( $config_file );
		}
	}
}

endif;