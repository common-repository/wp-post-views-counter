<?php
/*====================================================================
				Admin Class For Post view Counter
======================================================================*/
/*

Author	:	Ramandeep Singh
URI		:	http://designaeon.com

*/
if(!class_exists("TheaAdminCounter")){
	class TheaAdminCounter{
		var $sname = THEACOUNTER;		
		private $general_settings_key = 'general_settings';
		private $advanced_settings_key = 'advanced_settings';
		private $plugin_options_key = 'wp-post-views-counter';
		private $plugin_settings_tabs = array();
		
		function __construct(){
			
			//init vars
			$this->general_settings_key = THEACOUNTER.'general_settings';
			$this->advanced_settings_key = THEACOUNTER.'advanced_settings';			
			//script
			
			add_action('admin_enqueue_scripts',array(&$this,'registerScripts'));
			
			add_action( 'init', array( &$this, 'load_settings' ) );
			add_action( 'admin_init', array( &$this, 'register_general_settings' ) );
			add_action( 'admin_init', array( &$this, 'register_advanced_settings' ) );
			add_action( 'admin_menu', array( &$this, 'set_admin_menu' ) );	
		
			//register widgets
			$this->loadWidgets();
			add_action( 'widgets_init', array($this,'registerWidgets') );
			
			//dashwidgets
			add_action('wp_dashboard_setup', array($this,'da_dashboard_widgets'));
			
		}
		
		function load_settings() {
			$this->general_settings = (array) get_option( $this->general_settings_key );
			$this->advanced_settings = (array) get_option( $this->advanced_settings_key );
		
			// Merge with defaults
			$this->general_settings = array_merge( array(
				'ViewsPosition' => 'bottom',
				'StBackground'	=> '#fafafa',
				'StForeground'	=> '#000',
				'StFontsize'	=>	'20',
				'StStyle'	=>	'custom',
				'StEnableOn'=>array('post')
			), $this->general_settings );
		
			$this->advanced_settings = array_merge( array(
				'check_ajax' => 0
			), $this->advanced_settings );
		}
		
		function registerScripts(){
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'thea-admin', THEAURI.'admin/js/script.js', array( 'wp-color-picker' ), false, true );
		}
		
		//set up admin pages
		function set_admin_menu(){
			//add_menu_page( 'Wp Utilities', 'Wp Utilities', 'manage_options',  'wp-utilities-page' ); 
			
			//add_submenu_page( 'wp-utilities-page', 'Wp Post View Counter', 'Wp Post View Counter', 'manage_options', $this->plugin_options_key, array($this,'thea_admin_page') );
			add_options_page( 'Wp Post View Counter', 'Wp Post View Counter', 'manage_options', $this->plugin_options_key, array($this,'thea_admin_page') ); 
		}	
		
		//general settings
		function register_general_settings() {
			$this->plugin_settings_tabs[$this->general_settings_key] = 'General';		
			register_setting( $this->general_settings_key, $this->general_settings_key );
			add_settings_section( 'section_general', 'General Plugin Settings', array( &$this, 'section_general_desc' ), $this->general_settings_key );
			add_settings_field( 'ViewsPosition', 'Views Position', array( &$this, 'field_general_option' ), $this->general_settings_key, 'section_general' );
			add_settings_field( 'StBackground', 'Background', array( &$this, 'field_general_bg' ), $this->general_settings_key, 'section_general');
			
			add_settings_field( 'StForeground', 'Foreground', array( &$this, 'field_general_fg' ), $this->general_settings_key, 'section_general');			
			add_settings_field( 'StFontsize', 'Font Size', array( &$this, 'field_general_fs' ), $this->general_settings_key, 'section_general' );
			
			add_settings_field( 'StStyle', 'Style', array( &$this, 'field_general_style' ), $this->general_settings_key, 'section_general' );
			add_settings_field( 'StEnableOn', 'Enable ON', array( &$this, 'field_general_enable' ), $this->general_settings_key, 'section_general' );
		}
		
		function section_general_desc() { echo 'General Settings.'; }
		
		function field_general_option() {
			?>
			
            <select name="<?php echo $this->general_settings_key; ?>[ViewsPosition]">
            	<option value="top" <?php echo selected( $this->general_settings['ViewsPosition'], 'top', false)  ?>>Above Content</option>
                <option value="bottom" <?php echo selected( $this->general_settings['ViewsPosition'], 'bottom', false)  ?>>Below Content</option>
                <option value="disabled" <?php echo selected( $this->general_settings['ViewsPosition'], 'disabled', false)  ?>>Disabled</option>
            </select>
			<?php
		}
		function field_general_bg() {
			?>
			<input type="text" class="pick-color" name="<?php echo $this->general_settings_key; ?>[StBackground]" value="<?php echo $this->general_settings['StBackground'] ?>" />
            
			<?php
		}
		function field_general_fg() {
			?>
			<input type="text" class="pick-color" name="<?php echo $this->general_settings_key; ?>[StForeground]"  value="<?php echo $this->general_settings['StForeground'] ?>" />
            
			<?php
		}
		function field_general_fs() {
			?>
			<input type="text"  name="<?php echo $this->general_settings_key; ?>[StFontsize]"  value="<?php echo $this->general_settings['StFontsize'] ?>" />px
            
			<?php
		}
		function field_general_style() {
			?>
			
            <select name="<?php echo $this->general_settings_key; ?>[StStyle]">
            	 <option value="no-style" <?php echo selected( $this->general_settings['StStyle'], 'no-style', false)  ?>>No Sytle</option>
                <option value="custom" <?php echo selected( $this->general_settings['StStyle'], 'custom', false)  ?>>Custom</option>
               
            </select>
            <span style="font-size:11px;font-style:italic">Choosing No Style Will Override all Color Styles and Display Simple Text</span>
			<?php
		}
		function field_general_enable() {
			$post_types = get_post_types( array('public'=>true), 'names' ); 
			//print_r($this->general_settings);
			?>
			<?php foreach($post_types as $k=>$type): ?>
            	<input type="checkbox" id="<?php echo $k.$type; ?>" <?php  if(in_array($type,$this->general_settings['StEnableOn'])){echo "checked='checked'";} ?> name="<?php echo $this->general_settings_key; ?>[StEnableOn][]" value="<?php echo $type ?>" />
                <label for="<?php echo $k.$type; ?>"><?php echo ucfirst($type); ?></label>
            <?php endforeach; ?>
            
			<?php
		}
		
		//advance tab
		function register_advanced_settings() {
			$this->plugin_settings_tabs[$this->advanced_settings_key] = 'Advanced';
		
			register_setting( $this->advanced_settings_key, $this->advanced_settings_key );
			add_settings_section( 'section_advanced', 'Advanced Plugin Settings', array( &$this, 'section_advanced_desc' ), $this->advanced_settings_key );
			add_settings_field( 'check_ajax', 'Check For Ajax Request', array( &$this, 'field_advanced_option' ), $this->advanced_settings_key, 'section_advanced' );
		}
		
		function section_advanced_desc() { echo 'Advanced Settings.'; }
		
		function field_advanced_option() {
			?>
			<input type="checkbox" name="<?php echo $this->advanced_settings_key; ?>[check_ajax]" <?php echo  checked( 1, $this->advanced_settings['check_ajax'], false ); ?>  value="1" />
			<?php
		}
		
		//admin menu
		function thea_admin_page(){
			 $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
			?>
			<div class="wrap">
				<?php $this->plugin_options_tabs(); ?>
				<form method="post" action="options.php">
					<?php wp_nonce_field( 'update-options' ); ?>
					<?php settings_fields( $tab ); ?>
					<?php do_settings_sections( $tab ); ?>
					<?php submit_button(); ?>
				</form>
                
                <div>
                	 <p>        
                        <hr />
                
                        <label>If you liked this plugin, Please like on facebook ,G+:  </label><br />
                
                <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Ffacebook.com%2Fdesignaeon&amp;width&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=80&amp;appId=175431785895681" scrolling="no" frameborder="0" style="border:none; overflow:hidden;width:100%; height:50px;" allowTransparency="true"></iframe>
                
                    <iframe src="//www.facebook.com/plugins/subscribe.php?href=https%3A%2F%2Fwww.facebook.com%2Framandeep000&amp;layout=button_count&amp;show_faces=false&amp;colorscheme=light&amp;font&amp;width=120&amp;appId=102008056593077" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px;  height:21px;" allowTransparency="true"></iframe>
                
                    <a href="https://plus.google.com/103049352972527333852?prsrc=3" rel="author" style="display:inline-block;text-decoration:none;color:#333;text-align:center;font:13px/16px arial,sans-serif;white-space:nowrap;"><span style="display:inline-block;font-weight:bold;vertical-align:top;margin-right:5px;margin-top:0px;">Follow</span><span style="display:inline-block;vertical-align:top;margin-right:13px;margin-top:0px;">on</span><img src="https://ssl.gstatic.com/images/icons/gplus-16.png" alt="" style="border:0;width:16px;height:16px;"/></a>
                
                
                
                        </p>
                        
                        <p>Support this widget Share it! For more info, go to <a href="http://www.designaeon.com/wp-post-views-counter/" target="_blank">WP Post Views Counter</a>  page</p>
                </div>
			</div>
			<?php
		}
		
		//the tabs
		function plugin_options_tabs() {
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $this->general_settings_key;
		
			screen_icon();
			echo '<h2 class="nav-tab-wrapper">';
			foreach ( $this->plugin_settings_tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->plugin_options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			echo '</h2>';
		}
		
		//register the widgets
		function loadWidgets(){
			foreach (glob(THEAPATH ."/admin/inc/widgets/*.widget.php") as $file) {
				require_once($file);									

			}		

		}
		
		function registerWidgets(){
			register_widget( 'TheaViews' );
		}
		
		//add the feeds
		
		//designaeon feeds

		
		
		function da_dashboard_widgets() {
		
			 global $wp_meta_boxes;
		
			 // remove unnecessary widgets
		
			 // var_dump( $wp_meta_boxes['dashboard'] ); // use to get all the widget IDs
		
			 unset(
		
				  $wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins'],
		
				  $wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary'],
		
				  $wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']
		
			 );
		
			 // add a custom dashboard widget
			wp_add_dashboard_widget( 'dashboard_custom_feed', 'Important News you must read', array($this,'dashboard_da_custom_feed_output') );
			  //add new RSS feed output
		
		}
		
		function dashboard_da_custom_feed_output() {
		
			 echo '<div class="rss-widget">';
		
			 wp_widget_rss_output(array(
		
				  'url' => 'http://feeds.feedburner.com/designaeon',
		
				  'title' => 'What\'s up at Design Aeon',
		
				  'items' => 10,
		
				  'show_summary' => 1,
		
				  'show_author' => 0,
		
				  'show_date' => 1 
		
			 ));
		
			 echo "</div>";
		
		}
		
		//end feeds
		
	}//end of class
}
