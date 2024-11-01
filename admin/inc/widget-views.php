<?php
/*====================VIEWS WIDGET WORDPRESS========================
===================================================================*/

class TheaViews extends WP_Widget{
	
	public function __construct() {

		$widget_ops = array( 'classname' => 'views-widget', 'description' => 'Post Views Widget for wordpress '  );

		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'views-widget' );

		$this->WP_Widget( 'views-widget',THEACOUNTER .' - Views ', $widget_ops, $control_ops );		

	}


	public function widget($args, $instance){
		extract( $args );
		if(is_singular()){
		$title = apply_filters('widget_title', $instance['title'] );

		echo $before_widget;

		if(!empty($title)){

			echo $before_title;

			echo $title ; 

			echo $after_title;

		}
		?>

        <div class="thea-widget address-widget">        	

      		<?php if(!empty($instance['address'])):	?>
            <address><i class="icon-location"></i> <span class="addr"><?php echo $instance['address'] ?></span></address>
			<?php endif; ?>
           
       </div>

        <?php

		echo $after_widget;	
		}
	}
	
	public function form($instance){
		$defaults = array( 'title' => __('Post Views', 'thea'),'address'=>'','telephone'=>'','email'=>'' );

		$instance = wp_parse_args( (array) $instance, $defaults );
		?>
        
        <p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title : </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" type="text" />
		</p> 
        <?php
	}
	
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );

			

		return $instance;

	}	
}