<?php
/**
 * @package rss-per-page
 * @version 1.4
 */
/*
Plugin Name: rss-per-page
Plugin URI: http://www.funsite.eu/plugins/rss-per-page
Description: Adds a field to pages and implements a widget to show a RSS depending on that field. 
Author: Gerhard Hoogterp
Version: 1.4
Author URI: http://www.funsite.eu/
*/

if (!class_exists('basic_plugin_class')) {
	require(plugin_dir_path(__FILE__).'basics/basic_plugin.class');
}

class rss_per_page_widget extends WP_Widget {

	const FS_TEXTDOMAIN = rss_per_page_class::FS_TEXTDOMAIN; // as this widget is initizalized from the FS_rss_per_page class

	// constructor
	function rss_per_page_widget() {
		parent::WP_Widget(false, 
			$name = __('rss per page widget', self::FS_TEXTDOMAIN),
			array('description' => __('Show an rss feed build from an url and a page settable id.',self::FS_TEXTDOMAIN))
                );
	}


        function cachetimeout( $seconds ) {
            return 300;
        }	
	
	// widget form creation
	function form($instance) {
	    // Check values
	    if( $instance) {
			$title = esc_attr($instance['title']);
			$rssfeed = esc_attr($instance['rssfeed']);
			$defaultRSS = esc_textarea($instance['defaultRSS']);
			$defaultTitle = esc_textarea($instance['defaultTitle']);
			$maxShow = esc_textarea($instance['maxShow']);
	    } else {
			$title = __('Plugin reviews for @ID@',self::FS_TEXTDOMAIN);
			$rssfeed = 'https://wordpress.org/support/rss/view/plugin-reviews/@ID@';
			$defaultRSS = 'https://wordpress.org/news/feed/';
			$defaultTitle = 'Wordpress news';
			$maxShow = 3;
	    }
	    ?>

	    <p>
	    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', self::FS_TEXTDOMAIN); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	    </p>
	    <p>
	    <label for="<?php echo $this->get_field_id('rssfeed'); ?>"><?php _e('RSS feed (add <b>@ID@</b> where the RSS ID has to be inserted)', self::FS_TEXTDOMAIN); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('rssfeed'); ?>" name="<?php echo $this->get_field_name('rssfeed'); ?>" type="text" value="<?php echo $rssfeed; ?>" />
	    </p>
	    
   	    <p>
	    <label for="<?php echo $this->get_field_id('defaultRSS'); ?>"><?php _e('Default RSS feed (use when the page RSS ID is empty)', self::FS_TEXTDOMAIN); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('defaultRSS'); ?>" name="<?php echo $this->get_field_name('defaultRSS'); ?>" type="text" value="<?php echo $defaultRSS; ?>" />
	    </p>
	    
   	    <p>
	    <label for="<?php echo $this->get_field_id('defaultTitle'); ?>"><?php _e('Default RSS title (used with the default RSS feed)', self::FS_TEXTDOMAIN); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('defaultTitle'); ?>" name="<?php echo $this->get_field_name('defaultTitle'); ?>" type="text" value="<?php echo $defaultTitle; ?>" />
	    </p>

	    <p>
	    <label for="<?php echo $this->get_field_id('maxShow'); ?>"><?php _e('Show items', self::FS_TEXTDOMAIN); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('maxShow'); ?>" name="<?php echo $this->get_field_name('maxShow'); ?>" type="text" value="<?php echo $maxShow; ?>" />
	    </p>
	    
	    <?php
	}

	// widget update
	function update($new_instance, $old_instance) {
	    $instance = $old_instance;
	    // Fields
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['rssfeed'] = strip_tags($new_instance['rssfeed']);
	    $instance['defaultRSS'] = strip_tags($new_instance['defaultRSS']);
	    $instance['defaultTitle'] = strip_tags($new_instance['defaultTitle']);
	    $instance['maxShow'] = strip_tags($new_instance['maxShow']);
	    return $instance;
	}

