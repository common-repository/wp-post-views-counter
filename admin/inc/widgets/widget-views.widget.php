<?php
/*====================VIEWS WIDGET WORDPRESS========================
===================================================================*/

class TheaViews extends WP_Widget{
	
	public function __construct() {

		$widget_ops = array( 'classname' => 'views-widget', 'description' => 'Post Views Widget for wordpress '  );

		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'views-widget' );

		$this->WP_Widget( 'views-widget',THEACOUNTER .' - Post Views ', $widget_ops, $control_ops );		

	}


	public function widget($args, $instance){
		extract( $args );
		if(is_singular()){
		$title = apply_filters('widget_title', $instance['title'] );
		
		$show_uniq = $instance['show_uniq'] ? true : false ;
		$hide_credit = $instance['hide_credit'] ? true : false ;

		echo $before_widget;

		if(!empty($title)){

			echo $before_title;

			echo $title ; 

			echo $after_title;

		}
		?>

        <div class="thea-widget post-views-widget">
        	<div class="view-box">
           		<h4 class="views get_thea_count_number thea-number"><?php echo number_format(TheaCounter::get_post_views()); ?></h4>
                <span class="view-subtitle">Views</span> 
            </div>
            <?php if($show_uniq): ?>   		
			<div class="view-box">
           		<h4 class="views get_thea_count_number thea-uniq-number"><?php echo number_format(TheaCounter::get_uniq_views()); ?></h4>
                <span class="view-subtitle">Unique Views</span>  
            </div>
            <?php endif; ?>
            <?php if(!$hide_credit): ?>   		
	        <span class="credit">Developed by <a href="http://designaeon.com" target="_blank">Ramandeep Singh</a></span>
            <?php endif; ?>   
       </div>

        <?php

		echo $after_widget;	
		}
	}
	
	public function form($instance){
		$defaults = array( 'title' => __('Post Views', 'thea'),'show_uniq'=>1,'hide_credit'=>0 );

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
        
        <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" type="text" />
		</p> 
        
         <p>
			<input id="<?php echo $this->get_field_id( 'show_uniq' ); ?>" name="<?php echo $this->get_field_name( 'show_uniq' ); ?>" value="true" <?php if( @$instance['show_uniq'] ) echo 'checked="checked"'; ?> type="checkbox" />

			<label for="<?php echo $this->get_field_id( 'show_uniq' ); ?>">Show Unique Views?  </label>
		</p>
        
            <p>			

			<input id="<?php echo $this->get_field_id( 'hide_credit' ); ?>" name="<?php echo $this->get_field_name( 'hide_credit' ); ?>" value="true" <?php if( @$instance['hide_credit'] ) echo 'checked="checked"'; ?> type="checkbox" />

			<label for="<?php echo $this->get_field_id( 'hide_credit' ); ?>">Hide Credit  </label>

        </p> 
        
        <p>        
        <hr />

		<label>If you liked this plugin, Please like on facebook ,G+:  </label><br />

<iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Ffacebook.com%2Fdesignaeon&amp;width&amp;layout=standard&amp;action=like&amp;show_faces=true&amp;share=true&amp;height=80&amp;appId=175431785895681" scrolling="no" frameborder="0" style="border:none; overflow:hidden;width:100%; height:50px;" allowTransparency="true"></iframe>

	<iframe src="//www.facebook.com/plugins/subscribe.php?href=https%3A%2F%2Fwww.facebook.com%2Framandeep000&amp;layout=button_count&amp;show_faces=false&amp;colorscheme=light&amp;font&amp;width=120&amp;appId=102008056593077" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:120px;  height:21px;" allowTransparency="true"></iframe>

	<a href="https://plus.google.com/103049352972527333852?prsrc=3" rel="author" style="display:inline-block;text-decoration:none;color:#333;text-align:center;font:13px/16px arial,sans-serif;white-space:nowrap;"><span style="display:inline-block;font-weight:bold;vertical-align:top;margin-right:5px;margin-top:0px;">Follow</span><span style="display:inline-block;vertical-align:top;margin-right:13px;margin-top:0px;">on</span><img src="https://ssl.gstatic.com/images/icons/gplus-16.png" alt="" style="border:0;width:16px;height:16px;"/></a>



		</p>
		
		<p>Support this widget Share it! For more info, go to <a href="http://www.designaeon.com/wp-post-views-counter/" target="_blank">WP Post Views Counter</a>  page</p>
		
        <?php
	}
	
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );	
		$instance['show_uniq'] = strip_tags( $new_instance['show_uniq'] );	
		$instance['hide_credit'] = strip_tags( $new_instance['hide_credit'] );			

		return $instance;

	}	
}