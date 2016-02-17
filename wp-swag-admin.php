<?php 
/*
	wp-swag plugin admin functionalities and hooks
*/

require_once __DIR__."/utils.php";

class WP_Swag_admin{
	public function init_hooks(){
		add_shortcode("track-listing",array(get_called_class(), "ti_track_listing"));
		add_action('wp_enqueue_scripts',array(get_called_class(), "ti_enqueue_scripts"));	
	}

	/**
	 * Handle the track-listing short_code.
	 */
	public function ti_track_listing() {
		$parentId=get_the_ID();
		$pages=get_pages(array( 
			"parent"=>$parentId
		));

		$out = '<div class="masonry-loop">';

		foreach ($pages as $page) {
			if ($page->ID!=$parentId) {
    		$page->swagpaths = count(get_pages(array('child_of'=>$page->ID)));
				$out.=render_tpl(__DIR__."/tpl/tracklisting.php",array(
					"page"=>$page
				));
			}
		}
    $out .= '</div>';
    return $out;
	}

	/**
	 * Scripts and styles in the plugin
	 */
	public function ti_enqueue_scripts() {
		wp_register_style("wp_swag",plugins_url( "/style.css", __FILE__)); //?v=x added to refresh browser cache when stylesheet is updated. 
		wp_enqueue_style("wp_swag");
	}
}