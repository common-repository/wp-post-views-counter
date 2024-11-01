<?php
/*====================================================================
				FrontEnd Class For Post view Counter
======================================================================*/
/*

Author	:	Ramandeep Singh
URI		:	http://designaeon.com

*/
if(!class_exists("TheaCounter")){
	class TheaCounter{
		public $key,$uniq_key;public static $skey,$suniq_key;
		private $general_settings,$advance_settings;public static $g_settings,$a_settings;
		function __construct(){
			$this->general_settings_key = THEACOUNTER.'general_settings';
			$this->advanced_settings_key = THEACOUNTER.'advanced_settings';
			
			$this->general_settings = (array) get_option( $this->general_settings_key );
			self::$g_settings = (array) get_option( $this->general_settings_key );
			$this->advanced_settings = (array) get_option( $this->advanced_settings_key );
			self::$a_settings = (array) get_option( $this->advanced_settings_key );
			//init admin
			$this->init_admin();
			
			//load lib
			$this->load_libs();
			
			$this->key = THEACOUNTER.'post_views_count';self::$skey = THEACOUNTER.'post_views_count';
			$this->uniq_key = THEACOUNTER.'uniq_post_count';self::$suniq_key = THEACOUNTER.'uniq_post_count';
			
			//register the scripts and styles
			add_filter('wp_enqueue_scripts',array($this,'registerScripts'));
			add_action('wp_head', array($this,'registerGlobals'));
			//post views		
			add_action('wp_head',array( $this,'get_post_id'));
			//remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head',10, 0);
			//add_action('wp_head', array($this,'setPostViews'));
			
			
		
			//set up unique views
			//add_action('init', array($this,'setUniqPostViews'));			
			
			// add the Counter Column
			add_filter('manage_posts_columns', array($this,'posts_column_views'));
			add_action('manage_posts_custom_column', array($this,'posts_custom_column_views'),5,2);
			// unique columns
			add_filter('manage_posts_columns', array($this,'posts_uniq_column_views'));
			add_action('manage_posts_custom_column', array($this,'posts_custom_uniq_column_views'),5,2);
			
			//display the views
			add_action('wp_footer',array($this,'render_footer_script'));			
			add_filter('the_content', array($this,'display_views'));
			
			
			//ajax actions
			add_action( 'wp_ajax_get_thea_post_count', array($this,'thea_ajax_count') );
			add_action( 'wp_ajax_nopriv_get_thea_post_count', array($this,'thea_ajax_count') );
			
			add_action( 'wp_ajax_set_thea_uniq_post_count', array($this,'ajaxSetUniqPostViews') );
			add_action( 'wp_ajax_nopriv_set_thea_uniq_post_count', array($this,'ajaxSetUniqPostViews') );
			
			add_action( 'wp_ajax_get_thea_uniq_post_count', array($this,'thea_ajax_uniq_count') );
			add_action( 'wp_ajax_nopriv_get_thea_uniq_post_count', array($this,'thea_ajax_uniq_count') );
			
			//ajax powered
			add_action( 'wp_ajax_set_post_views', array($this,'ajaxSetPostViews') );
			add_action( 'wp_ajax_nopriv_set_post_views', array($this,'ajaxSetPostViews') );
			
			add_action( 'wp_ajax_get_post_views', array($this,'ajaxGetPostViews') );
			add_action( 'wp_ajax_nopriv_get_post_views', array($this,'ajaxGetPostViews') );
		}
		
		function init_admin(){
			require_once(THEAPATH.'/admin/wp-post-views-counter.admin.php');
		}
		function load_libs(){
			require_once(THEAPATH.'/lib/utility.class.php');
		}
		
		function registerScripts(){
			
			//font
			wp_enqueue_style('thea-font',THEAURI.'font/css/postcounter.css');
			wp_enqueue_style('thea-counter',THEAURI.'css/style.css');
			wp_enqueue_script('thea-counter',THEAURI.'js/script.js',array('jquery'),'0.5',true);
		}
		function registerGlobals(){
			?>
            <script type="text/javascript">
				var TheaScript = {TheaAjaxUrl : '<?php echo admin_url( 'admin-ajax.php' ); ?>',id: <?php echo get_the_ID(); ?>};
			</script>
            <?php	
		}
		
		//set up post counter
		//post views funcitons
		function get_post_id() {
			$postID = get_the_ID();
			return $postID;
		}
		
		function getPostViews(){
			$postID = $this->get_post_id();
			$count_key = $this->key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				return __('0');
			}
			return $count;
		}
		
		//set the views
		
		function setPostViews() {		
			$is_called = get_option( THEACOUNTER.'cr_counter_called',false);
			if($this->advance_settings['check_ajax']){
					if(!$is_called){
						$this->setRegularViews();
						update_option( THEACOUNTER.'cr_counter_called', true );					
					}
					else{
						delete_option( THEACOUNTER.'cr_counter_called' );
						add_option( THEACOUNTER.'cr_counter_called', false);
					}	
			}
			else{
				$this->setRegularViews();	
			}
						
		}
		
		
		/*
		deprecated
		
		*/
		function setUniqPostViews(){
			$url = explode('?', 'http://'.$_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
			$postid = url_to_postid($url[0]);			
			$post_id = $postid;	
			$usr_ip = $_SERVER['REMOTE_ADDR'];
			$ctime = time();
			$expire=time()+60*60*24*30;
			$c_data = array(
				'post_id'=>$post_id,
				'ip'=>$usr_ip,
				'time'=>$ctime
			);
			$cdata = json_encode($c_data,true);
			if (isset($_COOKIE[THEADOMAIN."uniq_views"][$post_id])){	
				//echo false;	
			}
			else{
				//$current_cookie = json_decode($_COOKIE['fz_likes']);
				setcookie(THEADOMAIN."uniq_views[".$post_id."]",$cdata,$expire,"/");						
				$this->setUniqueViews($post_id);							
			}
		}
		
		/*
		The Column FUnctions
		*/
		//the columns
		function posts_column_views($defaults){
			$defaults['post_views'] = __('Views');
			return $defaults;
		}
		
		function posts_custom_column_views($column_name, $id){
			if($column_name === 'post_views'){
				echo $this->getPostViews(get_the_ID());
			}
		}
		
		//the uniq Column
		
		function posts_uniq_column_views($defaults){
			$defaults['uniq_views'] = __('Unique Views');
			return $defaults;
		}
		
		function posts_custom_uniq_column_views($column_name, $id){
			if($column_name === 'uniq_views'){
				echo $this->getUniqueViews(get_the_ID());
			}
		}
		
		
		function setRegularViews(){
			global $wp_query;
			if ( is_singular() ) {
				$post_id = $wp_query->get_queried_object_id();	
				$old_views = get_post_meta( $post_id, $this->key, true );	
				$new_views = absint( $old_views ) + 1;	
				update_post_meta( $post_id, $this->key, $new_views, $old_views );				
			}
		}
		//set the unique views
		function setUniqueViews($id=null){
			$postID = $id;
			$count_key = $this->uniq_key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				$count = 0;
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
			}else{
				$count++;
				update_post_meta($postID, $count_key, $count);
			}
			return $count;
		}
		//get the unique views
		function getUniqueViews($id=null){
			$postID = $id;
			$count_key = $this->uniq_key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				return 0;
			}
			return $count;
		}
		/*
		AJAX VIEWS FUNCTIONS 
		*/
		function ajaxSetPostViews(){
			$postID = $_POST['pid'];
			$count_key = $this->key;
			$count = get_post_meta($postID, $count_key, true);				
			if($count==''){
				$count = 0;
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
			}else{
				$count++;
				update_post_meta($postID, $count_key, $count);
			}
			echo $count;
			die();
		}
		
		function ajaxGetPostViews(){
			$postID = $_POST['pid'];
			$count_key = $this->key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				//return '0';
			}
			echo $count;
			die();
		}
		
		function ajaxSetUniqPostViews(){			
			$post_id = $_POST['pid'];	
			$usr_ip = $_SERVER['REMOTE_ADDR'];
			$ctime = time();
			$expire=time()+60*60*24*30;
			$c_data = array(
				'post_id'=>$post_id,
				'ip'=>$usr_ip,
				'time'=>$ctime
			);
			$cdata = json_encode($c_data,true);
			if (isset($_COOKIE[THEADOMAIN."uniq_views"][$post_id])){	
				//echo false;	
			}
			else{
				//$current_cookie = json_decode($_COOKIE['fz_likes']);
				setcookie(THEADOMAIN."uniq_views[".$post_id."]",$cdata,$expire,"/");						
				$count = $this->setUniqueViews($post_id);							
				echo $count;
			}
			die();
		}
		
		function ajaxGetUniqueViews($id=null){
			$postID = $_POST['pid'];;
			$count_key = $this->uniq_key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				return 0;
			}
			echo $count;
			die();
		}
		//Render Footer Script
		function render_footer_script(){
			?>
            <script type="text/javascript">
				 //<![CDATA[
				 (function($) {
					 $(document).ready(function () {					 
						 
						 //counter
						 <?php if(is_singular()): ?>
						 var data = {
							'action': 'set_post_views',
							'pid': '<?php echo get_the_ID(); ?>'    
						};
						 jQuery.post(TheaScript.TheaAjaxUrl, data, function(response) {
							//console.log(response);
						});
						/* var data1 = {
							'action': 'get_post_views',
							'pid': '<?php echo get_the_ID(); ?>'     
						};
						 jQuery.post(TheaScript.TheaAjaxUrl, data1, function(response) {
							//console.log(response);
							$(".get_thea_count_number.thea-number").html(response);
						});*/
						
						var data3 = {
							'action': 'set_thea_uniq_post_count',
							'pid': '<?php echo get_the_ID(); ?>'    
						};
						 jQuery.post(TheaScript.TheaAjaxUrl, data3, function(response) {
							//console.log(response);
						});
						<?php endif; ?>
						
											
					});
				 })(jQuery);
				//]]>
			</script>
            <?php	
			
		}
		//show the views
		
		function display_views($content){
			if(is_singular($this->general_settings['StEnableOn'])){			
				$id = $this->get_post_id();
				$bg = $this->general_settings['StBackground'];
				$darkbg = TheaUtil::colourBrightness($bg,'-0.75'); 
				$fg = $this->general_settings['StForeground'];
				$fs = $this->general_settings['StFontsize'].'px';
				$fStyle = $this->general_settings['StStyle'];
				$style = "Style='background-color:{$bg};color:{$fg};font-size:{$fs};'";
				$style1 = "Style='background-color:{$bg};color:{$fg};font-size:{$fs};'";
				if($fStyle=='no-style'){
					$views = "<div class='thea-post-views no-style'><h4> This Post Has Been Viewed <strong class='get_thea_count_number thea-number'>".number_format($this->getPostViews())."</strong> Times</</h4></div>";
				}
				else{
					$views = "<div class='thea-post-views' {$style}><i class='thea-icon-eye' style='background:{$darkbg};'></i><h4 {$style1}> This Post Has Been Viewed <strong class='get_thea_count_number thea-number'>".number_format($this->getPostViews())."</strong> Times</</h4></div>";	
				}
				
				if($this->general_settings['ViewsPosition']=='top'){
					return $views.$content;
				}
				elseif($this->general_settings['ViewsPosition']=='bottom'){
					return $content.$views;
					
				}
				elseif($this->general_settings['ViewsPosition']=='disabled'){
					return $content;
				}
				else{
					return $content.$views;
				}
			}
			else{
				return $content;	
			}			
		}
		
		//helpers
		
		function extra_ajax_check(){
			if($this->advance_settings['check_ajax']){
				$is_called = get_option( THEACOUNTER.'cr_counter_called',false);
				if(!$is_called){
				
				}
			}
			else{
				return false;	
			}
		}
		
		//static functions
		static function get_post_views(){
			$postID = get_the_ID();
			$count_key = self::$skey;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				return __('0');
			}
			return $count;
		}
		
		static function get_uniq_views(){
			$postID = get_the_ID();
			$count_key = self::$suniq_key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				return 0;
			}
			return $count;
		}
		static function display_the_views($ct=false){
			$id = get_the_ID();
			if($ct){
				echo self::get_post_views();
			}
			else{
				$bg = self::$g_settings['StBackground'];
			$darkbg = TheaUtil::colourBrightness($bg,'-0.75'); 
			$fg = self::$g_settings['StForeground'];
			$fs = self::$g_settings['StFontsize'].'px';
			$fStyle = self::$g_settings['StStyle'];
			$style = "Style='background-color:{$bg};color:{$fg};font-size:{$fs};'";
			$style1 = "Style='background-color:{$bg};color:{$fg};font-size:{$fs};'";
				if($fStyle=='no-style'){
					$views = "<div class='thea-post-views'><h4> This Post Has Been Viewed <strong class='get_thea_count_number thea-number'>".number_format(self::get_post_views())."</strong> Times</</h4></div>";
					echo $views;
				}
				else{
						$views = "<div class='thea-post-views' {$style}><i class='thea-icon-eye' style='background:{$darkbg};'></i><h4 {$style1}> This Post Has Been Viewed <strong class='get_thea_count_number thea-number'>".number_format(self::get_post_views())."</strong> Times</</h4></div>";
					echo $views;
				}
				
			}
		}
		
		//ajax Functions
		function thea_ajax_count(){
			$postID = intval($_POST['id']);
			$count_key = $this->key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				echo __('0');
			}
			echo  number_format($count);
			die();	
		}
		function thea_ajax_uniq_count(){
			$postID = intval($_POST['id']);
			$count_key = $this->uniq_key;
			$count = get_post_meta($postID, $count_key, true);
			if($count==''){
				delete_post_meta($postID, $count_key);
				add_post_meta($postID, $count_key, '0');
				return 0;
			}
			echo number_format($count);
			die();
		}
		
		
	}//end of class
	
	//template tag function
	function wp_get_post_views_counter(){
		if(is_singular()){
			TheaCounter::display_the_views();	
		}		
	}
	function wp_get_only_post_views_count(){
		if(is_singular()){
			TheaCounter::display_the_views($true);	
		}		
	}
}				