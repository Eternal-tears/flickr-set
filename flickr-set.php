<?php
/*
 Plugin Name: Flickr set list
 Plugin URI: http://lovelog.eternal-tears.com/
 Description: Flickrのset
 Version: 1.0
 Author: Eternal-tears
 Author URI: http://lovelog.eternal-tears.com/
 */

//FlickrSetプログラム部分
class WP_Widget_FlickrSet extends WP_Widget{

	function __construct() {
		parent::__construct(
			'flickrset_widget', // Base ID
			__('Flickr Set', 'Lovelog_photolog'), // Name
			array( 'description' => __( 'Flickr setのウィジェットです。', 'Lovelog_photolog' ), ) // Args
		);
	}

	function widget( $args, $instance ) {
		extract($args);
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$myurl = apply_filters( 'widget_myurl', empty( $instance['myurl'] ) ? '' : $instance['myurl'], $instance );
		$api_key = apply_filters( 'widget_apikey', empty( $instance['api_key'] ) ? '' : $instance['api_key'], $instance );
		$photoset_id = apply_filters( 'widget_photosetid', empty( $instance['photoset_id'] ) ? '' : $instance['photoset_id'], $instance );
		$page = apply_filters( 'widget_page', empty( $instance['page'] ) ? '' : $instance['page'], $instance );
		$per_page = apply_filters( 'widget_perpage', empty( $instance['per_page'] ) ? '' : $instance['per_page'], $instance );
		$media = apply_filters( 'widget_media', empty( $instance['media'] ) ? '' : $instance['media'], $instance );
		$format = 'json';
		$nojsoncallback = '1';
		//$auth_token = get_option('flickrset_authtoken');
		//$api_sig = apply_filters( 'widget_apisig', empty( $instance['api_sig'] ) ? '' : $instance['api_sig'], $instance );
		$viewnum = apply_filters( 'widget_viewnum', empty( $instance['viewnum'] ) ? '' : $instance['viewnum'], $instance );

		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; } 

		$baseurl = "https://api.flickr.com/services/rest/";
		$params = array();
		$params['method']  = 'flickr.photosets.getPhotos';
		$params['api_key'] = $api_key;
		$params['photoset_id'] = $photoset_id;
		//$params['sort']	= $sort;
		if(! $per_page == 0){
		$params['per_page'] = $per_page;
		}
		if(! $page == 0){
		$params['page'] = $page;
		}
		//$params['media'] = $media;
		$params['format'] = 'json';
		$params['nojsoncallback'] = '1';
		//$params['api_sig'] = $api_sig;

		$canonical_string = '';
		foreach ($params as $k => $v) {
						$canonical_string .= '&'.$k.'='.$v;
		}
		$canonical_string = substr($canonical_string, 1);
		
		$url = file_get_contents($baseurl.'?'.$canonical_string);

		//echo $url;
		$rssdata = json_decode($url) or die("パースエラー");

		$len = array_reverse($rssdata->photoset->photo);

		if (!is_array($len)) return $len;
		$keys = array_keys($len);
		shuffle($keys);
		$random = array();
		foreach ($keys as $key) {
			$random[$key] = $len[$key];
		}
	//return $random;
//echo '<pre>';
//var_dump($random);
//echo '</pre>';
//var_dump(count($rssdata->photoset->photo));
		$outdata = "";
		$outdata .= "<div id=\"flickr-photos\">\n";
		$outdata .= "<ul class=\"small-block-grid-5\">\n";
				//var_dump(rand($i));
		if($viewnum > count($rssdata->photoset->photo)){
			$viewnum = count($rssdata->photoset->photo);
		}
		for ($n=0; $n<$viewnum; $n++){

			if(! $len[$n] == null){
				$itemFarm = $len[$n]->farm;
				$itemServer = $len[$n]->server;
				$itemID = $len[$n]->id;
				$itemSecret = $len[$n]->secret;
				$itemTitle = $len[$n]->title;

				$itemLink = $myurl . '/' . $itemID . '/';
				$itemPath = 'http://farm' . $itemFarm . '.static.flickr.com/' . $itemServer . '/' . $itemID . '_' . $itemSecret . '_' . $media . '.jpg';
				$flickrSrc = '<img src="' . $itemPath . '" alt="' . $itemTitle . '">';
				$outdata .= '<li><a href="' . $itemLink . '" target="_blank">' . $flickrSrc . '</a></li>';
			}

		}
		$outdata .= "</ul>\n";
		$outdata .= "</div>\n\n";

		echo $outdata;

