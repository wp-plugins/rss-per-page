<?php
/**
 * @package rss-per-page
 * @version 1.0
 */
/*
Plugin Name: rss-per-page
Plugin URI: http://www.funsite.eu/plugins/rss-per-page
Description: Adds a field to pages and implements a widget to show a RSS depending on that field. 
Author: Gerhard Hoogterp
Version: 1.0
Author URI: http://www.funsite.eu/
*/

function pubDate2timestamp($pubDate) {

	$day = substr($pubDate, 5, 2);
	$month = substr($pubDate, 8, 3);
	$month = date('m', strtotime("$month 1 2011"));
	$year = substr($pubDate, 12, 4);
	$hour = substr($pubDate, 17, 2);
	$min = substr($pubDate, 20, 2);
	$second = substr($pubDate, 23, 2);
	$timezone = substr($pubDate, 26);

	if (is_numeric($timezone)):
		$convert['GMT']=($timezone/100)*3600;
		$timezone='GMT';
	else:
		$convert['GMT'] = +3600;
		$convert['GMT'] += (date('I',mktime(12,0,0,$month,$day,$year)))?3600:0;   // extra hour for summertime;
	endif;	
	//print $timezone.'  '.$convert['GMT'].' ';

	$timestamp = mktime($hour, $min, $second, $month, $day, $year);
//	date_default_timezone_set('Europe/Amsterdam');

	if(is_numeric($timezone)) {
		$modifier = substr($timezone, 0, 1);
		$hours_mod = (int) substr($timezone, 1, 2);
		$mins_mod = (int) substr($timezone, 3, 2);
		$diff=(int)($modifier+($hours_mod*3600)+($mins_mod*60));
		$timestamp=$timestamp+$diff;
	} else {
		$timestamp=$timestamp+$convert[$timezone];
	}

	return $timestamp;
}




class rss_per_page_widget extends WP_Widget {

	// constructor
	function rss_per_page_widget() {
		parent::WP_Widget(false, 
							$name = __('rss per page widget', 'rss_per_page_widget_plugin'),
							array('description' => __('Show an rss feed build from an url and a page settable id.','GPS_MAP_Widget_plugin'))
								);
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
		$title = 'Plugin reviews for @ID@';
		$rssfeed = 'https://wordpress.org/support/rss/view/plugin-reviews/@ID@';
		$defaultRSS = 'https://wordpress.org/news/feed/';
		$defaultTitle = 'Wordpress news';
		$maxShow = 3;
	    }
	    ?>

	    <p>
	    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	    </p>
	    <p>
	    <label for="<?php echo $this->get_field_id('rssfeed'); ?>"><?php _e('RSS feed (add <b>@ID@</b> where the RSS ID has to be inserted)', 'wp_widget_plugin'); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('rssfeed'); ?>" name="<?php echo $this->get_field_name('rssfeed'); ?>" type="text" value="<?php echo $rssfeed; ?>" />
	    </p>
	    
   	    <p>
	    <label for="<?php echo $this->get_field_id('defaultRSS'); ?>"><?php _e('Default RSS feed (use when the page RSS ID is empty)', 'wp_widget_plugin'); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('defaultRSS'); ?>" name="<?php echo $this->get_field_name('defaultRSS'); ?>" type="text" value="<?php echo $defaultRSS; ?>" />
	    </p>
	    
   	    <p>
	    <label for="<?php echo $this->get_field_id('defaultTitle'); ?>"><?php _e('Default RSS title (used with the default RSS feed)', 'wp_widget_plugin'); ?></label>
	    <input class="widefat" id="<?php echo $this->get_field_id('defaultTitle'); ?>" name="<?php echo $this->get_field_name('defaultTitle'); ?>" type="text" value="<?php echo $defaultTitle; ?>" />
	    </p>

	    <p>
	    <label for="<?php echo $this->get_field_id('maxShow'); ?>"><?php _e('Show items', 'wp_widget_plugin'); ?></label>
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
		$rss = simplexml_load_file($feed);		
		if ($rss) {
			$title = apply_filters('widget_title', $instance['title']);
			if ($rss_id) {
				$title=str_replace('@ID@',$rss_id,$title);
				$title=$title?$title:$rss->channel->title;
			} else {
				$title=$default_title;
			}
			
		} else {
			$title="ERROR: Feed not found";
		}
		
		$maxShow = apply_filters('widget_title', $instance['maxShow']);

		echo $before_widget;
	  
		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box rss_per_page_hook">';

		// Check if title is set
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
			echo '<div class="entry">';
			if ($rss) {
				if (count($rss->channel->item)) {
					$cnt=0;
					foreach ($rss->channel->item as $item) {
						echo '<div class="title"><a href="'. $item->link .'" rel="external">' . $item->title . "</a></div>";
						$timestamp = pubDate2timestamp($item->pubDate);
						echo '<p><span class="date">' . strftime('%d %B %Y',$timestamp) . "</span><br>";
						echo  strip_tags($item->description) . "</p>";
						$cnt++;
						if ($cnt>=$maxShow) break;
					}
				} else {
					print '<span class="nonews">'._e('No news found', 'wp_widget_plugin').'</span>';
				}
			} else {
				print '<span class="nonews">'._e('RSS feed not found', 'wp_widget_plugin').'</span>';
			}
			echo '</div>';
			
		echo '</div>';
		echo $after_widget;
	}
}

function rss_per_page_headercode () {
  wp_enqueue_style('rss_per_page_handler', plugins_url('/css/rss-per-page.css', __FILE__ ));
}		


function create_rss_id_box() {
	add_meta_box( 'my-meta-box', 'RSS ID', 'RSS_ID_box', 'page', 'normal', 'high' );
}

function rss_id_box( $object, $box ) { ?>
    <p>
		<label for="RSSID">Give the ID to fill in for the @ID@ placeholder in the rss-per-page widget</label><br>
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
			$links[] = '<a href="https://wordpress.org/support/view/plugin-reviews/rss-per-page">' . __('A review would be appriciated.','wp_widget_plugin') . '</a>';
		}
		return $links;
	}

add_filter('plugin_row_meta', 'rss_per_page_PluginLinks',10,2);

// admin interface
add_action( 'admin_menu', 'create_rss_id_box' );
add_action( 'save_post', 'save_rss_id', 10, 2 );

// register widget
add_action('widgets_init', create_function('', 'return register_widget("rss_per_page_widget");'));
add_action('wp_head', 'rss_per_page_headercode',false,false,true);
?>