	// widget display
	function widget($args, $instance) {
		extract( $args );

		// these are the widget options
		$rssfeed = apply_filters('widget_title', $instance['rssfeed']);
		$defaultRSS = apply_filters('widget_title', $instance['defaultRSS']);
		$defaultTitle = apply_filters('widget_title', $instance['defaultTitle']);
		
		$rss_id = get_post_meta( $GLOBALS['post']->ID, 'rss_id', true );
		if (apply_filters('widget_title', $rss_id)) {
			$feed = str_replace('@ID@',$rss_id,$rssfeed);
			} else {
			$feed = $defaultRSS;
		}
		
		// use feed title if local title is empty
		add_filter( 'wp_feed_cache_transient_lifetime' , array($this,'cachetimeout'));
                $rss = fetch_feed($feed);
                remove_filter( 'wp_feed_cache_transient_lifetime' , array($this,'cachetimeout'));
                
		if ($rss) {
			$title = apply_filters('widget_title', $instance['title']);
			if ($rss_id) {
				$title=str_replace('@ID@',$rss_id,$title);
				$title=$title?$title:$rss->channel->title;
			} else {
				$title=$default_title;
			}
			
		} else {
			$title=__("ERROR: Feed not found",self::FS_TEXTDOMAIN);
		}
		
		$maxShow = apply_filters('widget_title', $instance['maxShow']);
                $maxItems = $rss->get_item_quantity($maxShow);
		$rss_items = $rss->get_items( 0, $maxItems );
		
		echo $before_widget;
	  
		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box rss_per_page_hook">';

		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
			echo '<div class="entry">';
			if ($rss) {
				if ($maxItems) {
					foreach ($rss_items as $item) {
						echo '<div class="title"><a href="'.esc_url( $item->get_permalink() ) .'" rel="external">' . $item->get_title() . "</a></div>";
					
						echo '<p><span class="date">' . $item->get_date('G:i, j M Y') . "</span><br>";
						echo  strip_tags($item->get_description()) . "</p>";
					}
				} else {
					print '<span class="nonews">'.__('No news found', self::FS_TEXTDOMAIN).'</span>';
				}
			} else {
				print '<span class="nonews">'.__('RSS feed not found', self::FS_TEXTDOMAIN).'</span>';
			}
			echo '</div>';
			
		echo '</div>';
		echo $after_widget;
	}
}


class rss_per_page_class extends basic_plugin_class {

	function getPluginBaseName() { return plugin_basename(__FILE__); }
	function getChildClassName() { return get_class($this); }

        public function __construct() {
            parent::__construct();

            add_filter('plugin_row_meta', array($this,'rss_per_page_PluginLinks'),10,2);

            // admin interface
            add_action( 'admin_menu', array($this,'create_rss_id_box' ));
            add_action( 'save_post', array($this,'save_rss_id'), 10, 2 );

            // register widget
            add_action('widgets_init', create_function('', 'return register_widget("rss_per_page_widget");'));
            add_action('wp_head', array($this,'rss_per_page_headercode'),false,false,true);    
        }

        function pluginInfoRight($info) {  }
        
    	const FS_TEXTDOMAIN = 'rss-per-page';	
	const FS_PLUGINNAME = 'rss-per-page';
    

	function rss_per_page_headercode () {
		wp_enqueue_style('rss_per_page_handler', plugins_url('/css/rss-per-page.css', __FILE__ ));
	}		


	function create_rss_id_box() {
		add_meta_box( 'my-meta-box', __('RSS ID',self::FS_TEXTDOMAIN), array($this,'rss_id_box'), 'page', 'side', 'default' );
	}

	function rss_id_box( $object, $box ) { ?>
		<p>
			<label for="RSSID"><?php _e("Give the ID to replace the @ID@ placeholder in the widget for this page",self::FS_TEXTDOMAIN); ?></label><br>
			<input name="rss_id" id="RSSID" style="width: 97%;" value="<?php echo wp_specialchars( get_post_meta( $object->ID, 'rss_id', true ), 1 ); ?>">
			<input type="hidden" name="my_meta_box_nonce" value="<?php echo wp_create_nonce( plugin_basename( __FILE__ ) ); ?>" />
		</p>
	<?php }

	function save_rss_id( $post_id, $post ) {

		if ( !wp_verify_nonce( $_POST['my_meta_box_nonce'], plugin_basename( __FILE__ ) ) )
			return $post_id;

		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;

		$meta_value = get_post_meta( $post_id, 'rss_id', true );
		$new_meta_value = stripslashes( $_POST['rss_id'] );

		if ( $new_meta_value && '' == $meta_value )
			add_post_meta( $post_id, 'rss_id', $new_meta_value, true );

		elseif ( $new_meta_value != $meta_value )
			update_post_meta( $post_id, 'rss_id', $new_meta_value );

		elseif ( '' == $new_meta_value && $meta_value )
			delete_post_meta( $post_id, 'rss_id', $meta_value );
	}

	/* -------------------------------------------------------------------------------------- */
	function rss_per_page_PluginLinks($links, $file) {
			$base = plugin_basename(__FILE__);
			if ($file == $base) {
				$links[] = '<a href="https://wordpress.org/support/view/plugin-reviews/'.self::FS_PLUGINNAME.'#postform">' . __('Please rate me.',self::FS_TEXTDOMAIN) . '</a>';
			}
			return $links;
		}
    
    
}
 
$rss__per_page = new rss_per_page_class();





?>