		echo $after_widget;}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = esc_attr($new_instance['title']);
		$instance['myurl'] = esc_url($new_instance['myurl']);
		$instance['api_key'] = esc_attr($new_instance['api_key']);
		$instance['photoset_id'] = esc_attr($new_instance['photoset_id']);
		$instance['page'] = esc_attr($new_instance['page']);
		$instance['per_page'] = esc_attr($new_instance['per_page']);
		$instance['media'] = esc_attr($new_instance['media']);
		//$instance['api_sig'] = esc_attr($new_instance['api_sig']);
		$instance['viewnum'] = esc_attr($new_instance['viewnum']);

		$instance['filter'] = isset($new_instance['filter']);
		return $instance;
	}

	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'myurl' => '', 'api_key' => '', 'photoset_id' => '', 'page' => '', 'per_page' => '', 'media' => '', 'viewnum' => '') );
		$title = esc_attr($instance['title']);
		$myurl = esc_url($instance['myurl']);
		$api_key = esc_attr($instance['api_key']);
		$photoset_id = esc_attr($instance['photoset_id']);
		$page = esc_attr($instance['page']);
		$per_page = esc_attr($instance['per_page']);
		$media = esc_attr($instance['media']);
		//$api_sig = esc_attr($instance['api_sig']);
		$viewnum = esc_attr($instance['viewnum']);
?>

		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id('myurl'); ?>">アカウントURL</label>
		<input class="widefat" id="<?php echo $this->get_field_id('myurl'); ?>" name="<?php echo $this->get_field_name('myurl'); ?>" type="text" value="<?php echo esc_attr($myurl); ?>" /><br />アカウントURLを指定してください。

		<p><label for="<?php echo $this->get_field_id('api_key'); ?>">Flickr API Key</label>
		<input class="widefat" id="<?php echo $this->get_field_id('api_key'); ?>" name="<?php echo $this->get_field_name('api_key'); ?>" type="text" value="<?php echo esc_attr($api_key); ?>" /><br />Flickr API Keyを指定してください。

		<p><label for="<?php echo $this->get_field_id('photoset_id'); ?>">Set id</label>
		<input class="widefat" id="<?php echo $this->get_field_id('photoset_id'); ?>" name="<?php echo $this->get_field_name('photoset_id'); ?>" type="text" value="<?php echo esc_attr($photoset_id); ?>" /><br />Set idを指定してください。

		<p><label for="<?php echo $this->get_field_id('page'); ?>">ページ</label>
		<input class="widefat small-text" type="number" id="<?php echo $this->get_field_id('page'); ?>" name="<?php echo $this->get_field_name('page'); ?>" type="text" value="<?php echo esc_attr($page); ?>" /><br />ページ数を指定します。

		<p><label for="<?php echo $this->get_field_id('per_page'); ?>">JSON内の表示件数</label>
		<input class="widefat small-text" type="number" id="<?php echo $this->get_field_id('per_page'); ?>" name="<?php echo $this->get_field_name('per_page'); ?>" type="text" value="<?php echo esc_attr($per_page); ?>" /><br />JSON内の表示件数（Max500）を指定します。

		<p><label for="<?php echo $this->get_field_id('viewnum'); ?>">画像の表示数</label>
		<input class="widefat small-text" type="number" id="<?php echo $this->get_field_id('viewnum'); ?>" name="<?php echo $this->get_field_name('viewnum'); ?>" type="text" value="<?php echo esc_attr($viewnum); ?>" /><br />表示件数を指定します。

		<p><label for="<?php echo $this->get_field_id('media'); ?>">画像サイズ</label>

		<select id="<?php echo $this->get_field_id('media'); ?>" name="<?php echo $this->get_field_name('media'); ?>">
		<?php
			$options3 = array(
				array('value' => 's', 'text' => 'url_sq(75px正方形)'),
				array('value' => 't', 'text' => 'url_t(100px縦auto)'),
				array('value' => 'q', 'text' => 'url_q(150px正方形)'),
				array('value' => 'm', 'text' => 'url_m(500px縦auto)'),
				array('value' => 'n', 'text' => 'url_n(320px縦auto)'),
				array('value' => 'z', 'text' => 'url_z(640px縦auto)'),
				array('value' => 'c', 'text' => 'url_c(800px縦auto)'),
				array('value' => 'l', 'text' => 'url_l(1024px縦auto)'),
				array('value' => 'o', 'text' => 'url_o(full)'),
			);
			foreach ($options3 as $option) : ?>
			<option value="<?php echo esc_attr($option['value']); ?>" id="<?php echo esc_attr($option['value']); ?>"<?php if($media == $option['value']){ ?> selected="selected"<?php } ?>><?php echo esc_attr($option['text']); ?></option>
			<?php endforeach; ?>
		</select><br />画像サイズを指定します。<br>

		<!--<p><label for="<?php //echo $this->get_field_id('flickr_apisig'); ?>">api_sig</label>
		<input class="widefat" id="<?php //echo $this->get_field_id('api_sig'); ?>" name="<?php //echo $this->get_field_name('api_sig'); ?>" type="text"  value="<?php //echo esc_attr($api_sig); ?>" /><br />api_sig指定します。//-->
		※<a href="https://www.flickr.com/services/api/explore/flickr.photosets.getPhotos" target="_blank">Product Advertising API</a>で確認できます。
<?php
	}
}
add_action('widgets_init', create_function('', 'return register_widget("WP_Widget_FlickrSet");'));


?>
