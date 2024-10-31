<?php 

if( !class_exists( 'PC_Views' ) ) {

	class PC_Views extends Plugin_Categories {

		public $options;

		/**
		 * Plugin construct
		 *
		 * @return
		 */

		public function __construct() {

			add_action( 'init', array( $this, 'category' ) ); // create the taxonomy

			add_action( 'admin_menu', array( $this, 'admin_menu' ) ); // manipulate admin menu

			add_action( 'parent_file', array( $this, 'taxonomy_correction') ); // change parent file

			add_action( 'manage_plugins_custom_column', array( $this, 'column_content' ), 99, 3 ); // add column content to the plugins table

			add_filter( 'manage_plugins_columns', array( $this, 'column' ), 99, 3 ); // add custom column to the plugins table

			add_filter( 'plugin_action_links', array( $this, 'actions'), 99, 4 ); // add action links

			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) ); // add admin scripts

			add_action( 'wp_ajax_plugin_category', array( $this, 'ajax') ); // ajax actions

			add_action( 'pre_current_active_plugins', array( $this, 'before_table') ); // add content before plugin table

			add_filter( 'all_plugins', array( $this, 'filter_plugins') ); // the plugin filter

			add_filter( 'manage_edit-plugin_category_columns', array( $this, 'category_column' ), 99, 3 ); // add category column

			add_action( 'manage_plugin_category_custom_column', array( $this, 'category_column_content' ), 99, 3 ); // populate columns

		}


		/**
		 * Cleanup
		 *
		 * @return
		 */
		
		public function cleanup() {
			
			$options = wp_load_alloptions();

			foreach($options as $key => $value) {

				if( strpos($key, '_plugin_categories_') !== false ) {
					delete_option( $key );
				}

			}

			$terms = get_terms( 'plugin_category', 'hide_empty=0' );

			foreach($terms as $term) {

				$transient = 'plugin_category_count_'. $term->term_id;

				if( get_transient( $transient ) )
					delete_transient( $transient );

			}

		}

		/**
		 * Module activation
		 *
		 * @return
		 */
		
		public function activate() {
			
		}

		/**
		 * Module deactivation
		 *
		 * @return
		 */
		
		public function deactivate() {
			//$this->cleanup();
		}

		/**
		 * Move taxonomy under "Plugins"
		 *
		 * @return
		 */
		
		public function admin_menu() {

			add_plugins_page( _x( 'Plugin Categories', 'Taxonomy General Name', 'plugin-categories' ), __( 'Categories', 'plugin-categories' ), 'activate_plugins', 'edit-tags.php?taxonomy=plugin_category', '');

		}

		/**
		 * Register custom taxonomy
		 *
		 * @return
		 */

		public function category() {
			
			$labels = array(
				'name'                       => _x( 'Plugin Categories', 'Taxonomy General Name', 'plugin-categories' ),
				'singular_name'              => _x( 'Plugin Category', 'Taxonomy Singular Name', 'plugin-categories' ),
				'menu_name'                  => __( 'Categories', 'plugin-categories' ),
				'all_items'                  => __( 'All Categories', 'plugin-categories' ),
				'parent_item'                => __( 'Parent Plugin Category', 'plugin-categories' ),
				'parent_item_colon'          => __( 'Parent Plugin Category:', 'plugin-categories' ),
				'new_item_name'              => __( 'New Plugin Category Name', 'plugin-categories' ),
				'add_new_item'               => __( 'Add New Plugin Category', 'plugin-categories' ),
				'edit_item'                  => __( 'Edit Plugin Category', 'plugin-categories' ),
				'update_item'                => __( 'Update Plugin Category', 'plugin-categories' ),
				'separate_items_with_commas' => __( 'Separate categories with commas', 'plugin-categories' ),
				'search_items'               => __( 'Search Plugin Category', 'plugin-categories' ),
				'add_or_remove_items'        => __( 'Add or remove categories', 'plugin-categories' ),
				'choose_from_most_used'      => __( 'Choose from the most used categories', 'plugin-categories' ),
				'not_found'                  => __( 'Not Found', 'plugin-categories' ),
			);
			$capabilities = array(
				'manage_terms'               => 'activate_plugins',
				'edit_terms'                 => 'activate_plugins',
				'delete_terms'               => 'activate_plugins',
				'assign_terms'               => 'activate_plugins',
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => true,
				'public'                     => false,
				'show_ui'                    => false,
				'show_admin_column'          => false,
				'show_in_nav_menus'          => false,
				'show_tagcloud'              => false,
				'rewrite'                    => false,
				'capabilities'               => $capabilities,
			);
			register_taxonomy( 'plugin_category', array( 'plugin-invisible' ), $args );
			
		}

		/**
		 * Enqueue scripts
		 *
		 * @return
		 */
		
		function scripts() {

			$screen = get_current_screen();

			if($screen->base == 'plugins') {
				wp_enqueue_script( 'plugin-categories', plugins_url( 'assets/js/backend.js', PCAT_DIR ), array('jquery') );
				wp_enqueue_style( 'plugin-categories', plugins_url( 'assets/css/backend.css', PCAT_DIR ) );
			}

		}

		/**
		 * Make sure "Plugins" the parent page
		 *
		 * @return string The parent file
		 */
		
		function taxonomy_correction($parent_file) {
			
			global $current_screen;

			$taxonomy = $current_screen->taxonomy;

			if ($taxonomy == 'plugin_category' )
				$parent_file = 'plugins.php';

			return $parent_file;
		}

		/**
		 * Get plugin categories
		 *
		 * @return array The plugin's categories
		 */

		function get_plugin_categories($plugin) {

			$plugin = sanitize_title( $plugin );

			$categories = get_option( '_plugin_categories_'.$plugin, array() );

			return $categories;

		}

		/**
		 * Populate Category Columns
		 *
		 * @return int The total plugins in the category
		 */

		function category_column_content( $out, $column_name, $category ) {

			if($column_name == 'plugins') {

				$out .= '<a href="plugins.php?plugin_category='.$category.'">'.$this->get_plugin_count($category).'</a>';

			}

			return $out;

		}

		/**
		 * Register custom category columns
		 *
		 * @return array The columns registered
		 */
		

		function category_column( $columns ) {

			unset($columns['posts']);

			$columns['plugins'] = __( 'Plugins', PCAT_DOMAIN );

			return $columns;
		}

		/**
		 * Populate Columns
		 *
		 * @return string The plugin's categories
		 */

		function column_content($column_name, $plugin_file, $plugin_data) {

			if($column_name == 'category') {

				$categories = $this->get_plugin_categories( $plugin_file );

				if( empty($categories) ) {
					echo ' — ';
					return;
				}

				$terms = array();

				foreach($categories as $category) {

					$term = get_term_by( 'id', $category, 'plugin_category' );

					if($term)
						$terms[] = '<a href="'.admin_url( 'plugins.php?plugin_category='. $term->term_id ).'">'. $term->name .'</a>';

				}

				if($terms)
					echo implode(', ', $terms);
				else 
					echo ' — ';
			}

		}

		/**
		 * Register custom columns
		 *
		 * @return array The columns registered
		 */
		

		function column( $columns ) {

			$columns['category'] = __( 'Category', PCAT_DOMAIN );

			return $columns;
		}

		/**
		 * Register plugin actions
		 *
		 * @return array An array with the actions availiable
		 */

		function actions($actions, $plugin_file, $plugin_data, $context) {

			$actions['category'] = '<a class="thickbox" href="'.admin_url( 'admin-ajax.php' ).'?action=plugin_category&type=edit&plugin='.urlencode( $plugin_file ).'"">Edit categories</a>';

			return $actions;

		}

		/**
		 * Ajax functions
		 *
		 * @return string The form for editing categories and the save funcion
		 */
		

		function ajax() {

			$handle = !$_POST ? $_GET : $_POST;

			if($handle['type'] == 'edit') {

				$plugin = $handle['plugin'];

				$plugin = sanitize_title( $plugin );

				$categories = get_option( '_plugin_categories_'.$plugin, array() );

				?>
					
				<h2><?php _e( 'Edit Plugin Categories', PCAT_DOMAIN ); ?></h2>

				<?php

				$terms = get_terms( 'plugin_category', 'hide_empty=0' );
				?>
				
				<form action="" id="add-category">
					
					<ul id="categorychecklist" class="categorychecklist form-no-clear">

						<?php foreach($terms as $term) : ?>
					
							<li id="category-<?php echo $term->term_id; ?>">
								<label class="selectit">
									<input value="<?php echo $term->term_id; ?>" type="checkbox" name="plugin_category[]" id="in-category-<?php echo $term->term_id; ?>" <?php if( in_array($term->term_id, $categories) ) { echo 'checked="true"'; } ?>> 
									<?php echo $term->name; ?>
								</label>
							</li>

						<?php endforeach; ?>
					
					</ul>

					<button type="submit" class="button-primary" id="save_categories"><?php _e( 'Save Categories', PCAT_DOMAIN ); ?></button>
					
					<script type="text/javascript">

						jQuery(document).ready(function($) {
	
							$('#add-category').submit( function(e) {
								
								e.preventDefault();

								var categories = $("input[name='plugin_category\\[\\]']:checked").map(function(){return $(this).val();}).get();

								var data = {
									action: 'plugin_category',
									type: 'save',
									categories: categories,
									plugin: '<?php echo $handle['plugin']; ?>'
								}

								$.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) {

									if(response === 'true')
										location.reload();

								});

							});

						});

					</script>

				</form>
				<?php

			}
			elseif($handle['type'] == 'save') {

				$categories = $handle['categories'];

				$plugin = $handle['plugin'];

				$plugin = sanitize_title( $plugin );

				update_option( '_plugin_categories_'.$plugin, $categories );

				foreach($categories as $category)
					delete_transient( 'plugin_category_count_'. $category );

				echo 'true';

			}
			

			die();
		}

		/**
		 * Count plugins in a category
		 *
		 * @return string
		 */

		function get_plugin_count($category = null) {

			if(!$category)
				return;

			$transient = 'plugin_category_count_'. $category;

			$count = get_transient( $transient );

			if( false == $count ) {

				$count = 0;

				$options = wp_load_alloptions();

				foreach($options as $key => $value) {

					if( strpos($key, '_plugin_categories_') !== false ) {
						
						$categories = unserialize($value);

						foreach($categories as $cat) {

							if($cat === $category)
								$count++;

						}
					}
				}

				set_transient( $transient, $count, 43200 );

			}

			return $count;

		}

		/**
		 * Output category filtering
		 *
		 * @return string
		 */

		function before_table($plugins) {

			$current = isset($_GET['plugin_category']) ? $_GET['plugin_category'] : 0;

			?>
			<div class="tablenav">
				<div class="actions alignleft">
					<select onchange="document.location.href = this.value">
						<option value="plugins.php"><?php _e('View all plugins', PCAT_DOMAIN); ?></option>
						<?php 
						$terms = get_terms( 'plugin_category', 'hide_empty=0' );

						foreach($terms as $term) {
						?>
						<option value="plugins.php?plugin_category=<?php echo $term->term_id; ?>" <?php selected( $term->term_id, $current ); ?>><?php echo $term->name; ?></option>
						<?php
						}
						?>
					</select>
				</div> 
			<?php

			if( isset($_GET['plugin_category']) ) {

				$term = get_term_by( 'id', $_GET['plugin_category'], 'plugin_category' );

				if(!$term)
					return;

				?>

				<div class="tablenav-pages one-page alignleft"><?php echo sprintf( __( 'Showing plugins in the %s category.', PCAT_DOMAIN ), '<strong>'.$term->name.'</strong>' ); ?></div>
				<?php

			}

			?>
			</div>
			<div style="clear:both">&nbsp;</div>
			<?php

		}

		function filter_plugins($plugins) {

			if( isset($_GET['plugin_category']) && !empty($_GET['plugin_category']) ) {

				foreach($plugins as $plugin => $data) {

					$categories = $this->get_plugin_categories($plugin);

					if(!$categories)
						unset($plugins[$plugin]);
					else {

						if( !in_array($_GET['plugin_category'], $categories) )
							unset($plugins[$plugin]);

					}

				}

			}

			return $plugins;
		}


	}

}