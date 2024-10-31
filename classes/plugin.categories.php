<?php 


if( !class_exists( 'Plugin_Categories' ) ) {

	class Plugin_Categories {

		protected static $readable_properties  = array();    // These should really be constants, but PHP doesn't allow class constants to be arrays
		protected static $writeable_properties = array();
		protected $modules;

		public function __construct() {

			// localize plugin
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );

			// modules
			$this->modules = array(
				'PC_Views'		=> new PC_Views
			);

			// load all modules
			$this->instances();

		}

		/**
		 * Localize plugin
		 *
		 * @return
		 */

		function load_textdomain() {
		
			$domain = 'plugin-categories';
			$locale = apply_filters( 'plugin_locale', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
				
		}

		/**
		 * Load modules
		 *
		 * @return
		 */
		
		private function instances() {

			if($this->modules) {
				foreach($this->modules as $module => $class) {

					$_GLOBALS[$module] = $class;

				}
			}

		}

		/**
		 * Plugin activation
		 *
		 * @return
		 */
		
		public function activate() {

			foreach($this->modules as $module)
				$module->activate();

			flush_rewrite_rules();

		}

		/**
		 * Plugin deactivation
		 *
		 * @return
		 */

		public function deactivate() {

			foreach($this->modules as $module)
				$module->deactivate();

			flush_rewrite_rules();

		}

	}

}

?>